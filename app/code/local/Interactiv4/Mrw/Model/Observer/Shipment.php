<?php
class Interactiv4_Mrw_Model_Observer_Shipment {
    const LIVE_MODE = '01';
    const DEVELOPER_MODE = '02';
    /**
     * Instancia de Interactiv4_Mrw_Helper_Data
     * @see Interactiv4_Mrw_Helper_Data
     * @var Interactiv4_Mrw_Helper_Data
     */
    protected $_helper;
    /**
     *
     * cliente ws. de zend con el que llamamos a los web services de MRW
     * @var Zend_Soap_Client_DotNet
     */
    protected $_soapClient;
    
    /**
     *
     * @var Mage_Sales_Model_Order_Shipment 
     */
    protected $_shipment = null;
    
    /**
     * Array de Interactiv4_PackedShipment_Model_Package con los bultos de este envio
     * @var array
     */
    protected $_packages = array();
    protected $_store = null;
    
    /**
     *
     * @var Interactiv4_PackedShipment_Model_Packedshipment 
     */
    protected $_packedShipment = null;
    public function __construct() {
        $this->_helper = Mage::helper('i4mrwes');
    }
    /**
     * Inicializamos el client de soap,
     * y los headers necesarios para autenticarse en el ws
     */
    protected function _getSoapClient($store) {
        try {
            if (!isset($this->_soapClient) || $store != $this->_store) {
                $mode = $this->_helper->getConfigData('mode', $store);
                $url = ($mode == self::LIVE_MODE) ? $this->_helper->getConfigData('url_envios', $store) : $this->_helper->getConfigData('url_envios_dev', $store);
                $this->_soapClient = new Zend_Soap_Client_DotNet($url);
                $authinfo = array(
                    'CodigoFranquicia' => $this->_helper->getConfigData('codigo_franquicia', $store),
                    'CodigoAbonado' => $this->_helper->getConfigData('codigo_abonado', $store),
                    'UserName' => $this->_helper->getConfigData('username', $store),
                    'Password' => $this->_helper->getConfigData('password', $store)
                );
                if ($this->_helper->getConfigData("codigo_departamento", $store)) {
                    $authinfo["CodigoDepartamento"] = $this->_helper->getConfigData("codigo_departamento", $store);
                }
                $authHeader = new SoapHeader("http://www.mrw.es/", 'AuthInfo', $authinfo);
                $this->_soapClient->addSoapInputHeader($authHeader, true);
            }
            return $this->_soapClient;
        } catch (Exception $e) {
            Mage::throwException($this->_helper->__("An error occurred connecting to the MRW web service. Error message: '%s'", $e->getMessage()));
        }
    }
    /**
     * Esta funcion se dispara con el evento "sales_order_shipment_save_after"
     * que ocurre después de que se haya generado un ship en el admin de magento
     *
     * Aqui es donde llamamos al WS de MRW solicitando envio, y si todo ok
     * guardamos info del envio en db. y tambien el tracking number
     * @param Varien_Event_Observer $o
     */
    public function afterSave(Varien_Event_Observer $o) {
        
        
        $shipment = $o->getEvent()->getShipment();
        if ($this->_shipment === $shipment) { // Sólo debemos pasar por aquí una vez para cada envío.
            return $o;
        }
        $this->_shipment = $shipment;
        $this->_packedShipment = Mage::getModel('i4packedshipment/packedshipment', array($shipment));  
        $this->_packages = $this->_packedShipment->getPackages();
        
        $totalPackages = count($this->_packages);
        if (!$totalPackages)
            return $o;
        //@todo decidimos varias variables como peso, según bultos
        $weight = $this->_packedShipment->getTotalWeight();
        
        $order = $shipment->getOrder();
        $store = $order->getStore();
        $shippingMethod = $order->getShippingMethod();
        $carrierArray = explode('_', $shippingMethod);
        list($carrier, $method) = $carrierArray;
        if ($carrier != 'i4mrwes') {
            return $o;
        }
        //a partir de aqui solicitamos envio por ws
        $request = array(
            //'DatosRecogida'=>$this->_prepareDatosRecogida(),//esto no esta implementado en MRW todavia
            'DatosEntrega' => $this->_prepareDatosEntrega($shipment),
            'DatosServicio' => $this->_prepareDatosServicio($shipment, $method, $totalPackages, $weight)
        );
        $params = array('request' => $request);
        $soapClient = $this->_getSoapClient($store);
        try {
            $response = $soapClient->TransmEnvio($params);
        } catch (Exception $exception) {
            Mage::throwException(printf("Error llamando al metodo TransmEnvio del WebService MRW: %s<br />\n", $exception->getMessage()));
            $this->_helper->log("last soapClient last request: \n" . $soapClient->getLastRequest());
        } catch (SoapFault $exception) {
            Mage::throwException(printf("Error llamando al metodo TransmEnvio del WebService MRW: %s<br />\n", $exception->getMessage()));
            $this->_helper->log("last soapClient last request: \n" . $soapClient->getLastRequest());
        }
        if ($response->Estado == 1) {
            $this->_saveTracking($shipment, $response->NumeroEnvio, $carrier);
            $this->_fetchTicket($response->NumeroEnvio, $shipment);
            Mage::getModel('i4mrwes/mrwes_ship')->addShip($shipment->getId(), $this->_getResponseUrl($response, $store));
        } else {
            $this->_helper->log("last soapClient request: \n" . $soapClient->getLastRequest());
            $this->_helper->log("last soapClient response: \n" . $soapClient->getLastResponse());
            Mage::throwException("Error creating shipment (" . $response->Mensaje . ")");
        }
    }
    protected function _prepareDatosEntrega($shipment) {
        $order = $shipment->getOrder();
        $address = $order->getShippingAddress();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $codigoTipoVia = '';
        $nombreVia = '';
        $numero = '';
        $resto = '';
        if (!$this->_helper->getAddressStreetParts($address, $codigoTipoVia, $nombreVia, $numero, $resto)) {
            Mage::throwException($this->_helper->__("The shipping address is not formatted correctly for MRW. Please edit the address so that it contains the street type, street name, and number."));
        }
        $result = array(
            'Direccion' => array(
                'CodigoTipoVia' => $codigoTipoVia,
                'Via' => $nombreVia,
                'Numero' => $numero,
                'Resto' => $resto,
                'CodigoPostal' => $address->getPostcode(),
                'Poblacion' => $address->getCity(),
                'Provincia' => $address->getRegion(),
            //'Estado'=>$address->getRegion(),
            //'CodigoPais'=>$address->getCountryId()
            ),
            'Nif' => $customer->getTaxvat(),
            'Nombre' => ($address->getCompany()) ? $address->getCompany() : $address->getName(),
            'Telefono' => $address->getTelephone(),
            'Contacto' => $address->getName(),
                //'ALaAtencionDe'=>$address->getName(),
                /* 'Horario'=>array(
                  'Rango'=>array('Desde'=>'10:00', 'Hasta'=>'12:00')
                  ), */
                //'Observaciones'=>'',
        );
        return $result;
    }
    protected function _prepareDatosServicio($shipment, $codigoServicio, $qty, $weight) {
        $order = $shipment->getOrder(); /* @var $order Mage_Sales_Model_Order */
        $bultos = array();
        foreach ($this->_packages as $package) {
            $bultos[] = array('BultoRequest' => array(
                    'Referencia' => $package->getRef(),
                    'Peso' => number_format($package->getPackageWeight(), 2, ',', '')
                    ));
        }
        $result = array(
            'Fecha' => $this->_calculateFechaRecogida($order),
            'Referencia' => $shipment->getIncrementId(),
            'EnFranquicia' => 'N',
            'CodigoServicio' => str_pad($codigoServicio, 4, "0", STR_PAD_LEFT),
            'Frecuencia' => '1', //@todo para URGENTE HOY hay que indicar en q frecuencia saldra 1 o 2
            'Bultos' => $bultos,
            'NumeroBultos' => $qty,
            'Peso' => number_format($weight, 2, ',', '')
                //'NumeroPuentes'=>'1', //autoasignado internamente por MRW
                //'EntregaSabado'=>'N',
                //'Entrega830'=>'N',
                //'EntregaPartirDe'=>'10:00',
                //'Gestion'=>'N',
                //'Retorno'=>'N',
                //'ConfirmacionInmediata'=>'N',
                //'Reembolso'=>'R',
                //'ImporteReembolso'=>'100,00'
        );
        if ($order->getPayment()->getMethod() == 'i4mrwes_cashondelivery') {
            $result['Reembolso'] = 'O';
            $result['ImporteReembolso'] = number_format($order->getBaseGrandTotal(), 2, ',', '');
        }
        return $result;
    }
    /**
     *
     * Calculamos la fecha de recogida en funcion a la "deliverydate" si la hubiera:
     * comprobamos que la order tiene GomageDeliverydate (esta activado el modulo para esa order)
     * Hay 3 casos posibles:
     * 1.- Entrega hoy -> recogida hoy
     * 2.- Entrega mañana -> recogida hoy
     * 3.- Entrega más alla de mañana -> recogida 1 dia antes de entrega
     *
     */
    protected function _calculateFechaRecogida($order) {
        $onedayIncrement = 60 * 60 * 24;
        $today = mktime(0, 0, 0);
        $tomorrow = $today + $onedayIncrement;
        $frmt = 'd/m/Y';
        $dd = $order->getGomageDeliverydate();
        if ($dd) {
            $today = mktime(0, 0, 0);
            $tomorrow = $today + $onedayIncrement;
            list($fecha, $hora) = explode(' ', $dd);
            list($year, $month, $day) = explode('-', $fecha);
            $ddTime = mktime(0, 0, 0, $month, $day, $year);
            //recogida today
            if ($ddTime == $today || $ddTime == $tomorrow) {
                return date($frmt, $today);
            }
            //recogida 1 dia antes de delivery
            return date($frmt, $ddTime - $onedayIncrement);
        }
        return date($frmt, $today); //hoy
    }
    protected function _getResponseUrl($response, $store) {
        // Montamos la URL para recuperar la información del envío y la etiqueta
        $url =
                $response->Url
                . "?Franq=" . $this->_helper->getConfigData('codigo_franquicia', $store)
                . "&Ab=" . $this->_helper->getConfigData('codigo_abonado', $store)
                . "&Dep=" . $this->_helper->getConfigData('codigo_departamento', $store)
                . "&Us=" . $this->_helper->getConfigData('username', $store)
                . "&Pwd=" . $this->_helper->getConfigData('password', $store)
                . "&NumSol=" . $response->NumeroSolicitud
                . "&NumEnv=" . $response->NumeroEnvio;
        return $url;
    }
    /**
     * Salvamos el tracking number para esta orden
     * @param unknown_type $shipment
     * @param unknown_type $tracknumber
     * @param unknown_type $carrier
     */
    protected function _saveTracking($shipment, $tracknumber, $carrier) {
        /**
         * @todo aqui guardariamos las etiquetas en un directorio local,
         * si quisieramos tener ese comportamiento.
         * Como MRW nos da una página de confirmación donde ademas de las
         * etiquetas vemos otra info de pedido, no lo hacemos así.
         * Asi no tenemos que "limpiar" las etiquetas locales con un cron,
         * y reducimos tiempos de espera y proceso de la máquina
         *
         * @see Interactiv4_Mrw_IndexController::etiquetaAction()
         * ahí tenemos una implementacion para traernos las etiquetas PDF
         * de MRW a local
         */
        $track = Mage::getModel('sales/order_shipment_track')
                ->setNumber($tracknumber)
                ->setCarrierCode($carrier)
                ->setTitle($this->_helper->getConfigData('title'));
        $shipment->addTrack($track);
        $shipment->save();
        return $this;
    }
    /**
     *
     * @param string $numEnvio
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return boolean
     * @throws Exception 
     */
    protected function _fetchTicket($numEnvio, Mage_Sales_Model_Order_Shipment $shipment) {
        $request = array(
            'request' => array(
                'NumeroEnvio' => $numEnvio,
                'SeparadorNumerosEnvio' => '',
                'FechaInicioEnvio' => '',
                'FechaFinEnvio' => '',
                'TipoEtiquetaEnvio' => '0',
                'ReportTopMargin' => '1100',
                'ReportLeftMargin' => '650'
            )
        );
        $soapClient = $this->_getSoapClient($shipment->getStore());
        try {
            $soapClient->EtiquetaEnvio($request);
            $response = simplexml_load_string($soapClient->getLastResponse());
            $response->registerXPathNamespace('ns1', 'http://www.mrw.es/');
            $state = $this->_getEtiquetaResponseFieldValue($response, 'Estado');
            $pdf = $this->_getEtiquetaResponseFieldValue($response, 'EtiquetaFile');
            if ($state == '1') {
                // ha funcionado
                $blobEtiqueta = base64_decode($pdf);
                if ($blobEtiqueta === false) {
                    throw new Exception($this->_helper->__("The shipping label returned by MRW could not be decoded."));
                }
                $shipment->setShippingLabel($blobEtiqueta);
                $shipment->save();
                return true;
            } else {
                $message = $this->_getEtiquetaResponseFieldValue($response, 'Mensaje');
                throw new Exception($this->_helper->__('A problem occured retrieving the shipping label from MRW. MRW says, "%s"', $message));
            }
        } catch (Exception $exception) {
            Mage::getSingleton('core/session')->addWarning($this->_helper->__("Although the shipment was communicated successfully to MRW, a problem occurred retrieving the shipping label: %s", $exception->getMessage()));
            $this->_helper->log(__METHOD__ . ": {$exception->getMessage()}");
            $this->_helper->log(__METHOD__ . ": last soapClient request: \n" . $soapClient->getLastRequest());
            $this->_helper->log(__METHOD__ . ": last soapClient response: \n" . $soapClient->getLastResponse());
            return false;
        }
    }
    /**
     * 
     * @param SimpleXMLElement $xml
     * @param string $field
     * @return string
     */
    protected function _getEtiquetaResponseFieldValue(SimpleXMLElement $response, $field) {
        $valueArr = $response->xpath('//ns1:GetEtiquetaEnvioResponse/ns1:GetEtiquetaEnvioResult/ns1:' . $field . '[1]');
        $value = (string) $valueArr[0];
        return $value;
    }
}
