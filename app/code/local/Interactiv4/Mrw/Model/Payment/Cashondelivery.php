<?php
class Interactiv4_Mrw_Model_Payment_Cashondelivery extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'i4mrwes_cashondelivery';
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
    protected $_surcharge = null;
    protected $_order = null;
    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle() {
        $order = $this->fetchOrder();
        $title = '';
        $store = null;
        if ($order) {
            $title = $this->getConfigData('title', $order->getStoreId());
            $store = $order->getStoreId();            
        } else {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if ($quote) {
                $title = $this->getConfigData('title', $quote->getStoreId());
                $store = $quote->getStoreId();
            }
        }
        $surcharge = $this->getSurchage();
        if ($surcharge > 0) {
            $title = $title . ' (+ ' . Mage::helper('core')->formatCurrency($surcharge, false);
            $taxConfig = Mage::getSingleton('tax/config'); /* @var $taxConfig Mage_Tax_Model_Config */
            if ($taxConfig->shippingPriceIncludesTax($store)) {
                $title .= $this->_getHelper()->__(' incl. tax');
            } else {
                $title .= $this->_getHelper()->__(' excl. tax');
            }
            $title .= ') ';
        }
        return $title;
    }
    /**
     * Check whether payment method can be used
     *
     * TODO: payment method instance is not supposed to know about quote
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null) {
        $checkResult = new StdClass;
        $isActive = (bool) (int) $this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        if (!$isActive || strpos($quote->getShippingAddress()->getShippingMethod(), 'i4mrwes') === false || is_null($this->getSurchage())) {
            $isActive = false;
        }
        $checkResult->isAvailable = $isActive;
        $checkResult->isDeniedInConfig = !$isActive; // for future use in observers
        Mage::dispatchEvent('payment_method_is_active', array(
            'result' => $checkResult,
            'method_instance' => $this,
            'quote' => $quote,
        ));
        // disable method if it cannot implement recurring profiles management and there are recurring items in quote
        if ($checkResult->isAvailable) {
            $implementsRecurring = $this->canManageRecurringProfiles();
            // the $quote->hasRecurringItems() causes big performance impact, thus it has to be called last
            if ($quote && !$implementsRecurring && $quote->hasRecurringItems()) {
                $checkResult->isAvailable = false;
            }
        }
        return $checkResult->isAvailable;
    }
    public function getSurchage() {
        if ($this->_surcharge === null) {
            $order = $this->fetchOrder();
            if ($order) {
                $this->_surcharge = $order->getData('base_i4mrwes_cashondelivery_surcharge');
            } else {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                if ($quote) {
                    $shippingAddress = $quote->getShippingAddress();
                    $request = new Varien_Object();
                    $request->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                    $request->setDestCountryId($shippingAddress->getCountryId());
                    $request->setDestRegionId($shippingAddress->getRegionId());
                    $request->setDestPostcode($shippingAddress->getPostcode());
                    $request->setPackageWeight($shippingAddress->getWeight());
                    if ($this->_getTaxHelper()->shippingPriceIncludesTax($quote->getStoreId())) {
                        $request->setData('i4_table_price', $shippingAddress->getBaseSubtotalInclTax());
                    } else {
                        $request->setData('i4_table_price', $shippingAddress->getBaseSubtotal());
                    }                               
                    $request->setPrice($shippingAddress->getShippingAmount());
                    $request->setMethod(str_replace('i4mrwes_', '', $shippingAddress->getShippingMethod()));
                    $tablerateSurcharge = Mage::getResourceModel('i4mrwes/carrier_tablerate')->getCashondeliverySurchage($request);
                    $this->_surcharge = isset($tablerateSurcharge) ? $this->_getHelper()->calculateQuoteBaseCashOnDeliverySurcharge($quote, $tablerateSurcharge) : null;
                }
            }
        }
        return $this->_surcharge;
    }
    
    /**
     *
     * @return Mage_Tax_Helper_Data
     */
    protected function _getTaxHelper() {
        return Mage::helper('tax');        
    }      
    public function fetchOrder() {
        if (is_null($this->_order)) {
            if (Mage::app()->getStore()->isAdmin()) {
                $this->_order = Mage::registry('current_order');
                if (!$order && Mage::app()->getRequest()->getParam('order_id')) {
                    $this->_order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
                }
            } else {
                $order_id = Mage::app()->getRequest()->getParam('order_id');
                if ($order_id) {
                    $this->_order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
                }
            }
        }
        return $this->_order;
    }
    
    /**
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountry($country) {
        $canUseForCountry = parent::canUseForCountry($country) && in_array($country, Interactiv4_Mrw_Model_Payment_Cashondelivery_Source_Country::getAllAllowedCountries());
        return $canUseForCountry ? true : false;
    }
    
    /**
     *
     * @return Interactiv4_Mrw_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4mrwes');
    }    
    
}
?>
