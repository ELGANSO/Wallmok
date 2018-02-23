<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Source_Customer_Address
{

    public function toOptionArray($addEmpty = null)
    {
        $collection = Mage::getResourceModel('customer/address_attribute_collection')->load();
        $options = array();
        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select an Attribute --'),
                'value' => ''
            );
        }
        foreach ($collection as $attribute) {
            if ($attribute->getFrontendLabel()) {
                $options[] = array(
                   'label' => $attribute->getFrontendLabel() . ' (' . $attribute->getName() . ')',
                   'value' => $attribute->getName()
                );
            }
        }
        return $options;
    }
    
}