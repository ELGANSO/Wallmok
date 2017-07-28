<?php
/*
 * Created on Jul 25, 2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class Interactiv4_Mrw_Model_Shipping_Carrier_Mrw_Source_Mode
{
    public function toOptionArray()
    {
        return array(
        	Interactiv4_Mrw_Model_Observer_Shipment::LIVE_MODE => Mage::helper('i4mrwes')->__('Live'),
        	Interactiv4_Mrw_Model_Observer_Shipment::DEVELOPER_MODE => Mage::helper('i4mrwes')->__('Development'),
        );
    }
}

