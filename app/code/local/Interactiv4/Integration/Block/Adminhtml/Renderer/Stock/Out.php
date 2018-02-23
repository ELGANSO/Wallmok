<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */
 
class Interactiv4_Integration_Block_Adminhtml_Renderer_Stock_Out extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    
    protected $_groupRenderer;

    public function __construct() {
	   parent::__construct();
        $this->setTemplate('i4integration/config/form/field/sync/stock/out.phtml');
    }

    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'i4integration/adminhtml_form_field_product_attributes', '',
                array('is_render_to_js_template' => true)
            );
            $this->_groupRenderer->setClass('i4integration_stock_select');
            $this->_groupRenderer->setExtraParams('style="width:280px"');
        }
        return $this->_groupRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn('local', array(
            'label' => Mage::helper('i4integration')->__('Local'),
            'renderer' => $this->_getGroupRenderer(),
        ));
        $this->addColumn('remote', array(
            'label' => Mage::helper('i4integration')->__('Remote'),
            'style' => 'width:100px',
        ));
        $this->addColumn('position', array(
            'label' => Mage::helper('i4integration')->__('Position'),
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('i4integration')->__('Add an Attribute');
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('local')),
            'selected="selected"'
        );
    }

}
