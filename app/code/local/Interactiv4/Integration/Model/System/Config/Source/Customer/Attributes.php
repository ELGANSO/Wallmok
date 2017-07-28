<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Source_Customer_Attributes
{

    public function toOptionArray($addEmpty = null)
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $collection->setOrder('frontend_label', 'asc');
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