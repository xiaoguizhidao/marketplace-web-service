<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Marketplace Web Services', 100);

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'amazon_fulfillment', array(
    'type' => 'varchar',
    'group' => 'Marketplace Web Services',
    'label' => 'Fulfillment',
    'source' => 'edgecom_marketplacewebservice/catalog_product_attribute_source_fulfillment',
    'input' => 'select',
    'required' => false
));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'amazon_fulfillment_center', array(
    'type' => 'varchar',
    'group' => 'Marketplace Web Services',
    'label' => 'Fulfillment Center',
    'source' => 'edgecom_marketplacewebservice/catalog_product_attribute_source_fulfillment_center',
    'input' => 'select',
    'note' => 'This field is only necessary if products are fulfilled by Amazon',
    'required' => false
));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'amazon_description', array(
    'type' => 'varchar',
    'group' => 'Marketplace Web Services',
    'label' => 'Description',
    'input' => 'textarea',
    'required' => false
));

for ($i = 1; $i <= 5; $i++) {
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'amazon_bulletpoint_' . $i, array(
        'type' => 'varchar',
        'group' => 'Marketplace Web Services',
        'label' => 'Bulletpoint ' . $i,
        'input' => 'text',
        'required' => false
    ));
}

for ($i = 1; $i <= 5; $i++) {
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'amazon_searchterm_' . $i, array(
        'type' => 'varchar',
        'group' => 'Marketplace Web Services',
        'label' => 'Search Term ' . $i,
        'input' => 'text',
        'required' => false
    ));
}

for ($i = 1; $i <= 10; $i++) {
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'amazon_promotionkeyword_' . $i, array(
        'type' => 'varchar',
        'group' => 'Marketplace Web Services',
        'label' => 'Promotion Keyword ' . $i,
        'input' => 'text',
        'required' => false
    ));
}

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'amazon_sync_date', array(
    'type' => 'datetime',
    'group' => 'Marketplace Web Services',
    'label' => 'Amazon Sync Date',
    'input' => 'date',
    'backend' => 'eav/entity_attribute_backend_datetime',
    'required' => false
));

$installer->endSetup();
