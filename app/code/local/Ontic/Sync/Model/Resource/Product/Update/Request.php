<?php

class Ontic_Sync_Model_Resource_Product_Update_Request extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('onticsync/product_update_request', 'request_id');
    }
}