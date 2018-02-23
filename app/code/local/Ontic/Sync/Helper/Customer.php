<?php

class Ontic_Sync_Helper_Customer extends Mage_Core_Helper_Abstract
{
    public function getTrackedAttributes()
    {
        return array_keys($this->getTrackedAttributesWithTypes());
    }

    public function getTrackedAttributesWithTypes()
    {
        return [
            'entity_id' => 'int',
            'website_id' => 'int',
            'email' => 'string',
            'firstname' => 'string',
            'lastname' => 'string',
            'taxvat' => 'string',
            'gender' => 'int',
            'dob' => 'string'
        ];
    }
}