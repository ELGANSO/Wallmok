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

$installer->run("UPDATE {$this->getTable('sales/order')} SET is_exported = 1 WHERE created_at <= NOW();");

$installer->endSetup(); 
