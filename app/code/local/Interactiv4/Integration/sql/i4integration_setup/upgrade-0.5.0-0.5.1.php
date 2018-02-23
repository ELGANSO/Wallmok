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

CREATE TABLE {$this->getTable('i4integration/flag')} (
  integration_flag_id int(11) unsigned NOT NULL auto_increment,
  code VARCHAR(50) NOT NULL,
  flag VARCHAR(50),
  PRIMARY KEY (integration_flag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 