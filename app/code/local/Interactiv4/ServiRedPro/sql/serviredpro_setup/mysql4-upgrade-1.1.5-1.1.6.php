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
$salesSetup->addAttribute('order', 'i4servired_email_notification', array('type' => 'int', 'default' => 0));
$salesSetup->addAttribute('order', 'i4servired_email_read_attempts', array('type' => 'int', 'default' => 0));
$salesSetup->addAttribute('order', 'i4servired_email_notification_required', array('type' => 'int', 'default' => 0));
$status = Mage::getModel('sales/order_status');
$status->setStatus('servired_no_email_notification');
$status->setLabel('Servired - Email Notification Not Received');
$status->assignState('processing');
$status->save();
$installer->endSetup();
