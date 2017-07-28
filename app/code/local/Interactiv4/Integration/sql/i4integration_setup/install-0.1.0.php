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

CREATE TABLE {$this->getTable('i4integration/logs')} (
  integration_logs_id int(11) unsigned NOT NULL auto_increment,
  log_date datetime NULL,
  process_name VARCHAR(50),
  message TEXT,
  PRIMARY KEY (integration_logs_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('i4integration/process')} (
  integration_process_id int(11) unsigned NOT NULL auto_increment,
  label VARCHAR(50),
  code VARCHAR(50),
  PRIMARY KEY (integration_process_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 