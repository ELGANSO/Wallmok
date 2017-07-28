<?php
class Interactiv4_Mrw_Model_Sales_Order_Pdf_Total_Cashondeliverysurchage extends Mage_Sales_Model_Order_Pdf_Total_Default {
    public function getTotalsForDisplay() {
        $amount = $this->getOrder()->getData('i4mrwes_cashondelivery_surcharge');
        if (floatval($amount)) {
            if ($this->getAmountPrefix()) {
                $discount = $this->getAmountPrefix() . $discount;
            }
            $title = Mage::getStoreConfig('payment/i4mrwes_cashondelivery/total_title', $this->getOrder()->getStoreId());
            $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
            $totals = array();
            if ($this->_displayBoth() || $this->_displayExcludingTax()) {
                $totals[] = array(
                    'label' => $title . ($this->_displayBoth() ? $this->_getHelper()->__(' (Excl. Tax)') : '') . ':',
                    'amount' => $this->getOrder()->formatPriceTxt($amount),
                    'font_size' => $fontSize,
                );
            }
            if ($this->_displayBoth() || $this->_displayIncludingTax()) {
                $amount += $this->getOrder()->getData('i4mrwes_cashondelivery_surcharge_tax');
                $totals[] = array(
                    'label' => $title .  ($this->_displayBoth() ? $this->_getHelper()->__(' (Incl. Tax)') : '') . ':',
                    'amount' => $this->getOrder()->formatPriceTxt($amount),
                    'font_size' => $fontSize,
                );
            }
            return $totals;
        }
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
     * @return Interactiv4_Mrw_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('i4mrwes');
    }
    /**
     *
     * @return Mage_Core_Model_Store 
     */
    protected function _getStore() {
        return $this->getOrder()->getStore();
    }
}
