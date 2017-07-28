<?php

class Ontic_Sync_Model_Product_Update extends Mage_Core_Model_Abstract
{
    const Status_Pending = -1;
    const Status_Success = 0;
    const Status_Error = 1;

    protected function _construct()
    {
        $this->_init('onticsync/product_update');
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this['sku'];
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
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            static::Status_Pending => 'Pendiente',
            static::Status_Success => 'Completado',
            static::Status_Error => 'Error'
        ];
    }
}