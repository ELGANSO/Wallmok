<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Source_Catalog_Website
{

    public function toOptionArray()
    {
        $options = array();
        
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $options[] = array(
                       'value' => $store->getId(),
                       'label' => $website->getName() . ' (' . $website->getCode() . ') - ' . $store->getName() . ' (' . $store->getCode() . ')' 
                    );
                }
            }
        }
        return $options;
    }
}
