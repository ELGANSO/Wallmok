<?php

class Ontic_Sync_Model_Observer_Address
{
    public function customerAddressSaveAfter($event)
    {
        /** @var Mage_Customer_Model_Address $address */
        $address = $event['customer_address'];

        if(!($changes = $this->getChanges($address)))
        {
            // La dirección no ha cambiado, salimos directamente
            return;
        }

        // Registramos los cambios en el log
        Mage::log(sprintf(
            'La dirección %d ha cambiado, marcándo su cliente %d como no sincronizado.',
            $address->getId(),
            $address->getCustomerId()), null, 'onticsync.log');

        foreach($changes as $change)
        {
            Mage::log($change, null, 'onticsync.log');
        }

        // Y marcamos el cliente como no syncronizado
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        $customer->setId($address->getCustomerId());
        $customer->setData('synchronized', false);
        $customer->getResource()->saveAttribute($customer,'synchronized');
    }

    /**
     * Obtiene la lista de los cambios que ha sufrido la dirección
     * desde la última vez que se guardó
     * @param Mage_Customer_Model_Address $address
     * @return array
     */
    protected function getChanges(Mage_Customer_Model_Address $address)
    {
        $changes = [];

        $attributes = Mage::helper('onticsync/address')->getTrackedAttributes();
        foreach($attributes as $attribute)
        {
            $originalValue = $address->getOrigData($attribute);
            $currentValue = $address->getData($attribute);

            if($originalValue != $currentValue)
            {
                $changes[] = sprintf('%s: %s => %s', $attribute, $originalValue, $currentValue);
            }
        }

        return $changes;
    }

}