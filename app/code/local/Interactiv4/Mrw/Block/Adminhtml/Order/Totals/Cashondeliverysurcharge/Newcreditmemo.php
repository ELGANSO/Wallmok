<?php
/**
 * Description of Newcreditmemo
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Block_Adminhtml_Order_Totals_Cashondeliverysurcharge_Newcreditmemo extends Interactiv4_Mrw_Block_Order_Creditmemo_Totals_Cashondeliverysurcharge {
    /**
     * En admin cuando creamos un abono nuevo, posicionamos correctamente el sobrecargo contrareembolso en los totales.
     * @return string 
     */
    protected function _getAfter() {
        return $this->_getConfig()->displaySalesSubtotalBoth($this->_getStore()) ?'subtotal_incl' : 'subtotal';
    }    
    
    
    
}
?>
