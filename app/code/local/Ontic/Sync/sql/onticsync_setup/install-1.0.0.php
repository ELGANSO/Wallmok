<?php

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;
$setup = $installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('customer', 'synchronized', [
    'type'     => 'int',
    'backend'  => '',
    'label'    => 'Synchronized',
    'input'    => 'checkbox',
    'source'   => '',
    'visible'  => true,
    'required' => false,
    'default'  => '',
    'frontend' => '',
    'unique'   => false,
    'note'     => 'Synchronized'
]);

$attribute   = Mage::getSingleton('eav/config')->getAttribute('customer', 'synchronized');

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'synchronized',
    '999'
);

$attribute->setData('used_in_forms', [])
    ->setData('is_used_for_customer_segment', true)
    ->setData('is_system', 0)
    ->setData('is_user_defined', 1)
    ->setData('is_visible', 0)
    ->setData('sort_order', 100)
;
$attribute->save();

$installer->endSetup();