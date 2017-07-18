<?php

namespace Ontic\SyncApi\Serializers;

use Mage;

class AddressSerializer
{
    public function serialize($customer, $addressType)
    {
        if(strlen($customer["${addressType}_firstname"]) === 0)
        {
            // La dirección no existe, devolvemos null
            return null;
        }

        // Nos quedamos con los atributos que nos interesan de la dirección
        $data = [];
        $attributes = Mage::helper('onticsync/address')->getTrackedAttributesWithTypes();
        foreach($attributes as $attribute => $type)
        {
            $data[$attribute] = $customer["${addressType}_$attribute"];

            if($type === 'int' && $data[$attribute] !== null)
            {
                $data[$attribute] = (int) $data[$attribute];
            }
        }

        return $data;
    }

    public function serializeAddress($address)
    {
        // Nos quedamos con los atributos que nos interesan de la dirección
        $data = [];
        $attributes = Mage::helper('onticsync/address')->getTrackedAttributesWithTypes();
        foreach($attributes as $attribute => $type)
        {
            $data[$attribute] = $address[$attribute];

            if($type === 'int' && $data[$attribute] !== null)
            {
                $data[$attribute] = (int) $data[$attribute];
            }
        }

        return $data;
    }
}