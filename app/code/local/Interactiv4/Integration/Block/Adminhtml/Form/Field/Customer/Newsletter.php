<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Block_Adminhtml_Form_Field_Customer_Newsletter extends Mage_Core_Block_Html_Select
{

    private $_attributes;

    protected function _getAttributes($attributeId = null)
    {
        if (is_null($this->_attributes)) {
            $this->_attributes = array();
            $collection = Mage::getModel('i4integration/system_config_source_customer_newsletter')->toOptionArray();
            foreach ($collection as $item) {
                $this->_attributes[$item['value']] = $item['label'];
            }
        }
        if (!is_null($attributeId)) {
            return isset($this->_attributes[$attributeId]) ? $this->_attributes[$attributeId] : null;
        }
        return $this->_attributes;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getAttributes() as $attributeId => $attributeLabel) {
                $this->addOption($attributeId, addslashes($attributeLabel));
            }
        }
        return parent::_toHtml();
    }
}
