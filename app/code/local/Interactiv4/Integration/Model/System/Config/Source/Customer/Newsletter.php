<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Source_Customer_Newsletter
{

    public function toOptionArray($addEmpty = null)
    {
        $collection = array();
        $collection[] = array(
            "name" => "subscriber_status", "label" => "Subscriber status"
        );

        $options = array();
        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select an Attribute --'),
                'value' => ''
            );
        }
        foreach ($collection as $attribute) {
            $options[] = array(
               'label' => $attribute["label"] . ' (' . $attribute["name"] . ')',
               'value' => $attribute["name"]
            );
        }
        return $options;
    }
}
