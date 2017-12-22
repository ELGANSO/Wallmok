<?php
$installer = $this; /* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('i4mrwes_tablerate'), 'cod_min_surcharge', "decimal(12,4)");
$installer->getConnection()->addColumn($this->getTable('i4mrwes_tablerate'), 'price_vs_dest', "int(10) NOT NULL default '0'");
$installer->endSetup();
?>
