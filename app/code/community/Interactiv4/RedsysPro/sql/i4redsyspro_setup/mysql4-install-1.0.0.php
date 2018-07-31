<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create tables with its fields
 * Firstly, notifications table
 */
/* @var $table Varien_Db_Ddl_Table */
$table = $this->getConnection()->newTable($this->getTable('i4redsyspro/redsyspro_notification'));

$table
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
        array('primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true),
        'Index')  // id
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
        array('unsigned' => true, 'nullable' => true),
        'Order ID') //shipment_id
    ->addColumn('response', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array('nullable' => true, 'default' => null),
        'Response')
    ->addColumn('authorization_code', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array('nullable' => true, 'default' => null),
        'Authorization Code')
    ->addColumn('error_code', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array('nullable' => true, 'default' => null),
        'Error Code');

$this->getConnection()->createTable($table);

$this->getConnection()->addIndex(
    $this->getTable('i4redsyspro/redsyspro_notification'),
    'entity_id_unique',
    array('entity_id'),
    'unique');

/**
 * Create tables with its fields
 * Secondly, refunds table
 */
/* @var $table Varien_Db_Ddl_Table */
$table = $this->getConnection()->newTable($this->getTable('i4redsyspro/redsyspro_refund'));

$table
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
        array('primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true),
        'Index')  // id
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
        array('unsigned' => true, 'nullable' => true),
        'Order ID') //shipment_id
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array('nullable' => true, 'default' => null),
        'Status')
    ->addColumn('amount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, null,
        array('unsigned' => true, 'nullable' => true, 'default' => null, 'scale' => 4, 'precision' => 10),
        'Amount Refunded')
    ->addColumn('refunded_on', Varien_Db_Ddl_Table::TYPE_DATETIME, null,
        array('nullable' => true, 'default' => null),
        'Refund Date')
    ->addColumn('tx_auth_no', Varien_Db_Ddl_Table::TYPE_INTEGER, 11,
        array('unsigned' => true, 'nullable' => true, 'default' => null),
        'Error Code');

$this->getConnection()->createTable($table);

$this->getConnection()->addIndex(
    $this->getTable('i4redsyspro/redsyspro_refund'),
    'entity_id_unique',
    array('entity_id'),
    'unique');

/**
 * Secondly, quote & order attributes
 */

$salesSetup = Mage::getModel('sales/resource_setup', 'core_setup'); /** @var $salesSetup Mage_Sales_Model_Resource_Setup */
$salesSetup->addAttribute('order', 'i4redsyspro_session_id', array('type' => 'varchar', 'default' => ''));
$salesSetup->addAttribute('order', 'i4redsyspro_params', array('type' => 'text', 'default' => ''));
$salesSetup->addAttribute('order', 'i4redsyspro_order_reference', array('type' => 'varchar'));

$salesSetup->addAttribute('order', 'i4redsyspro_email_notification', array('type' => 'int', 'default' => 0));
$salesSetup->addAttribute('order', 'i4redsyspro_email_read_attempts', array('type' => 'int', 'default' => 0));
$salesSetup->addAttribute('order', 'i4redsyspro_email_notification_required', array('type' => 'int', 'default' => 0));

/**
 * Thirdly, order statuses
 */

$statusTable        = $installer->getTable('sales/order_status');
$statusStateTable   = $installer->getTable('sales/order_status_state');

$filePath = Mage::getModuleDir('etc', 'Interactiv4_RedsysPro').DS.'config.xml';
$fileconfig = new Mage_Core_Model_Config_Base();
$fileconfig->loadFile($filePath);
$statuses = $fileconfig->getNode('global/sales/order/statuses')->asArray();

$data = array();
foreach ($statuses as $code => $info) {
    $data[] = array(
        'status'    => $code,
        'label'     => $info['label']
    );
}

$installer->getConnection()->insertArray(
    $statusTable,
    array('status', 'label'),
    $data
);

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