<?php

class Ontic_Sync_Model_Product_Update_Request extends Mage_Core_Model_Abstract
{
    const Status_Pending = 0;
    const Status_Processing = 1;
    const Status_Finished = 2;

    protected function _construct()
    {
        $this->_init('onticsync/product_update_request');
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData('status', $status);
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return static::getStatusCodes()[$this['status']];
    }

    /**
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract|Ontic_Sync_Model_Product_Update[]
     */
    public function getAllUpdates()
    {
        return Mage::getModel('onticsync/product_update')
            ->getCollection()
            ->addFieldToFilter('request_id', [ 'eq' => $this->getId() ]);
    }

    /**
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract|Ontic_Sync_Model_Product_Update[]
     */
    public function getAllPendingUpdates()
    {
        return $this
            ->getAllUpdates()
            ->addFieldToFilter('status', [ 'eq' => Ontic_Sync_Model_Product_Update::Status_Pending ]);
    }

    /**
     * @return array
     */
    public static function getStatusCodes()
    {
        return [
            static::Status_Pending => 'pending',
            static::Status_Processing => 'processing',
            static::Status_Finished => 'finished'
        ];
    }
}