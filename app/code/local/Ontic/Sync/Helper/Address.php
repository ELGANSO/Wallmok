<?php

class Ontic_Sync_Helper_Address extends Mage_Core_Helper_Abstract
{
    public function getTrackedAttributes()
    {
        return array_keys($this->getTrackedAttributesWithTypes());
    }

    public function getTrackedAttributesWithTypes()
    {
        return [
            'firstname' => 'string',
            'lastname' => 'string',
            'street' => 'string',
            'city' => 'string',
            'country_id' => 'string',
            'region' => 'string',
            'region_id' => 'int',
            'postcode' => 'string',
            'telephone' => 'string'
        ];
    }
}