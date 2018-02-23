<?php
$installer = $this; /* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$installer->getConnection()->dropKey($this->getTable('i4mrwes_tablerate'), "dest_country");
$installer->getConnection()->addKey(
        $this->getTable('i4mrwes_tablerate'), 
        "dest_country", 
        array('website_id','dest_country_id','dest_region_id','dest_zip','weight','method', 'price_vs_dest'), 
        "unique");
$installer->endSetup();
?>
