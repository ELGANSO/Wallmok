<?php
 class Interactiv4_Mrw_Model_Mrwes_Ship extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('i4mrwes/mrwes_ship');
    }
	public function addShip($parentId,$url)
	{
		$this->setEntityId($parentId)
			->setUrl($url)
			->setLogFechaAlta(new Zend_Db_Expr('NOW()'))
			->save();
	}
    
	public function getShip($orderId)
	{
		try {
		$ship_collection = Mage::getResourceModel('i4mrwes/mrwes_ship_collection');
		$ship_collection->setShipmentFilter($orderId)
                ->load();
		}
		catch(exception $e)
		{
			Mage::helper('i4mrwes')->log($e->getMessage());
		}
		return $ship_collection;
	}
}
