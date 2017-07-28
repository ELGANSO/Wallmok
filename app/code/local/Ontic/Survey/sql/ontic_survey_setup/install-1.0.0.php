<?php

$installer = $this;

$installer->startSetup();

// Preguntas
$table = $installer->getConnection()
    ->newTable($installer->getTable('survey_question'))
    ->addColumn('question_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Id')
    ->addColumn('text', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, [
        'nullable'  => false,
    ], 'Texto')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'unsigned' => true,
        'nullable'  => false,
    ], 'Tienda')
    ->addColumn('enabled', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, [
        'nullable'  => false,
    ], 'Activada')
    ->addColumn('order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable'  => false,
    ], 'Orden')
    ->addForeignKey(
         $installer->getFkName('survey_question', 'store_id', 'core_store','store_id'),
         'store_id',
         $installer->getTable('core_store'), 
         'store_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, 
         Varien_Db_Ddl_Table::ACTION_CASCADE
    )  
    ;

$installer->getConnection()->createTable($table);

// Respuestas
$table = $installer->getConnection()
    ->newTable($installer->getTable('survey_answer'))
    ->addColumn('answer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Id')
    ->addColumn('question_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable'  => false,
    ], 'Pregunta')
    ->addColumn('text', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, [
        'nullable'  => false,
    ], 'Texto')
    ->addColumn('enabled', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, [
        'nullable'  => false,
    ], 'Activada')
    ->addColumn('order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable'  => false,
    ], 'Orden')
    ->addForeignKey(
         $installer->getFkName('survey_answer', 'question_id', 'survey_question','question_id'),
         'question_id',
         $installer->getTable('survey_question'), 
         'question_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, 
         Varien_Db_Ddl_Table::ACTION_CASCADE
    )  
    ;

$installer->getConnection()->createTable($table);

// Contestaciones
$table = $installer->getConnection()
    ->newTable($installer->getTable('survey_response'))
    ->addColumn('response_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true
    ], 'Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable'  => false
    ], 'Cliente')
    ->addColumn('answer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'nullable'  => false,
    ], 'Respuesta')
    ->addForeignKey(
         $installer->getFkName('survey_response', 'customer_id', 'customer_entity','entity_id'),
         'customer_id',
         $installer->getTable('customer_entity'), 
         'entity_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, 
         Varien_Db_Ddl_Table::ACTION_CASCADE
    )  
    ;

$installer->getConnection()->createTable($table);

$installer->endSetup();
