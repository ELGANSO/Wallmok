<?php

namespace Ontic\SyncApi\Controllers;

use Mage;
use Ontic\SyncApi\BaseController;
use Symfony\Component\HttpFoundation\Response;

class SetOrderStateController extends BaseController
{
	protected $states = array("processing", "complete", "closed", "canceled" );
    /**
     * @return Response
     */
    function defaultAction()
    {
           // Decodificamos el cuerpo de la petición
        if(!($data = json_decode($this->getRequest()->getContent(), true)))
        {
            return new Response('400 Bad Request<br>Invalid JSON', 400);
        }
         foreach($data as $orderData)
        {
	        /** @var \Mage_Sales_Model_Order $order */
	        if(isset($orderData['entity_id']) && !empty($orderData['entity_id']))
	        {
	        	$orderId = $orderData['entity_id'];
	        	$order = Mage::getModel('sales/order')->load($orderId);
	        }else{
	        	$order = Mage::getModel('sales/order')->loadByIncrementId($orderData['increment_id']);
	        }
	        

	        if($order->isObjectNew())
	        {
	            return new Response('404 Not Found', 404);
	        }
	        //Si el estado nuevo esta permitido, lo cambia
	        if(in_array($orderData["status"],$this->states))
	        {
	        	$this->changeState($order, $orderData["status"]);
	        }else{
	        	return new Response('400 Bad Request<br>Invalid JSON', 400);
	        }
	        
	        //Si puede genera envío, si no solamente cambia el estado
	        if(!empty($orderData['trackingId']) && $order->canShip()){

	        	$this->addShipment($order, $orderData["trackingId"]);
	        }

	        $order->save();
	    }
        return new Response('', 200);
    }

    protected function changeState($order, $state){

    	//$order->setData('status', $state."lf");
    	$prevState = $order->getStatusLabel();
    	$order->setData('status', $state);
        $order->setData('state', $state);
        $history = $order->addStatusHistoryComment($state, false);
        //$history = $order->addStatusHistoryComment($state.' by Logisfashion', false);
        Mage::log($prevState,null,"ivan.log");
        if($state == "canceled"){
        	foreach ($order->getAllItems() as $item) {
        		
			    $item->setQtyCanceled($item->getData('qty_ordered'));
			    $item->save();
			}
        }else if($prevState == "Canceled" && $state == "processing"){
        	foreach ($order->getAllItems() as $item) {
			    $item->setQtyCanceled(0);
			    $item->save();
			}
        }
        $history->setIsCustomerNotified(false);
        return true;
    }

    protected function addShipment($order, $trackingNumber){

    	$itemQty =  $order->getItemsCollection()->count();
		$shipment = Mage::getModel('sales/service_order', $order)
                    ->prepareShipment($itemQty);
	  	$shipment = $order->prepareShipment();
	  	$arrTracking = array(
            'carrier_code' => isset($carrier_code) ? $carrier_code : $order->getShippingCarrier()->getCarrierCode(),
            'title' => isset($shipmentCarrierTitle) ? $shipmentCarrierTitle : $order->getShippingCarrier()->getConfigData('title'),
            'number' => $trackingNumber,
        );

        $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
        $shipment->addTrack($track);

        // Register Shipment
        $shipment->register();
        // Save the Shipment
        $this->_saveShipment($shipment, $order, "");
	}

	protected function _saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments = ''){

	    $shipment->getOrder()->setIsInProcess(true);
	    $transactionSave = Mage::getModel('core/resource_transaction')
	                           ->addObject($shipment)
	                           ->addObject($order)
	                           ->save();
	 
	    $emailSentStatus = $shipment->getData('email_sent');
		$ship_data = $shipment->getOrder()->getData();
	    $customerEmail = $ship_data['customer_email'];
		
	    if (!is_null($customerEmail) && !$emailSentStatus) {
	        $shipment->sendEmail(true, $customerEmailComments);
	        $shipment->setEmailSent(true);
	    }
	 
	    return $this;
	}
}