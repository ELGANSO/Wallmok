<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
$installer = $this;
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$salesSetup = Mage::getModel('sales/resource_setup', 'core_setup'); /** @var $salesSetup Mage_Sales_Model_Resource_Setup */
$salesSetup->addAttribute('order', 'i4servired_session_id', array('type' => 'varchar', 'default' => ''));
$salesSetup->addAttribute('order', 'i4servired_params', array('type' => 'text', 'default' => ''));
$installer->endSetup();
