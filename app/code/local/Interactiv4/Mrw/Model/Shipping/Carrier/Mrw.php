<?php
/*
 * Created on Nov 3, 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Interactiv4_Mrw_Model_Shipping_Carrier_Mrw extends Interactiv4_Mrw_Model_Shipping_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface, Interactiv4_PackedShipment_Model_Carrier_Interface {
    /**
     * @todo estas valores son provisionales hasta que MRW nos de los correctos  
     */
    const SHIPPING_ADDRESS_MAX_LENGTH_TIPO_VIA = 255;
    const SHIPPING_ADDRESS_MAX_LENGTH_NUMERO = 255;
    const SHIPPING_ADDRESS_MAX_LENGTH_NOMBRE_VIA = 255;
    const SHIPPING_ADDRESS_MAX_LENGTH_REST = 255;    
    
    protected $_code = 'i4mrwes';
    protected $_result = null;
    /**
     * Instancia de Interactiv4_Mrw_Helper_Data
     * @see Interactiv4_Mrw_Helper_Data
     * @var Interactiv4_Mrw_Helper_Data
     */
    protected $_helper;
    public function __construct() {
        $this->_helper = Mage::helper('i4mrwes');
        parent::__construct();
    }
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }
        // Free shipping by qty
        $freeQty = 0;
        $totalPriceInclTax = 0;
        $totalPriceExclTax = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $totalPriceInclTax += $item->getBaseRowTotalInclTax();
                $totalPriceExclTax += $item->getBaseRowTotal();
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }
//        if (!$request->getConditionName()) {
//            $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
//        }
        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();
//        $request->setPackageWeight($request->getFreeMethodWeight());
//        $request->setPackageQty($oldQty - $freeQty);
        // Se incluye el precio del envío para los cálculos precio vs destino.
        if ($this->_getTaxHelper()->shippingPriceIncludesTax($request->getStoreId())) {
            $request->setData('i4_table_price', $totalPriceInclTax);
        } else {
            $request->setData('i4_table_price', $totalPriceExclTax);
        }
        $result = Mage::getModel('shipping/rate_result');
