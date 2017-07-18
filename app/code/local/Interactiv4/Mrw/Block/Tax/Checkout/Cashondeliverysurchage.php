<?php
class Interactiv4_Mrw_Block_Tax_Checkout_Cashondeliverysurchage extends Mage_Checkout_Block_Total_Default {
    protected $_template = 'i4mrwes/tax/checkout/cashondeliverysurchage.phtml';
    /**
     *
     * @return float
     */
    protected function _getCashOnDeliverySurcharge() {
        return $this->getTotal()->getAddress()->getData('i4mrwes_cashondelivery_surcharge');
    }
    /**
     *
     * @return float
     */
    protected function _getCashOnDeliverySurchargeTax() {
        return $this->getTotal()->getAddress()->getData('i4mrwes_cashondelivery_surcharge_tax');
    }
    /**
     * Check if we need display shipping include and exclude tax
     *
     * @return bool
     */
    public function displayBoth() {
        return Mage::getSingleton('tax/config')->displayCartShippingBoth($this->getStore());
    }
    /**
     * Check if we need display shipping include tax
     *
     * @return bool
     */
    public function displayIncludeTax() {
        return Mage::getSingleton('tax/config')->displayCartShippingInclTax($this->getStore());
    }
    /**
     * Get label for cash on delivery surcharge including tax
     *
     * @return float
     */
    public function getIncludeTaxLabel() {
        return $this->_getTitle() . $this->_getHelper()->__(' incl. tax');
    }
    /**
     * Get label for cash on delivery surcharge excluding tax
     *
     * @return float
     */
    public function getExcludeTaxLabel() {
        return $this->_getTitle() . $this->_getHelper()->__(' excl. tax');
    }
    /**
     * Usa mejor getCashOnDeliverySurchargeExcludeTax que este método tiene typo.
     * @deprecated
     * @return float 
     */
    public function getCashondeliverySurchageExcludeTax() {
        return $this->getTotal()->getAddress()->getData('i4mrwes_cashondelivery_surcharge');
    }
    /**
     * 
     * @return float 
     */
    public function getCashOnDeliverySurchargeExcludeTax() {
        return $this->_getCashOnDeliverySurcharge();
    }
    /**
     *
     * @return float
     */
    public function getCashOnDeliverySurchargeIncludeTax() {
        return $this->_getCashOnDeliverySurcharge() + $this->_getCashOnDeliverySurchargeTax();
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
     * @return string 
     */
    protected function _getTitle() {
        return $this->getTotal()->getTitle();
    }
}
