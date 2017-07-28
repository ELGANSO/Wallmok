#!/usr/bin/env php
<?php

require_once __DIR__ . '/../../abstract.php';

class MarkAllOrdersAsSynchronizedAction extends Mage_Shell_Abstract
{
    public function run()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $table = $resource->getTableName('sales/order');
        $count = $connection->query("UPDATE $table SET synchronized = 1;")->rowCount();

        echo "$count pedidos actualizados." . PHP_EOL;
    }
}

$shell = new MarkAllOrdersAsSynchronizedAction();
$shell->run();
