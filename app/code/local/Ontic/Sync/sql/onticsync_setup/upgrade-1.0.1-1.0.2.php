<?php

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;
$setup = $installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('onticsync/product_update_request'))
    ->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable' => false,
        'default' => 0
    ])
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, [
        'nullable' => false,
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT
    ])
    ->addIndex('IDX_ONTICSYNC_PRODUCT_UPDATE_REQUEST_STATUS', 'status')
    ->addIndex('IDX_ONTICSYNC_PRODUCT_UPDATE_REQUEST_CREATED_AT', 'created_at')
;

$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('onticsync/product_update'))
    ->addColumn('update_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ], 'Id')
    ->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable' => false
    ])
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, [
        'nullable' => false
    ], 'SKU')
    ->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, null, [
        'nullable' => false
    ])
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'nullable' => false,
        'default' => -1
    ])
    ->addIndex('IDX_ONTICSYNC_PRODUCT_UPDATE_STATUS', 'status')
    ->addForeignKey(
        'FK_ONTICSYNC_PRODUCT_UPDATE_REQUEST_ID',
        'request_id',
        $installer->getTable('onticsync/product_update_request'),
        'request_id')
    ;

$installer->getConnection()->createTable($table);

$installer->endSetup();