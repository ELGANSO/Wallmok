<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of Form
 *
 * @author davidslater
 */
class Interactiv4_TableRates_Block_Adminhtml_Tablerate_Import_Form extends Mage_Adminhtml_Block_Widget_Form {
    /**
     * @var array
     */
    public function __construct() {
        parent::__construct();
    }    
    
   protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/importrates', array('tablerate_id' => $this->getRequest()->getParam('tablerate_id'), 'carrier' => $this->_getHelper()->getCarrierCode())),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ));
        $this->setForm($form);
        $fieldset = $form->addFieldset('base_fieldset',array());
        $fieldset->addField('website_id', 'select', array(
            'name' => 'website_id',
            'label' => $this->_getHelper()->__('Website'),
            'values' => Mage::getSingleton('i4tablerates/source_website')->toOptionArray(),
            'required' => true
        ));         
        
        $fieldset->addField('import', 'file', array(
            'name' => 'import',
            'label' => $this->_getHelper()->__('Import Rates'),
            'required' => true,
            
        ));        
        
        
        
        
        
        $form->setUseContainer(true);
        return parent::_prepareForm();
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
