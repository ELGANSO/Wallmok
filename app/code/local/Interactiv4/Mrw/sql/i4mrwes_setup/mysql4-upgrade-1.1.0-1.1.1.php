<?php
/**
 * Mrw
 *
 * @category    Interactiv4
 * @package     Interactiv4_Mrw
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
$installer = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
$installer->startSetup();
$installer->addAttribute('quote_address', 'i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->addAttribute('quote_address', 'base_i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->addAttribute('order', 'i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->addAttribute('order', 'base_i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->addAttribute('invoice', 'i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->addAttribute('invoice', 'base_i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->addAttribute('creditmemo', 'i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->addAttribute('creditmemo', 'base_i4mrwes_cashondelivery_surcharge', array('type' => 'decimal'));
$installer->endSetup();
