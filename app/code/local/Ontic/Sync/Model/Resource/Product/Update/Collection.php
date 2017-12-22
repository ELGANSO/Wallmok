<?php

class Ontic_Sync_Model_Resource_Product_Update_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('onticsync/product_update');
    }
}