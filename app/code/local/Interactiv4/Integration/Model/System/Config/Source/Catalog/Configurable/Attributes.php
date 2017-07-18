<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Source_Catalog_Configurable_Attributes extends Mage_Core_Model_Abstract
{

	public function toOptionArray()
    {
    	$collection = Mage::getResourceModel('catalog/product_attribute_collection')
                        ->addFieldToFilter('is_configurable', array('eq' => 1))
                        ->addFieldToFilter('frontend_input', array('eq' => 'select'))
                        ->addFieldToFilter('is_global', array('eq' => 1))
                        ->setOrder('frontend_label', 'asc');
        $options = array();
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