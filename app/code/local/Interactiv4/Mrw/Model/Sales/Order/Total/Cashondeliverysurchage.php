<?php
class Interactiv4_Mrw_Model_Sales_Order_Total_Cashondeliverysurchage extends Mage_Sales_Model_Order_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order $order)
    {
    	$order->setData('i4mrw_cashondelivery_surcharge', 0);
        $order->setData('base_i4mrw_cashondelivery_surcharge', 0);
    	$amount = $order->getOrder()->getData('i4mrw_cashondelivery_surcharge');
        $order->setData('i4mrw_cashondelivery_surcharge', $amount);
        $amount = $order->getOrder()->getData('base_i4mrw_cashondelivery_surcharge');
        $order->setData('base_i4mrw_cashondelivery_surcharge', $amount);
        $order->setGrandTotal($order->getGrandTotal() + $order->getData('i4mrw_cashondelivery_surcharge'));
        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getData('base_i4mrw_cashondelivery_surcharge'));
        return $this;
    }
}
