#!/usr/bin/env php
<?php

require_once __DIR__ . '/../../abstract.php';

class ProcessUpdatesAction extends Mage_Shell_Abstract
{
    public function run()
    {
        $lockFile = Mage::getBaseDir('var') . '/locks/product_updates.lock';
        $fileHandle = fopen($lockFile, 'w');
        if(!flock($fileHandle, LOCK_EX | LOCK_NB))
        {
            // Ya hay una instancia del script en ejecución, salimos
            echo 'En uso' . PHP_EOL;
            return;
        }

        $updater = Mage::getModel('onticsync/productUpdater');
        $updater->processAllUpdates();
    }
}

$shell = new ProcessUpdatesAction();
$shell->run();
