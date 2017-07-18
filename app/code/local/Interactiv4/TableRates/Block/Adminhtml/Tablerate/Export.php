<?php
/**
 * Description of Exportexport
 *
 * @author davidslater
 */
class Interactiv4_TableRates_Block_Adminhtml_Tablerate_Export extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        $this->_objectId = 'tablerate_id';
        $this->_blockGroup = 'i4tablerates';
        $this->_controller = 'adminhtml_tablerate';
        $this->_mode = 'export';
        parent::__construct();
        $this->_updateButton('save', 'label',  Mage::helper('i4tablerates')->__('Export'));
        $this->_removeButton('delete');
        $this->_removeButton('reset');
    }
    public function getHeaderText() {
        return $this->_getHelper()->__('Export').' '.$this->_getHelper()->getGridTitle();
    }
    public function getBackUrl() {
        return $this->getUrl('*/*/', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }
   
    /**
     *
     * @return Interactiv4_TableRates_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('i4tablerates');
    }
    
}
?>
