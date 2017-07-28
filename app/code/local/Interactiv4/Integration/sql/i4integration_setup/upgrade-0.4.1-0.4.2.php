<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales/order')} MODIFY is_exported BOOL DEFAULT false;

    ");

$installer->endSetup(); 