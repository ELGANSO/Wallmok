<?php
/**
 * Description of Percentage
 *
 * @author davidslater
 */
class Interactiv4_TableRates_Block_Adminhtml_Tablerate_Grid_Renderer_Shippingpercentage extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        if (isset($row->getShippingPercentage())) {
            return $row->getShippingPercentage() . '%';
        } else {
            return '';
        }
    } 
}
?>
