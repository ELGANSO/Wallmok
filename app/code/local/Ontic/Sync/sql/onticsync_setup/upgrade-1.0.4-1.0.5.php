<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('sales_flat_order'), 'franja', "TEXT DEFAULT NULL");
$installer->getConnection()->addColumn($this->getTable('sales_flat_order'), 'fecharecogida', "DATE DEFAULT NULL");
$installer->endSetup();