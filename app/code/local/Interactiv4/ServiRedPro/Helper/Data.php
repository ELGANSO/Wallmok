<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_Helper_Data extends Mage_Core_Helper_Abstract {
    const SERVIRED_DS_ORDER_REQUIRED_LENGTH = 12;
    protected $errorMessage = array(
        "SIS0007" => "Error al desmontar el XML de entrada",
        "SIS0008" => "Error falta Ds_Merchant_MerchantCode",
        "SIS0009" => "Error de formato en Ds_Merchant_MerchantCode",
        "SIS0010" => "Error falta Ds_Merchant_Terminal",
        "SIS0011" => "Error de formato en Ds_Merchant_Terminal",
        "SIS0014" => " Error de formato en Ds_Merchant_Order",
        "SIS0015" => " Error falta Ds_Merchant_Currency",
        "SIS0016" => "Error de formato en Ds_Merchant_Currency",
        "SIS0017" => "Error no se admiten operaciones en pesetas",
        "SIS0018" => "Error falta Ds_Merchant_Amount",
        "SIS0019" => "Error de formato en Ds_Merchant_Amount",
        "SIS0020" => "Error falta Ds_Merchant_MerchantSignature",
        "SIS0021" => "Error la Ds_Merchant_MerchantSignature viene vacía",
        "SIS0022" => "Error de formato en Ds_Merchant_TransactionType",
        "SIS0023" => "Error Ds_Merchant_TransactionType desconocido",
        "SIS0024" => "Error Ds_Merchant_ConsumerLanguage tiene mas de 3 posiciones",
        "SIS0025" => "Error de formato en Ds_Merchant_ConsumerLanguage",
        "SIS0026" => "Error No existe el comercio / terminal enviado",
        "SIS0027" => "Error Moneda enviada por el comercio es diferente a la que tiene asignada para ese terminal ",
        "SIS0028" => "Error Comercio / terminal está dado de baja",
        "SIS0030" => "Error en un pago con tarjeta ha llegado un tipo de operación que no es ni pago ni preautorización ",
        "SIS0031" => "Método de pago no definido",
        "SIS0033" => "Error en un pago con móvil ha llegado un tipo de operación que no es ni pago ni preautorización ",
        "SIS0034" => "Error de acceso a la Base de Datos",
        "SIS0037" => "El número de teléfono no es válido",
        "SIS0038" => "Error en java",
        "SIS0040" => "Error el comercio / terminal no tiene ningún método de pago asignado ",
        "SIS0041" => "Error en el cálculo de la HASH de datos del comercio.",
        "SIS0042" => "La firma enviada no es correcta",
        "SIS0043" => "Error al realizar la notificación on-line",
        "SIS0046" => "El bin de la tarjeta no está dado de alta",
        "SIS0051" => "Error número de pedido repetido",
        "SIS0054" => "Error no existe operación sobre la que realizar la devolución",
        "SIS0055" => "Error existe más de un pago con el mismo número de pedido",
        "SIS0056" => "La operación sobre la que se desea devolver no está autorizada",
        "SIS0057" => "El importe a devolver supera el permitido",
        "SIS0058" => "Inconsistencia de datos, en la validación de una confirmación",
        "SIS0059" => "Error no existe operación sobre la que realizar la confirmación",
        "SIS0060" => "Ya existe una confirmación asociada a la preautorización",
        "SIS0061" => "La preautorización sobre la que se desea confirmar no está autorizada ",
        "SIS0062" => "El importe a confirmar supera el permitido",
        "SIS0063" => "Error. Número de tarjeta no disponible",
        "SIS0064" => "Error. El número de tarjeta no puede tener más de 19 posiciones",
        "SIS0065" => "Error. El número de tarjeta no es numérico",
        "SIS0066" => "Error. Mes de caducidad no disponible",
        "SIS0067" => "Error. El mes de la caducidad no es numérico",
        "SIS0068" => "Error. El mes de la caducidad no es válido",
        "SIS0069" => "Error. Año de caducidad no disponible",
        "SIS0070" => "Error. El Año de la caducidad no es numérico",
        "SIS0071" => "Tarjeta caducada",
        "SIS0072" => "Operación no anulable",
        "SIS0074" => "Error falta Ds_Merchant_Order",
        "SIS0075" => "Error el Ds_Merchant_Order tiene menos de 4 posiciones o más de 12 ",
        "SIS0076" => "Error el Ds_Merchant_Order no tiene las cuatro primeras posiciones numéricas ",
        "SIS0077" => "Error el Ds_Merchant_Order no tiene las cuatro primeras posiciones numéricas. No se utiliza ",
        "SIS0078" => "Método de pago no disponible",
        "SIS0079" => "Error al realizar el pago con tarjeta",
        "SIS0081" => "La sesión es nueva, se han perdido los datos almacenados",
        "SIS0084" => "El valor de Ds_Merchant_Conciliation es nulo",
        "SIS0085" => "El valor de Ds_Merchant_Conciliation no es numérico",
        "SIS0086" => "El valor de Ds_Merchant_Conciliation no ocupa 6 posiciones",
        "SIS0089" => "El valor de Ds_Merchant_ExpiryDate no ocupa 4 posiciones",
        "SIS0092" => "El valor de Ds_Merchant_ExpiryDate es nulo",
        "SIS0093" => "Tarjeta no encontrada en la tabla de rangos",
        "SIS0094" => "La tarjeta no fue autenticada como 3D Secure",
        "SIS0097" => "Valor del campo Ds_Merchant_CComercio no válido",
        "SIS0098" => "Valor del campo Ds_Merchant_CVentana no válido",
        "SIS0112" => "Error El tipo de transacción especificado en Ds_Merchant_Transaction_Type no esta permitido ",
        "SIS0113" => "Excepción producida en el servlet de operaciones",
        "SIS0114" => "Error, se ha llamado con un GET en lugar de un POST",
        "SIS0115" => "Error no existe operación sobre la que realizar el pago de la cuota",
        "SIS0116" => "La operación sobre la que se desea pagar una cuota no es una operación válida ",
        "SIS0117" => "La operación sobre la que se desea pagar una cuota no está autorizada ",
        "SIS0118" => "Se ha excedido el importe total de las cuotas",
        "SIS0119" => "Valor del campo Ds_Merchant_DateFrecuency no válido",
        "SIS0120" => "Valor del campo Ds_Merchant_ChargeExpiryDate no válido",
        "SIS0121" => "Valor del campo Ds_Merchant_SumTotal no válido",
        "SIS0122" => "Valor del campo Ds_Merchant_DateFrecuency Ds_Merchant_SumTotal tiene formato incorrecto",
        "SIS0123" => "Se ha excedido la fecha tope para realizar transacciones",
        "SIS0124" => "No ha transcurrido la frecuencia mínima en un pago recurrente sucesivo",
        "SIS0132" => "La fecha de Confirmación de Autorización no puede superar en mas de 7 días a la de Preautorización.",
        "SIS0133" => "La fecha de Confirmación de Autenticación no puede superar en mas de 45 días a la de Autenticación Previa.",
        "SIS0139" => "Error el pago recurrente inicial está duplicado",
        "SIS0142" => "Tiempo excedido para el pago",
        "SIS0197" => "Error al obtener los datos de cesta de la compra en operación tipo pasarela",
        "SIS0198" => "Error el importe supera el límite permitido para el comercio",
        "SIS0199" => "Error el número de operaciones supera el límite permitido para el comercio ",
        "SIS0200" => "Error el importe acumulado supera el límite permitido para el comercio ",
        "SIS0214" => "El comercio no admite devoluciones",
        "SIS0216" => "Error Ds_Merchant_CVV2 tiene mas de 3 posiciones",
        "SIS0217" => "Error de formato en Ds_Merchant_CVV2",
        "SIS0218" => "El comercio no permite operaciones seguras por la entrada/operaciones ",
        "SIS0219" => "Error el número de operaciones de la tarjeta supera el límite",
        "SIS0220" => "Error el importe acumulado de la tarjeta supera el límite permitido para el comercio",
        "SIS0221" => "Error el CVV2 es obligatorio",
        "SIS0222" => "Ya existe una anulación asociada a la preautorización",
        "SIS0223" => "La preautorización que se desea anular no está autorizada",
        "SIS0224" => "El comercio no permite anulaciones por no tener firma ampliada",
        "SIS0225" => "Error no existe operación sobre la que realizar la anulación",
        "SIS0226" => "Inconsistencia de datos, en la validación de una anulación",
        "SIS0227" => "Valor del campo Ds_Merchant_TransactionDate no válido",
        "SIS0229" => "No existe el código de pago aplazado solicitado",
        "SIS0252" => "El comercio no permite el envío de tarjeta",
        "SIS0253" => "La tarjeta no cumple el check-digit",
        "SIS0254" => "El número de operaciones de la IP supera el límite permitido por el comercio ",
        "SIS0255" => "El importe acumulado por la IP supera el límite permitido por el comercio ",
        "SIS0256" => "El comercio no puede realizar preautorizaciones",
        "SIS0257" => "Esta tarjeta no permite operativa de preautorizaciones",
        "SIS0258" => "Inconsistencia de datos, en la validación de una confirmación",
        "SIS0261" => "Operación detenida por superar el control de restricciones en la entrada al SIS",
        "SIS0270" => "El comercio no puede realizar autorizaciones en diferido",
        "SIS0274" => "Tipo de operación desconocida o no permitida por esta entrada al SIS ",
    );
    public function getCheckoutMessage() {
        return Mage::getStoreConfig('payment/serviredpro/checkoutmessage');
    }
    /**
     *
     * @return string 
     */
    public function getImage() {
        $imageFile = Mage::getStoreConfig('payment/serviredpro/placeholder');
        if ($imageFile) {
            $prefix = Mage::getBaseUrl('media') . 'serviredpro/';
            return $prefix . $imageFile;
        } else {
            return '';
        }
    }
    public function getErrorDescription($errorId) {
        return $this->errorMessage[$errorId];
    }
    /**
     *
     * @param string $msg
     * @return \Interactiv4_ServiRedPro_Helper_Data 
     */
    public function log($msg) {
        Mage::log($msg, null, 'serviredpro.log');
        return $this;
    }
    /**
     *
     * @return string 
     */
    protected function _getServiceUrl($operation) {
        $serviceUrlOverride = $this->getConfigValue("serviceurloverride");
        if (!$serviceUrlOverride || ($serviceUrlOverride == Interactiv4_ServiRedPro_Model_Config_Source_Urls::URL_DEFAULT)) {
            $service = $this->getConfigValue("banksettings/{$this->getBank()}/serviceurl");
            if (!$service) {
                $service = $this->getConfigValue("banksettings/" . Interactiv4_ServiRedPro_Model_Config_Source_Banks::BANK_DEFAULT . "/serviceurl");
            }
        } else {
            $service = $serviceUrlOverride;
        }
        if ($this->isProductionEnvironment()) {
            $url = $this->getConfigValue("serviceurl/$service/productionurl");
        } else {
            $url = $this->getConfigValue("serviceurl/$service/testurl");
        }
        if ($operation) {
            $url .= $operation;
        }
        return $url;
    }
    /**
     *
     * @return string
     */
    public function getServiredUrl() {
        $serviredUrl = $this->_getServiceUrl("realizarPago");
        return $serviredUrl;
    }
    /**
     *
     * @return string
     */
    public function getServiredProBackendUrl() {
        $serviredProUrl = $this->_getServiceUrl("operaciones");
        return $serviredProUrl;
    }
    /**
     * Se devuelve true si estamos usando el entorno real de trabajo del TPV.
     * @return boolean
     */
    public function isProductionEnvironment() {
        $isProductionEnvironment = $this->getConfigValue("urlservired");
        return $isProductionEnvironment ? true : false;
    }
    /**
     *
     * @param string $field
     * @return string 
     */
    public function getConfigValue($field, $store = null) {
        $configValue = Mage::getStoreConfig("payment/serviredpro/$field", $store);
        return $configValue;
    }
    /**
     * Se devuelve el nombre del banco que uso el usuario.
     * @return string
     */
    public function getBank() {
        $bank = $this->getConfigValue("bank");
        $bank = $bank ? $bank : Interactiv4_ServiRedPro_Model_Config_Source_Banks::BANK_DEFAULT;
        return $bank;
    }
    /**
     *
     * @param mixed $store
     * @return boolean 
     */
    public function isActiveServired($store) {
        return Mage::getStoreConfig("payment/serviredpro/active", $store) ? true : false;
    }
    /**
     * Se devuelve la referencia del pedido pasado a servired, dado el incrementId del 
     * pedido y el store desde que llamamos.
     * @deprecated since 2.2.0
     * @param type $store
     * @param type $orderNum
     * @return type 
     */
    public function getDsOrderParam($store, $orderIncrementId) {
        return $this->getConfigValue('prefijo', $store) . $orderIncrementId;
    }
    /**
     * Generamos una referencia única para el pedido para pasar a servired.
     * Se va el parámetro DS_ORDER. 
     * @param Mage_Sales_Model_Order $order
     * @return string 
     */
    public function generateServiredOrderReference(Mage_Sales_Model_Order $order) {
        $this->log(__METHOD__);
        $serviredOrderReference = $order->getData('i4servired_order_reference');
        if (!$serviredOrderReference) {
            $prefix = $this->getConfigValue('prefijo', $order->getStore());
            if (!$prefix) {
                $prefix = '';
            }
            // Si el prefijo juntado con el increment id del pedido cabe en el espacio
            // permitido por Servired, basamos la referencia en el incrementid.
            // Si no, la basamos en el id interno del pedido.
            if (strlen($order->getIncrementId()) + strlen($prefix) <= self::SERVIRED_DS_ORDER_REQUIRED_LENGTH) {
                $seed = $order->getIncrementId();
            } else {
                $seed = $order->getId();
                // Si aún no cabe, es porque el prefijo es demasiado largo.
                // No se puede usar.
                if (strlen($prefix) + strlen($seed) > self::SERVIRED_DS_ORDER_REQUIRED_LENGTH) {
                    $this->log(sprintf("Order reference prefix for store %s ('%s') is too long given the length of the Magento order reference number. The prefix will be ignored.", $order->getStore()->getCode(), $prefix));
                    $prefix = '';
                }
            }
            
            // Formamos la referencia por el prefijo más el seed ampliado con espacios para llenar la longitud requerida.
            $suffix = str_pad($seed, self::SERVIRED_DS_ORDER_REQUIRED_LENGTH - strlen($prefix), "0", STR_PAD_LEFT);
            $serviredOrderReference = $prefix . $suffix;
            
            $order->setData('i4servired_order_reference', $serviredOrderReference);
        }
        return $serviredOrderReference;
    }
    
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return string 
     */
    public function getServiredOrderReference(Mage_Sales_Model_Order $order) {
        $serviredOrderReference = $order->getData('i4servired_order_reference');
        if (!$serviredOrderReference) {
            $serviredOrderReference = $this->generateServiredOrderReference($order);
        }
        return $serviredOrderReference;
    }
    
    
    
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $params
     * @return \Interactiv4_ServiRedPro_Helper_Data 
     */
    public function setOrderServiredParams(Mage_Sales_Model_Order $order, array $params) {
        $order->setData('i4servired_params', serialize($params));
        return $this;
    }
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return array|boolean 
     */
    public function getOrderServiredParams(Mage_Sales_Model_Order $order) {
        $params = $order->getData('i4servired_params');
        if (!is_string($params)) {
            return false;
        }
        $params = @unserialize($params);
        return is_array($params) ? $params : false;
    }
    /**
     *
     * @param array $paramSet1
     * @param array $paramSet2
     * @param array $keys
     * @return boolean 
     */
    public function compareServiredParamSets($paramSet1, $paramSet2, array $keys = null) {
        if (!is_array($paramSet1) || (!is_array($paramSet2))) {
            return false;
        }
        if (!$keys) {
            $keys = array('Ds_TransactionType',
                'Ds_Card_Country',
                'Ds_Date',
                'Ds_SecurePayment',
                'Ds_Signature',
                'Ds_Order',
                'Ds_Response',
                'Ds_AuthorisationCode');
        }
        foreach ($keys as $key) {
            if (!array_key_exists($key, $paramSet1) || !array_key_exists($key, $paramSet2) || ($paramSet1[$key] != $paramSet2[$key])) {
                return false;
            }
        }
        return true;
    }
    /**
     *
     * @param array $params
     * @return string 
     */
    public function serviredParamsToString($params) {
        if (!is_array($params) || (!$params)) {
            return $this->__('No data received for transaction.');
        }
        $result = '';
        foreach ($params as $key => $value) {
            $result .= "$key: $value</br>";
        }
        return $result;
    }
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $commentReason
     * @return \Interactiv4_ServiRedPro_Helper_Data 
     */
    public function setSuspectedFraud(Mage_Sales_Model_Order $order, $commentReason = '') {
        $order->setState('payment_review', 'fraud', $commentReason);
        return $this;
    }
}
