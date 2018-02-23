<?php

class Ontic_Sync_Model_Observer_Customer
{
    public function customerSaveAfter($event)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $event['customer'];

        if(!($changes = $this->getChanges($customer)))
        {
            // El cliente no ha cambiado, salimos directamente
            return;
        }

        // Registramos los cambios en el log
        Mage::log(sprintf('El cliente %d ha cambiado, marcándolo como no sincronizado.', $customer->getId()), null, 'onticsync.log');
        foreach($changes as $change)
        {
            Mage::log($change, null, 'onticsync.log');
        }

        // Y marcamos el cliente como no syncronizado
        $customer->setData('synchronized', false);
        $customer->getResource()->saveAttribute($customer,'synchronized');
    }

    /**
     * Obtiene la lista de los cambios que ha sufrido el cliente
     * desde la última vez que se guardó
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    protected function getChanges(Mage_Customer_Model_Customer $customer)
    {
        $changes = [];

        $attributes = Mage::helper('onticsync/customer')->getTrackedAttributes();
        foreach($attributes as $attribute)
        {
            $originalValue = $customer->getOrigData($attribute);
            $currentValue = $customer->getData($attribute);

            if($originalValue != $currentValue)
            {
                $changes[] = sprintf('%s: %s => %s', $attribute, $originalValue, $currentValue);
            }
        }

        return $changes;
    }

}