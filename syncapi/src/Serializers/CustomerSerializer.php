<?php

namespace Ontic\SyncApi\Serializers;

use Mage;

class CustomerSerializer
{
    public function serialize($customer)
    {
        $addressSerializer = new AddressSerializer();

        $data = [];
        $attributes = Mage::helper('onticsync/customer')->getTrackedAttributesWithTypes();

        foreach($attributes as $attribute => $type)
        {
            $data[$attribute] = $customer[$attribute];

            if($type === 'int' && $data[$attribute] !== null)
            {
                $data[$attribute] = (int) $data[$attribute];
            }
        }

        $data['billing_address'] = $addressSerializer->serialize($customer, 'billing');
        $data['shipping_address'] = $addressSerializer->serialize($customer, 'shipping');

        return $data;
    }
}