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
$salesSetup = new Mage_Sales_Model_Resource_Setup('core_setup'); 
$salesSetup->addAttribute('order', 'i4servired_order_reference', array('type' => 'varchar'));
$installer->endSetup();
