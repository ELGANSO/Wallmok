<?php
/*
 * Created on Nov 3, 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Interactiv4_Mrw_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     *
     * fichero de log de este modulo
     * @var string
     */
    const LOG_FILE = 'i4mrwes.log';
    /**
     * Wrapper de Mage::log para escribir a nuestro propio fichero de log
     * @param string $message
     * @param int optional $level
     */
    public function log($message, $level = null) {
        //solo hacemos log si la opcion debug es true
        if ($this->getConfigData('debug')) {
            Mage::log($message, $level, self::LOG_FILE);
        }
    }
    /**
     * Wrapper de conveniencia para recuperar valores
     * del config de este modulo.
     *
     * @param string $field
     * @param mixed $store
     */
    public function getConfigData($field, $store = null) {
        $path = 'carriers/i4mrwes/' . $field;
        return Mage::getStoreConfig($path, $store);
    }
    /**
     * Esta funcion devuelve el tipo de via que se ha seleccionado en una direccion
     * @param Mage_Customer_Model_Address $address
     */
    public function getTipoVia(Mage_Customer_Model_Address_Abstract $address = null) {
        $defaultTipoViaCode = Mage::getSingleton('i4mrwes/shipping_carrier_mrw_source_tipovia')->getDefaultTipoViaCode();
        $result = ($address) && $address->getStreet(1) ? $address->getStreet(1) : $defaultTipoViaCode;
	if($result == "Otro")
		$result = $defaultTipoViaCode;
        return $result;
    }
    
    /**
     * Se analizan las partes del street de un address en sus componentes MRW.
     * Devuelve true si se ha convertido con éxito y false si no ha sido 
     * posible convertir - en cual caso se mete todo en el nombreVia y 
     * se pone el código tipo via por defecto.
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param type $codigoTipoVia
     * @param type $nombreVia
     * @param type $numero
     * @param type $resto
     * @return boolean 
     */
    public function getAddressStreetParts(Mage_Customer_Model_Address_Abstract $address, &$codigoTipoVia, &$nombreVia, &$numero, &$resto) {
        $tiposVias = Mage::getSingleton('i4mrwes/shipping_carrier_mrw_source_tipovia'); /* @var $tiposVias Interactiv4_Mrw_Model_Shipping_Carrier_Mrw_Source_Tipovia */
        $codigoTipoVia = $this->getTipoVia($address);
        if ($tiposVias->isValidTipoViaCode($codigoTipoVia)) {
            $nombreVia = $address->getStreet2();
            $numero = $address->getStreet3();
            $resto = $address->getStreet4();
            return true;
        } else {
            $codigoTipoVia = $tiposVias->getDefaultTipoViaCode();
            $nombreVia = implode(' ', $address->getStreet());
            $numero = '-';
            $resto = '';
            return false;
        } 
    }
    /**
     *
     * Esta funcion devuelve un select HTML con los tipos de vía disponibles para una direccion
     * Se utiliza en los formularios de edicion y creacion de customer addresses.
     * @param unknown_type $address
     * @param unknown_type $name
     * @param unknown_type $id
     * @param unknown_type $title
     */
    public function getTipoViaHtmlSelect(Mage_Customer_Model_Address_Abstract $address = null, $name = 'street[]', $id = 'street_1', $title = 'Tipo de vía', $extraParams = '', $class = 'validate-select') {
        Varien_Profiler::start('TEST: ' . __METHOD__);
        $defValue = $this->getTipoVia($address);
        $options = Mage::getSingleton('i4mrwes/shipping_carrier_mrw_source_tipovia')->toOptionArray();
        $layout = Mage::app()->getLayout(); /* @var $layout Mage_Core_Model_Layout */ 
        $html = $layout->createBlock('core/html_select')
                ->setName($name)
                ->setId($id)
                ->setTitle(Mage::helper('i4mrwes')->__($title))
                ->setClass($class)
                ->setValue($defValue)
                ->setOptions($options)
                ->setExtraParams($extraParams)
                ->getHtml();
        Varien_Profiler::stop('TEST: ' . __METHOD__);
        return $html;
    }
    
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return string 
     */
    public function getFormattedValorReembolso(Mage_Sales_Model_Order $order) {
        if ($this->isOrderCashOnDelivery($order)) {
            return number_format($order->getData('base_i4mrwes_cashondelivery_surcharge') + $order->getData('base_i4mrwes_cashondelivery_surcharge_tax'), 2, ',', '');
        } else {
            return '';
        }
    }
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function isOrderCashOnDelivery(Mage_Sales_Model_Order $order) {
        return $order->getPayment()->getMethod() == 'i4mrwes_cashondelivery' ? true : false;
    }
    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal 
     *
     * @param string $value
     * @param int $precision
     * @return boolean |float
     * 
     */
    public function parseDecimalValue($value, $precision = 4) {
        $value = trim($value);
        if (!is_numeric($value)) {
            return false;
        }
        $value = (float) sprintf("%.{$precision}F", $value);
        return $value;
    }    
    /**
     * Parsear una cadena de forma nn.nn% y devuelve el porcentaje como una fracción.
     * Se devuelve false la cadena no tiene la forma correcta.
     * @param type $value
     * @return boolean 
     */
    public function parsePercentageValueAsFraction($value) {
        if (!is_string($value)) {
            return false;
        }
        $value = trim($value);
        if (strlen($value) < 2 || substr($value, -1) != '%') {
            return false;
        }
        $percentage = $this->parseDecimalValue(substr($value, 0, strlen($value) - 1));
        if ($percentage === false) {
            return false;
        }
        return $percentage / 100;
    }
    /**
     * El surcharge de contrareembolso se puede expresar como un precio fijo 
     * o un porcentaje de los bienes envíados. Aquí calculamos el surcharge (base)
     * que cobrar al cliente para un quote especificado. 
     * @param Mage_Sales_Model_Quote $quote
     * @param string $surcharge 
     * @return float
     */
    public function calculateQuoteBaseCashOnDeliverySurcharge(Mage_Sales_Model_Quote $quote, $surcharge) {
        if (!$surcharge || !is_array($surcharge) || !isset($surcharge['cashondelivery_surcharge'])) {
            return 0;
        }
        $baseCashondeliverySurchargePercent = $this->parsePercentageValueAsFraction($surcharge['cashondelivery_surcharge']);
        if ($baseCashondeliverySurchargePercent !== false) {
            $address = $quote->getShippingAddress();
            $taxConfig = Mage::getSingleton('tax/config'); /* @var $taxConfig Mage_Tax_Model_Config */
            $shippingAmount = $taxConfig->shippingPriceIncludesTax($quote->getStore()) ? $address->getBaseShippingInclTax() : $address->getBaseShippingAmount();
            $baseCashondeliverySurcharge = $baseCashondeliverySurchargePercent * ($this->getBaseValueOfShippableGoods($quote) + $shippingAmount);
            if (isset($surcharge['cod_min_surcharge'])) {
                $baseCashondeliverySurcharge = max(array((float) $surcharge['cod_min_surcharge'], $baseCashondeliverySurcharge));
            }
        } else {
            $baseCashondeliverySurcharge = (float) $surcharge['cashondelivery_surcharge'];
        }
        return $baseCashondeliverySurcharge;
    }
    /**
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return float
     */
    public function getBaseValueOfShippableGoods(Mage_Sales_Model_Quote $quote) {
        $baseTotalPrice = 0.0;
        $taxConfig = Mage::getSingleton('tax/config'); /* @var $taxConfig Mage_Tax_Model_Config */
        if ($quote->getAllItems()) {
            foreach ($quote->getAllItems() as $item) { /* @var $item Mage_Sales_Model_Quote_Item */
                if ($item->getProduct()->isVirtual()) {
                    continue;
                }
                $baseTotalPrice += $taxConfig->shippingPriceIncludesTax($quote->getStore()) ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal();
            }
        }
        return $baseTotalPrice;
    }
    
    /**
     *
     * @param mixed $store
     * @return type 
     */
    public function isActive($store = null) {
        return $this->getConfigData("active", $store);
    }
        
    /*     * ******** de aqui hacia abajo funciones que creo tienen que ver con LICENSE ********** */
    /* public function decodeTrackingHash($hash)
      {
      $hash = explode(':', Mage::helper('core')->urlDecode($hash));
      if (count($hash) === 3 && in_array($hash[0], $this->_allowedHashKeys)) {
      return array('key' => $hash[0], 'id' => (int)$hash[1], 'hash' => $hash[2]);
      }
      return array();
      } */
    /* public function getFormatedPrice($price, $currency,$precision=2)
      {
      Mage::log($currency);
      $symbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
      Mage::log($symbol);
      return sprintf("%s%.2f",$symbol,$price);
      } */
    /* private function __validkey($module, $version, $k)
      {
      $host = Mage::helper('core/http')->getHttpHost(false);
      if(in_array(base64_encode(substr($host, 0, strpos($host, '.'))), unserialize('a:5:{i:0;s:4:"ZGV2";i:1;s:8:"dGVzdA==";i:2;s:12:"c3RhZ2luZw==";i:3;s:4:"dWF0";i:4;s:8:"bG9jYWw=";}'))){
      return true;
      }
      return (bool)((string)hash('sha1',md5($module.$version.preg_replace("/^www\./","",$host))."Interactiv4") === (string)$k);
      } */
    /* public function is_key_valid($module_name, $module_version)
      {
      $lowcase_name = strtolower($module_name);
      $major = substr($module_version, 0, strpos($module_version, '.'));
      $key = Mage::getStoreConfig("carriers/i4mrwes/licensekey", Mage::app()->getStore());
      return $this->__validkey($module_name, $major, $key);
      } */
    /* public function getModuleLabel($name, $config)
      {
      $name = str_replace('_', ' ', $name);
      $name .= " (v{$config->version})";
      return $name;
      } */
    /* public function getModules()
      {
      $modules = Mage::getConfig()->getModuleConfig();
      $childmodules = array();
      foreach($modules->children() as $_moduleName => $_module){
      if($_moduleName == 'Interactiv4_Mrw') {
      $childmodules [$_moduleName]= $_module;
      }
      }
      return $childmodules;
      } */
}
