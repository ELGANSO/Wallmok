<?php

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;
$setup = new Mage_Sales_Model_Resource_Setup(null);
$setup->addAttribute('order', 'synchronized', ['type' => 'boolean']);
$installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD INDEX IDX_SALES_FLAT_ORDER_SYNCHRONIZED(synchronized);");
$installer->run("ALTER TABLE {$this->getTable('sales/order')} MODIFY synchronized BOOL DEFAULT FALSE;");
$installer->endSetup();

