#!/usr/bin/env php
<?php

require_once __DIR__ . '/../../abstract.php';

class Action extends Mage_Shell_Abstract
{
    public function run()
    {
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('synchronized', 'left')
            ->addAttributeToFilter([
                [ 'attribute' => 'synchronized', 'neq' => 1 ],
                [ 'attribute' => 'synchronized', 'null' => 1 ],
            ]);

        /** @var Mage_Customer_Model_Customer $customer */
        $count = 0;
        foreach($customers as $customer)
        {
            $customer->setData('synchronized', true);
            $customer->getResource()->saveAttribute($customer, 'synchronized');
            $count++;
        }

        echo $count . ' clientes actualizados.' . PHP_EOL;
    }
}

$shell = new Action();
$shell->run();
