<?php
class Interactiv4_Mrw_Block_Order_Totals_Cashondeliverysurchage extends Mage_Sales_Block_Order_Totals {
    public function initTotals() {
        $title = Mage::getStoreConfig('payment/i4mrwes_cashondelivery/total_title', $this->_getOrder()->getStoreId());
        if (!round($this->_getAmount(), 2)) {
            return;
        }
        $includingAfter = $this->_getAfter();
        if ($this->_displayBoth() || $this->_displayExcludingTax()) {
            $this->getParentBlock()->addTotal(new Varien_Object(array(
                        'code' => 'i4mrwes_cashondelivery_surcharge',
                        'value' => $this->_getAmount(),
                        'base_value' => $this->_getBaseAmount(),
                        'label' => $title . ($this->_displayBoth() ? ' ' . $this->_getHelper()->__('(Excl.Tax)') : ''),
                    )), $this->_getAfter());
            $includingAfter = 'i4mrwes_cashondelivery_surcharge';
        }
        if ($this->_displayIncludingTax() || $this->_displayBoth()) {
            $this->getParentBlock()->addTotal(new Varien_Object(array(
                        'code' => 'i4mrwes_cashondelivery_surcharge_incl_tax',
                        'value' => $this->_getAmount() + $this->_getTax(),
                        'base_value' => $this->_getBaseAmount() + $this->_getBaseTax(),
                        'label' => $title . ($this->_displayBoth() ? ' ' . $this->_getHelper()->__('(Incl.Tax)') : ''),
                    )), $includingAfter);
        }
    }
    /**
     *
     * @return Mage_Sales_Model_Order 
     */
    protected function _getOrder() {
        return $this->getParentBlock()->getOrder();
    }
    protected function _getStore() {
        return $this->_getOrder()->getStore();
    }
    /**
     *
     * @return string 
     */
    protected function _getAfter() {
        return $this->_displayBoth() ? 'shipping_incl' : 'shipping';
    }
    
    
    /**
     *
     * @return float 
     */
    protected function _getAmount() {
        return $this->_getOrder()->getData('i4mrwes_cashondelivery_surcharge');
    }
    /**
     *
     * @return float 
     */
    protected function _getBaseAmount() {
        return $this->_getOrder()->getData('base_i4mrwes_cashondelivery_surcharge');
    }
    /**
     *
     * @return float 
     */
    protected function _getTax() {
        return $this->_getOrder()->getData('i4mrwes_cashondelivery_surcharge_tax');
    }
    /**
     *
     * @return float 
     */
    protected function _getBaseTax() {
        return $this->_getOrder()->getData('base_i4mrwes_cashondelivery_surcharge_tax');
    }
    /**
     *
     * @return boolean 
     */
    protected function _displayBoth() {
        return $this->_getConfig()->displaySalesShippingBoth($this->_getStore());
    }
    /**
     *
     * @return boolean 
     */
    protected function _displayIncludingTax() {
        return $this->_getConfig()->displaySalesShippingInclTax($this->_getStore());
    }
    /**
     *
     * @return boolean 
     */
    protected function _displayExcludingTax() {
        return $this->_getConfig()->displaySalesShippingExclTax($this->_getStore());
    }
    /**
     *
     * @return Mage_Tax_Model_Config 
     */
    protected function _getConfig() {
        return Mage::getSingleton('tax/config');
    }
    /**
     *
     * @return Interactiv4_DPD_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('i4mrwes');
    }
}
