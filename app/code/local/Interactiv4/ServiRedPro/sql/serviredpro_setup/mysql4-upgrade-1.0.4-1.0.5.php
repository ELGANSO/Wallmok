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
$statusTable        = $installer->getTable('sales/order_status');
$statusStateTable   = $installer->getTable('sales/order_status_state');
$statusLabelTable   = $installer->getTable('sales/order_status_label');
$file = Mage::getModuleDir('etc', 'Interactiv4_ServiRedPro').DS.'config.xml';
$fileconfig = new Mage_Core_Model_Config_Base();
$fileconfig->loadFile($file);
$statuses = $fileconfig->getNode('global/sales/order/statuses')->asArray();
$data = array();
foreach ($statuses as $code => $info) {
    $data[] = array(
        'status'    => $code,
        'label'     => $info['label']
    );
}
$installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
$file = Mage::getModuleDir('etc', 'Interactiv4_ServiRedPro').DS.'config.xml';
$fileconfig = new Mage_Core_Model_Config_Base();
$fileconfig->loadFile($file);
$states = $fileconfig->getNode('global/sales/order/states')->asArray();
$data = array();
foreach ($states as $code => $info) {
    if (isset($info['statuses'])) {
        foreach ($info['statuses'] as $status => $statusInfo) {
            $data[] = array(
                'status'    => $status,
                'state'     => $code,
                'is_default'=> is_array($statusInfo) && isset($statusInfo['@']['default']) ? 1 : 0
            );
        }
    }
}
$installer->getConnection()->insertArray(
    $statusStateTable,
    array('status', 'state', 'is_default'),
    $data
);
$installer->endSetup();
