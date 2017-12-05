<?php

$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'featured_width', 'int(3) NOT NULL default "30"');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'submenu_width', 'int(5) NOT NULL default "100"');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'submenu_align', 'int(3) NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'leftsubmenu_align', 'int(3) NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'megamenu_type', 'int(3) NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'category_type', 'int(3) NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'featured_content', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'main_content', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'featured_column', "int(3) NOT NULL");
$installer->getConnection()->addColumn($installer->getTable('megamenu/megamenu'), 'category_show_type', "int(3) NOT NULL");
$installer->endSetup();