<?php

namespace Ontic\SyncApi\Controllers;

use Mage;
use Ontic\SyncApi\BaseController;
use Symfony\Component\HttpFoundation\Response;

class MarkOrderAsSynchronizedController extends BaseController
{
    /**
     * @return Response
     */
    function defaultAction()
    {
        if(!($orderId = $this->getParameter('orderId')))
        {
            return new Response('400 Bad Request', 400);
        }

        /** @var \Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if($order->isObjectNew())
        {
            return new Response('404 Not Found', 404);
        }

        $order->setData('synchronized', 1);
        $order->save();
        return new Response('', 200);
    }
}