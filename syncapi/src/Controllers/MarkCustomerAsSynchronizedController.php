<?php

namespace Ontic\SyncApi\Controllers;

use Mage;
use Ontic\SyncApi\BaseController;
use Symfony\Component\HttpFoundation\Response;

class MarkCustomerAsSynchronizedController extends BaseController
{
    /**
     * @return Response
     */
    function defaultAction()
    {
        if(!($customerId = $this->getParameter('customerId')))
        {
            return new Response('400 Bad Request', 400);
        }

        /** @var \Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if($customer->isObjectNew())
        {
            return new Response('404 Not Found', 404);
        }

        $customer->setData('synchronized', 1);
        $customer->getResource()->saveAttribute($customer, 'synchronized');
        return new Response('', 200);
    }
}