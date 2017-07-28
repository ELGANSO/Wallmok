<?php
class Interactiv4_TableRates_Block_Adminhtml_Tablerate extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_tablerate';
        $this->_blockGroup = 'i4tablerates';
        $this->_headerText = $this->_getHelper()->getGridTitle();
        $this->_addButtonLabel = Mage::helper('i4tablerates')->__('Add Rate');
        
        $this->_addButton('i4import', 
                array(
                    'label' => $this->_getHelper()->__('Import Rates'),
                    'onclick' => "setLocation('{$this->getImportUrl()}')"
                ));
                    
        $this->_addButton('i4export', 
                array(
                    'label' => $this->_getHelper()->__('Export Rates'),
                    'onclick' => "setLocation('{$this->getExportUrl()}')"
                ));                    
        parent::__construct();
        
        
    }
    
    /**
     *
     * @return Interactiv4_TableRates_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('i4tablerates');
    }
    
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }   
    
    public function getImportUrl()
    {
        return $this->getUrl('*/*/import', array("carrier" => $this->_getHelper()->getCarrierCode()));
    } 
    
    public function getExportUrl()
    {
        return $this->getUrl('*/*/export', array("carrier" => $this->_getHelper()->getCarrierCode()));
    }      
    
}
