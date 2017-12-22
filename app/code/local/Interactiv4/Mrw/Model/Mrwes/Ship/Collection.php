<?php
class Interactiv4_Mrw_Model_Mrwes_Ship_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('i4mrwes/mrwes_ship');
    }
    public function setShipmentFilter($orderId)
    {
        $this->addAttributeToFilter('parent_id', $orderId);
        return $this;
    }
}
