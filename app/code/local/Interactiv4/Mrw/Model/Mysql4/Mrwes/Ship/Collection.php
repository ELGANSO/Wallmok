<?php
/*
 * Created on Jul 19, 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Interactiv4_Mrw_Model_Mysql4_Mrwes_Ship_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('i4mrwes/mrwes_ship');
	}
    public function setShipmentFilter($orderId)
    {
        $this->addFieldToFilter('entity_id', $orderId);
        return $this;
    }
}
