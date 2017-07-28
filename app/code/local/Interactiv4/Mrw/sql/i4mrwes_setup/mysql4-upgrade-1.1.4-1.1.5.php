<?php
/**
 * Mrw
 *
 * @category    Interactiv4
 * @package     Interactiv4_Mrw
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
$installer = $this; /* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
// Cambiamos el tipo de la columna surcharge para admitir cadenas... así podemos introducir porcentajes.
$installer->getConnection()->modifyColumn($this->getTable('i4mrwes_tablerate'), 'cashondelivery_surcharge', "varchar(20) DEFAULT NULL");
// Añadimos un estado para los nuevos pedidos contrareembolso.
$status = Mage::getModel('sales/order_status');
$status->setStatus('mrwes_pending_cashondelivery');
$status->setLabel('MRW Pending Cash On Delivery');
$status->assignState('pending_payment');
$status->save();
// Añadimos unos atributos para guardar el IVA sobre los modelos de ventas
$salesSetup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
$salesSetup->addAttribute('quote_address', 'i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('quote_address', 'base_i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('order', 'i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('order', 'base_i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('invoice', 'i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('invoice', 'base_i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('creditmemo', 'i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$salesSetup->addAttribute('creditmemo', 'base_i4mrwes_cashondelivery_surcharge_tax', array('type' => 'decimal'));
$installer->endSetup();
