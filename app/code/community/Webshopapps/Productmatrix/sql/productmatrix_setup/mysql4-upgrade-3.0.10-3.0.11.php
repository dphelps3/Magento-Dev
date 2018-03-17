<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('website_id')),
    array('website_id')
);


$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('dest_country_id')),
    array('dest_country_id')
);


$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('dest_region_id')),
    array('dest_region_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('website_id','dest_country_id','dest_region_id','package_id')),
    array('website_id','dest_country_id','dest_region_id','package_id')
);



$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('dest_city')),
    array('dest_city')
);


$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('package_id')),
    array('package_id')
);


$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('weight_from_value')),
    array('weight_from_value')
);


$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('weight_to_value')),
    array('weight_to_value')
);


$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('price_from_value')),
    array('price_from_value')
);


$installer->getConnection()->addIndex(
    $installer->getTable('shipping_productmatrix'),
    $installer->getIdxName('shipping_productmatrix', array('price_to_value')),
    array('price_to_value')
);


$installer->endSetup();


