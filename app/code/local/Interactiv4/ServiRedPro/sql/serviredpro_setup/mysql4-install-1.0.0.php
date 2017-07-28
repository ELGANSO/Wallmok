<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('serviredpro_transactions')};
        CREATE TABLE {$this->getTable('serviredpro_transactions')} (
          `id` int(10) unsigned NOT NULL auto_increment,
          `entity_id` int(10) unsigned, -- Order Id
          `address_result` TINYINT(2) unsigned NULL,
          `postcode_result` TINYINT(2) unsigned NULL,
          `cv2_result` TINYINT(2) unsigned NULL,
          `security_key` VARCHAR(255) NULL,
          `vendor_tx_code` VARCHAR(255) NULL,
          `released` TINYINT(2) unsigned NULL,
          `authorised` TINYINT(2) unsigned NULL,
          `canceled` TINYINT(2) unsigned NULL,
          `aborted` TINYINT(2) unsigned NULL,
          `repeated` TINYINT(2) unsigned NULL,
          `voided` TINYINT(2) unsigned NULL,
          `action_date` DATETIME,
          `action_by` VARCHAR(255) NULL,
          `sgpdp_amount_released` DECIMAL(12,4) unsigned,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        DROP TABLE IF EXISTS {$this->getTable('serviredpro_refunds')};
        CREATE TABLE {$this->getTable('serviredpro_refunds')} (
          `id` int(10) unsigned NOT NULL auto_increment,
          `entity_id` int(10) unsigned, -- Order Id
          `status` VARCHAR(255) NULL,
          `amount_refunded` DECIMAL(12,4) unsigned,
          `refunded_on` DATETIME,
          `status_detail` VARCHAR(255) NULL,
          `vps_tx_id` VARCHAR(255) NULL,
          `tx_auth_no` INT(11) unsigned NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");
$installer->endSetup();
