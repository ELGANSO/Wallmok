<?php
/*
 * Created on Nov 3, 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class Interactiv4_PackedShipment_Helper_Data extends Mage_Core_Helper_Abstract
{
     public function __construct() {
         $debug = 1;
     }
     
     /*
      * Se devuelve el peso total de un envío. A pesar de tener una columna 
      * total_weight en sales_flat_shipment, parece que no se calcula esta cifra 
      * en magento.
      * @param Mage_Sales_Model_Order_Shipment $shipment
      * @return float
      */
     
     public function getShipmentWeight( Mage_Sales_Model_Order_Shipment $shipment)
     {
	 $shipmentWeight = 0;
	 foreach ($shipment->getAllItems() as $item)
	 {
	     $shipmentWeight += $item->getWeight() * $item->getQty();
	 }
	 return $shipmentWeight;
     }
     
     /**
      * Se devuelve true si debemos transportar el envío en un solo bulto.
      * @param Mage_Sales_Model_Order_Shipment $shipment
      * @return boolean 
      */
     public function mustShipInOneBulto(Mage_Sales_Model_Order_Shipment $shipment)  {
         $carrier = $shipment->getOrder()->getShippingCarrier();
         if ($carrier instanceof Interactiv4_PackedShipment_Model_Carrier_Interface) {
             $shippingMethod = $shipment->getOrder()->getShippingMethod();
             return $carrier->shippingMethodRequiresShipmentsOfOnlyOneBulto($shippingMethod);
         }
         return true;
     }
     
     /**
      * Se devuelve true si el carrier puede calcular costes de envío.
      * @param Mage_Shipping_Model_Carrier_Abstract $carrier
      * @return boolean 
      */
     public function carrierSupportsCalculationOfShippingCosts(Mage_Shipping_Model_Carrier_Abstract $carrier = null) {       
         if ($carrier instanceof Interactiv4_PackedShipment_Model_Carrier_Interface) {
             return $carrier->supportsCalculationOfShippingCosts();
         }
         return false;
     }
     
     /**
      * Se devuelve true si el carrier soporte validación de direcciones (código postal, población)
      * @param Mage_Shipping_Model_Carrier_Abstract $carrier
      * @param string $countryId
      * @return boolean 
      */
     public function carrierSupportsAddressValidation(Mage_Shipping_Model_Carrier_Abstract $carrier = null, $countryId = 'ES') {
         if ($carrier instanceof Interactiv4_PackedShipment_Model_Carrier_Interface) {
             return $carrier->supportsAddressValidation($countryId);
         }
         return false;
     }
     
    /**
     * Sustituimos la plantilla de bultos si el carrier lo soporte.
     * @return string 
     */
    public function changeOrderItemsTemplate() {
        $shipment = $this->_getCurrentShipment(); /* @var $shipment Mage_Sales_Model_Order_Shipment */
        if ($shipment && ($this->carrierSupportsPackedShipment($shipment->getOrder()->getShippingCarrier())) ) {
            return 'i4packedshipment/sales/order/shipment/create/items.phtml';
        } else {
            return Mage::app()->getLayout()->getBlock('order_items')->getTemplate();
        }
    }
    
    /**
     * Se devuelve true si el carrier soporte distribución de bultos.
     * @param mixed $carrier
     * @return boolean
     * @throws Exception
     */
    public function carrierSupportsPackedShipment($carrier = null) {
        if ($carrier instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $carrier;
            $carrier = $shipment->getOrder()->getShippingCarrier();
        } elseif ($carrier instanceof Mage_Sales_Model_Order) {
            $order = $carrier;
            $carrier = $order->getShippingCarrier();
        } elseif ($carrier && !($carrier instanceof Mage_Shipping_Model_Carrier_Abstract)) {
            $message = "Invalid argument of type " . (is_object($carrier) ? get_class($carrier) : gettype($carrier));
            $this->log($message, __METHOD__, __LINE__);
            throw new Exception($message);
        }
        return $carrier instanceof  Interactiv4_PackedShipment_Model_Carrier_Interface;
    }
    
    public function changeAddressValidationJsTemplate() {
        $shipment = $this->_getCurrentShipment(); /* @var $shipment Mage_Sales_Model_Order_Shipment */
        if ($shipment && ($this->carrierSupportsPackedShipment($shipment->getOrder()->getShippingCarrier())) ) {
            return 'i4packedshipment/sales/order/shipment/create/address_validation_info_js.phtml';
        } else {
            return'';
        }        
    }
    
    /**
     *
     * @return boolean 
     */
    public function useDescriptionsInsteadOfReferences() {
        $useDescriptionsInsteadOfReferences = Mage::getStoreConfig("i4packedshipment/useDescriptionsInsteadOfReferences");
        return $useDescriptionsInsteadOfReferences ? true : false;
    }
    
    /**
     *
     * @return boolean 
     */
    public function skipDialogByDefault($store = null) {
        if (!isset($store)) {
            $store = $this->_getCurrentShipment()->getStoreId();
        }
        $carrier = $this->_getCurrentShipment()->getOrder()->getShippingCarrier();
        if ($carrier instanceof Interactiv4_PackedShipment_Model_Carrier_Interface) {
            return $carrier->skipDialog($store);
        } else {
            return false;
        }
       
    }
    
    /**
     * @param boolean $isBulkSend
     * @return \Interactiv4_PackedShipment_Helper_Data 
     */
    public function registerBulkSend($isBulkSend) {
        Mage::unregister('i4packedshipment_bulk_send');
        Mage::register('i4packedshipment_bulk_send', $isBulkSend);
        return $this;
    }
    
    /**
     *
     * @return boolean 
     */
    public function isRegisteredBulkSend() {
        return Mage::registry('i4packedshipment_bulk_send') ? true : false;
    }
    
    /**
     *
     * @return Mage_Sales_Model_Order_Shipment 
     */
    protected function _getCurrentShipment() {
        return Mage::registry('current_shipment');
    }
    
    /**
     *
     * @param string $message
     * @param string $method
     * @param string $line
     * @return \Interactiv4_PackedShipment_Helper_Data 
     */
    public function log($message, $method = "", $line = "") {
        $message = $message . ($method ? " in $method" : "") . ($line ? " on line $line" : "");
        Mage::log($message, null, "i4packedshipment.log");
        return $message;
    }
    
    public function getCarrierLogoPath(Mage_Sales_Model_Order $order) {
        
        $shippingMethod = $order->getShippingMethod();
        $array          = explode('_', $shippingMethod);
        $carrierCode    = $array[0];
        
        $logo = $this->getCarrierLogo($carrierCode);
        
        $path = $carrierCode."/images/".$logo;
        
        return $path;
    }
    
    public function getCarrierLogo($carrierCode) {
        
        $logo = Mage::getStoreConfig("carriers/" . $carrierCode . "/i4packedshipment/logo");
        
        return $logo;
    }
}
