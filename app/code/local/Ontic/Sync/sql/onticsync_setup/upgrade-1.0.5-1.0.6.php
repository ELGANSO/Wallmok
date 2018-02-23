<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('sales_flat_quote'), 'franja', "TEXT DEFAULT NULL");
$installer->getConnection()->addColumn($this->getTable('sales_flat_quote'), 'fecharecogida', "DATE DEFAULT NULL");
$installer->endSetup();
