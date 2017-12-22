<?php
/**
 * Description of Cashondeliverysurcharge
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Block_Order_Creditmemo_Totals_Cashondeliverysurcharge extends Interactiv4_Mrw_Block_Order_Totals_Cashondeliverysurchage {
    /**
     *
     * @return float 
     */
    protected function _getAmount() {
        return $this->_getCreditmemo()->getData('i4mrwes_cashondelivery_surcharge');
    }
    
    /**
     *
     * @return float 
     */
    protected function _getBaseAmount() {
        return $this->_getCreditmemo()->getData('base_i4mrwes_cashondelivery_surcharge');
    }
    
    /**
     *
     * @return type 
     */
    protected function _getCreditmemo() {
        return $this->getParentBlock()->getCreditmemo();
    }
    
    /**
     *
     * @return float 
     */
    protected function _getBaseTax() {
        return $this->_getCreditmemo()->getData('base_i4mrwes_cashondelivery_surcharge_tax');
    }
    
    /**
     *
     * @return float 
     */
    protected function _getTax() {
        return $this->_getCreditmemo()->getData('i4mrwes_cashondelivery_surcharge_tax');
    }
}
?>