//        $request->setMethod($this->getConfigData('method'));
        $rate = $this->getRate($request);
        Mage::helper('i4mrwes')->log($rate);
        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);
        if (count($rate) > 0) {
            if ($request->getFreeShipping() === true) {
                $cheap_method = 0;
                $cheap_price = 999999999;
                foreach ($rate as $r) {
                    if ($r['price'] < $cheap_price) {
                        $cheap_method = $r['method'];
                        $cheap_price = $r['price'];
                    }
                }
                $method = Mage::getModel('shipping/rate_result_method');
                $shippingPrice = '0.00'; // we set the shipping price to zero
                // then we change the method title to indicate shipping is free
                $method->setCarrier('i4mrwes');
                $method->setCarrierTitle($this->getConfigData('title'));
                $method->setMethodTitle(Mage::helper('shipping')->__('Free Shipping'));
                $method->setPrice($shippingPrice);
                $method->setMethod($cheap_method);
                $method->setCost($shippingPrice);
                $result->append($method);
            } else {
                $mrwmethod = 0;
                foreach ($rate as $r) {
                    if ($r['method'] != $mrwmethod) {
                        $mrwmethod = $r['method'];
                        $method = Mage::getModel('shipping/rate_result_method');
                        $method->setCarrier('i4mrwes');
                        $method->setCarrierTitle($this->getConfigData('title'));
                        $method->setPrice($r['price']);
                        $method->setCost($r['price']);
                        $method->setMethod($r['method']);
                        $methodDescriptions = Mage::getModel('i4mrwes/shipping_carrier_mrw_source_method')->toOptionArray();
                        $method->setMethodTitle($methodDescriptions[$r['method']]);
                        $availableMethods = explode(',', $this->getconfigData('method'));
                        
                        // Si el precio es negativo, indica que este método no está disponible para carritos con este valor/peso.
                        if ($r['price'] < 0) {
                            continue;
                        }                        
                        if (in_array($r['method'], $availableMethods)) {
                            $result->append($method);
                        }
                    }
                }
            }
        }
        return $result;
    }
    public function getRate(Mage_Shipping_Model_Rate_Request $request) {
        return Mage::getResourceModel('i4mrwes/carrier_tablerate')->getRate($request);
    }
    public function getAllowedMethods() {
        return array('i4mrwes' => $this->getConfigData('name'));
    }
    /* public function addTrack(Mage_Sales_Model_Order_Shipment_Track $track)
      {
      return $this;
      } */
    /**
     * Funcion que he hecho temporalmente mientras se arreglan llamadas al ws
     * de MRW que no funciona en pruebas.
     * @param unknown_type $trackings
     */
    public function getTracking($trackings) {
        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }
        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }
        foreach ($trackings as $trackingNumber) {
            $trackStatusResponse = $this->getTrackingStatus($trackingNumber);
            if ($trackStatusResponse->Estado != 1) {
                //error
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($trackingNumber);
                $error->setErrorMessage($trackStatusResponse->Mensaje);
                $this->_result->append($error);
            } else {
                //ok
                $envio = $trackStatusResponse->Envio;
                $fechaEntrega = preg_replace('/(\d{2})(\d{2})(\d{4})/i', '$1/$2/$3', $envio->FechaEntrega);
                $horaEntrega = preg_replace('/(\d{2})(\d{2})/i', '$1:$2:00', $envio->HoraEntrega);
                $location = sprintf("%s , %s - %s", $envio->DireccionEntrega, $envio->CPEntrega, $envio->PoblacionEntrega);
                $ok = array(
                    'status' => $envio->EstadoDescripcion,
                    'deliverydate' => $fechaEntrega, // mm/dd/YYYY o d-m-y
                    'deliverytime' => $horaEntrega,
                    'deliverylocation' => $location,
                    //'signedby'=>'signedby',
                    'deliveryto' => $envio->Destinatario,
                    'progressdetail' => array(
                        array(
                            'activity' => $envio->EstadoDescripcion,
                            'deliverydate' => $fechaEntrega,
                            'deliverytime' => $horaEntrega,
                            'deliverylocation' => $location
                        )
                    )
                );
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($trackingNumber);
                $tracking->addData($ok);
                //$tracking->setErrorMessage("mensaje de error");
                $this->_result->append($tracking);
            }
        }
        return $this->_result;
    }
    public function getTrackingStatus($trackingNumber) {
        $client = new Zend_Soap_Client_DotNet("http://seguimiento.mrw.es/swc/wssgmntnvs.asmx?WSDL");
        $params = array(
            'Franquicia' => $this->_helper->getConfigData('codigo_franquicia'),
            'Cliente' => $this->_helper->getConfigData('codigo_abonado'),
            'Password' => 'J8E24E',
            //'Password'=>$this->_helper->getConfigData('password_seguimiento'),
            'NumeroMRW' => $trackingNumber,
            'Agrupado' => 0,
                //'Referencia'=>''
        );
        $response = $client->SeguimientoNumeroEnvioMRWNacional($params);
        return $response;
    }
    /**
     * Codigo abajo para la interface de packedShipment
     */
    /**
     * Function required by interface Interactiv4_PackedShipment_Model_Carrier_Interface
     * @see Interactiv4_PackedShipment_Model_Carrier_Interface
     * @param string $countryId
     * @return boolean
     */
    public function supportsAddressValidation($countryId) {
        return false;
    }
    /*
     * Se deveulve true si la combinación de población y código postal es válida
     * (Función requerida por la interfaz Interactiv4_PackedShipment_Model_Carrier_Interface)
     * @see Interactiv4_PackedShipment_Model_Carrier_Interface
     * @param string $city
     * @param string $postcode
     * @param string &$errorMsg - En el caso de un error, devuelve el mensaje.
     * @return bool
     */
    public function isValidCityPostcode($city, $postcode, &$errorMsg) {
        return true;
    }
    /*
     * Se devuelve la lista de códigos postales que sean válidos para
     * la población proporcionada.
     * (Función requerida por la interfaz Interactiv4_PackedShipment_Model_Carrier_Interface)
     * @see Interactiv4_PackedShipment_Model_Carrier_Interface     *
     * @param string $city
     * @param string &$errorMsg - En el caso de un error, devuelve el mensaje.     *
     * @return array (of string)
     */
    public function getPostcodesForCity($city, &$errorMsg) {
        return array();
    }
    /*
     * Se devuelve la lista de poblaciones que sean válidas para
     * el código postal proporcionado.
     * (Función requerida por la interfaz Interactiv4_PackedShipment_Model_Carrier_Interface)     *
     * @see Interactiv4_PackedShipment_Model_Carrier_Interface     *
     * @param string $postcode
     * @param string &$errorMsg - En el caso de un error, devuelve el mensaje.     *
     * @return array (of string)
     */
    public function getCitiesForPostcode($postcode, &$errorMsg) {
        return array();
    }
    /**
     * Método requierido por la interfaz Interactiv4_PackedShipment_Model_Carrier_Interface
     * @see Interactiv4_PackedShipment_Model_Carrier_Interface
     * @return boolean
     */
    public function supportsCalculationOfShippingCosts() {
        return false;
    }
    public function getShippingCost(Mage_Sales_Model_Order $order, $city, $postcode, $weightsBultos, &$errorStr) {
        return 0;
    }
    /**
     * Se devuelve true si el shipping method pasado sólo es válido en el caso
     * de que los envíos contengan un solo bulto.
     * @param string $shippingMethod
     * @return boolean
     */
    public function shippingMethodRequiresShipmentsOfOnlyOneBulto($shippingMethod) {
        return true;
    }
    /**
     *
     * @return Mage_Tax_Helper_Data
     */
    protected function _getTaxHelper() {
        return Mage::helper('tax');
    }
    public function skipDialog($store) {
        return Mage::getStoreConfig('carriers/i4mrwes/skip_packed_shipment_dialog', $store) ? true : false;
    }
    /* public function getTracking($trackings)
      {
      $return = array();
      if (!is_array($trackings)) {
      $trackings = array($trackings);
      }
      //$this->setXMLAccessRequest();
      $this->_getXmlTracking($trackings);
      print_r($this->_result);exit;
      return $this->_result;
      } */
    /* protected function _getXmlTracking($trackings)
      {
      $url = $this->getConfigData('tracking_xml_url');
      print_r($trackings);exit;
      foreach($trackings as $tracking){
      $xmlRequest=$this->_xmlAccessRequest;
      // RequestOption==>'activity' or '1' to request all activities
      $xmlRequest .=  <<<XMLAuth
      <?xml version="1.0" ?>
      <TrackRequest xml:lang="en-US">
      <Request>
      <RequestAction>Track</RequestAction>
      <RequestOption>activity</RequestOption>
      </Request>
      <TrackingNumber>$tracking</TrackingNumber>
      <IncludeFreight>01</IncludeFreight>
      </TrackRequest>
      XMLAuth;
      $debugData = array('request' => $xmlRequest);
      try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      $xmlResponse = curl_exec ($ch);
      $debugData['result'] = $xmlResponse;
      curl_close ($ch);
      }
      catch (Exception $e) {
      $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
      $xmlResponse = '';
      }
      $this->_debug($debugData);
      $this->_parseXmlTrackingResponse($tracking, $xmlResponse);
      }
      return $this->_result;
      } */
    /*
      protected function _parseXmlTrackingResponse($trackingvalue, $xmlResponse)
      {
      $errorTitle = 'Unable to retrieve tracking';
      $resultArr = array();
      $packageProgress = array();
      if ($xmlResponse) {
      $xml = new Varien_Simplexml_Config();
      $xml->loadString($xmlResponse);
      $arr = $xml->getXpath("//TrackResponse/Response/ResponseStatusCode/text()");
      $success = (int)$arr[0][0];
      if($success===1){
      $arr = $xml->getXpath("//TrackResponse/Shipment/Service/Description/text()");
      $resultArr['service'] = (string)$arr[0];
      $arr = $xml->getXpath("//TrackResponse/Shipment/PickupDate/text()");
      $resultArr['shippeddate'] = (string)$arr[0];
      $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/Weight/text()");
      $weight = (string)$arr[0];
      $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/UnitOfMeasurement/Code/text()");
      $unit = (string)$arr[0];
      $resultArr['weight'] = "{$weight} {$unit}";
      $activityTags = $xml->getXpath("//TrackResponse/Shipment/Package/Activity");
      if ($activityTags) {
      $i=1;
      foreach ($activityTags as $activityTag) {
      $addArr=array();
      if (isset($activityTag->ActivityLocation->Address->City)) {
      $addArr[] = (string)$activityTag->ActivityLocation->Address->City;
      }
      if (isset($activityTag->ActivityLocation->Address->StateProvinceCode)) {
      $addArr[] = (string)$activityTag->ActivityLocation->Address->StateProvinceCode;
      }
      if (isset($activityTag->ActivityLocation->Address->CountryCode)) {
      $addArr[] = (string)$activityTag->ActivityLocation->Address->CountryCode;
      }
      $dateArr = array();
      $date = (string)$activityTag->Date;//YYYYMMDD
      $dateArr[] = substr($date,0,4);
      $dateArr[] = substr($date,4,2);
      $dateArr[] = substr($date,-2,2);
      $timeArr = array();
      $time = (string)$activityTag->Time;//HHMMSS
      $timeArr[] = substr($time,0,2);
      $timeArr[] = substr($time,2,2);
      $timeArr[] = substr($time,-2,2);
      if($i==1){
      $resultArr['status'] = (string)$activityTag->Status->StatusType->Description;
      $resultArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
      $resultArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
      $resultArr['deliverylocation'] = (string)$activityTag->ActivityLocation->Description;
      $resultArr['signedby'] = (string)$activityTag->ActivityLocation->SignedForByName;
      if ($addArr) {
      $resultArr['deliveryto']=implode(', ',$addArr);
      }
      }else{
      $tempArr=array();
      $tempArr['activity'] = (string)$activityTag->Status->StatusType->Description;
      $tempArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
      $tempArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
      if ($addArr) {
      $tempArr['deliverylocation']=implode(', ',$addArr);
      }
      $packageProgress[] = $tempArr;
      }
      $i++;
      }
      $resultArr['progressdetail'] = $packageProgress;
      }
      } else {
      $arr = $xml->getXpath("//TrackResponse/Response/Error/ErrorDescription/text()");
      $errorTitle = (string)$arr[0][0];
      }
      }
      if (!$this->_result) {
      $this->_result = Mage::getModel('shipping/tracking_result');
      }
      $defaults = $this->getDefaults();
      if ($resultArr) {
      $tracking = Mage::getModel('shipping/tracking_result_status');
      $tracking->setCarrier('ups');
      $tracking->setCarrierTitle($this->getConfigData('title'));
      $tracking->setTracking($trackingvalue);
      $tracking->addData($resultArr);
      $this->_result->append($tracking);
      } else {
      $error = Mage::getModel('shipping/tracking_result_error');
      $error->setCarrier('ups');
      $error->setCarrierTitle($this->getConfigData('title'));
      $error->setTracking($trackingvalue);
      $error->setErrorMessage($errorTitle);
      $this->_result->append($error);
      }
      return $this->_result;
      } */
    /**
     * Establecemos la tienda para usar para las siguientes llamadas
     * @param $store
     * @return mixed
     */
    function setCurrentStore($store)
    {
        // TODO: Implement setCurrentStore() method.
    }
    /**
     * Devolvemos la tienda actual para usar en las llamdas de esta interfaz
     * @return mixed
     */
    function getCurrentStore()
    {
        // TODO: Implement getCurrentStore() method.
    }
}
