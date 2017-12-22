<?php

namespace Ontic\SyncApi\Controllers;

use Mage;
use Mage_Customer_Model_Customer;
use Ontic\SyncApi\BaseController;
use Ontic\SyncApi\Serializers\CustomerSerializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnsynchronizedCustomersController extends BaseController
{
    /**
     * @return Response
     */
    function defaultAction()
    {

        $response = new StreamedResponse();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        $response->setCallback(function()
        {
            echo '[';

            $serializer = new CustomerSerializer();

            $first = true;
            foreach($this->getUnsynchronizedCustomers() as $customer)
            {
                if($first)
                {
                    $first = false;
                }
                else
                {
                    echo ',';
                }

                echo json_encode($serializer->serialize($customer));
            }

            echo ']';
        });

        return $response;
    }

    /**
     * @return \Iterator|Mage_Customer_Model_Customer[]
     */
    protected function getUnsynchronizedCustomers()
    {
        $customerAttributes = Mage::helper('onticsync/customer')->getTrackedAttributes();
        $addressAttributes = Mage::helper('onticsync/address')->getTrackedAttributes();

        // Obtenemos los datos de clientes
        /** @var \Mage_Customer_Model_Resource_Customer_Collection $customers */
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect($customerAttributes, 'left')
            ->addAttributeToFilter([
                [ 'attribute' => 'synchronized', 'null' => true ],
                [ 'attribute' => 'synchronized', 'neq' => 1 ]
            ]);

        // Añadimos los datos de la dirección de facturación por defecto
        foreach($addressAttributes as $attribute)
        {
            $customers->joinAttribute("billing_$attribute", "customer_address/$attribute", 'default_billing', null, 'left');
        }

        // Y de la dirección de envío
        foreach($addressAttributes as $attribute)
        {
            $customers->joinAttribute("shipping_$attribute", "customer_address/$attribute", 'default_shipping', null, 'left');
        }

        $query = $customers
            ->getSelect()
            ->query();

        while($row = $query->fetch())
        {
            yield $row;
        }
    }
}