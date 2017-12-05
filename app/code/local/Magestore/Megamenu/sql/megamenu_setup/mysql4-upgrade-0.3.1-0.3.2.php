<?php

$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'product_label', 'text');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'product_label_color', 'text NOT NULL');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'products_using_label', 'text');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'featured_title', 'text');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'view_all', 'int(3) NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'number_products', 'int(11) NOT NULL default "0"');
$installer->endSetup();