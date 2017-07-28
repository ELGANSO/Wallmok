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

$setup = new Mage_Sales_Model_Resource_Setup;
$setup->addAttribute('order', 'is_exported', array('type' => 'boolean', 'grid' => true));

$installer->run("

ALTER TABLE {$this->getTable('sales/order')} ADD INDEX IDX_SALES_FLAT_ORDER_IS_EXPORTED(is_exported);

    ");

$installer->endSetup(); 