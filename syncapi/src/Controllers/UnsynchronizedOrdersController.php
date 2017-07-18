<?php

namespace Ontic\SyncApi\Controllers;

use Mage;
use Ontic\SyncApi\BaseController;
use Ontic\SyncApi\Serializers\OrderSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UnsynchronizedOrdersController extends BaseController
{
    /**
     * @return Response
     */
    function defaultAction()
    {
        $serializer = new OrderSerializer();

        $data = [];
        foreach($this->getUnsynchronizedOrders() as $order)
        {
            $data[] = $serializer->serialize($order);
        }

        return new JsonResponse($data);
    }

    /**
     * @return \Generator|\Mage_Sales_Model_Order[]
     */
    protected function getUnsynchronizedOrders()
    {
        $orders = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('synchronized', [ 'neq' => true ]);

        foreach($orders as $order)
        {
            yield $order;
        }
    }
}