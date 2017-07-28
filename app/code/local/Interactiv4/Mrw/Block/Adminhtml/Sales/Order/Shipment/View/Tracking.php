<?php
class Interactiv4_Mrw_Block_Adminhtml_Sales_Order_Shipment_View_Tracking extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Tracking
//extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Tracking
//Mage_Adminhtml_Block_Sales_Order_Abstract
{
	public function isMrwes()
	{
		$shipping = explode('_',$this->getShipment()->getOrder()->getShippingMethod());
		if($shipping[0]=='i4mrwes') {
			return true;
		}
		else {
			return false;
		}
	}
	public function getShipInfo()
	{
		$id = $this->getShipment()->getId();
		$ships = Mage::getModel('i4mrwes/mrwes_ship')->getShip($id);
		return $ships->getFirstItem();
	}
}
