
<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('serviredpro_notifications')};
        CREATE TABLE {$this->getTable('serviredpro_notifications')} (
          `id` int(10) unsigned NOT NULL auto_increment,
          `entity_id` int(10) unsigned, -- Order Id
          `response` VARCHAR(255) NULL,
          `authorisation_code` VARCHAR(255) NULL,
		  `error_code`VARCHAR(255) NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");
$installer->endSetup();
