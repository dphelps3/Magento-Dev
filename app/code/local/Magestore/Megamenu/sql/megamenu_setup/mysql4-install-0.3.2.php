<?php

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('megamenu/megamenu')};
CREATE TABLE {$this->getTable('megamenu/megamenu')} (
    `megamenu_id` int(11) unsigned NOT NULL auto_increment,
    `name_menu` varchar(255) NOT NULL default '',
    `stores` TEXT NULL,
    `link` TEXT NULL,
    `sort_order` int(11) NULL,
    `item_icon` TEXT NULL,
    `megamenu_type` smallint(3) NOT NULL default '0',
    `status` smallint(6) NOT NULL default '0',
    `created_time` datetime NULL,
    `update_time` datetime NULL,

    `menu_type` int(11)  NOT NULL default '6',
    `products` TEXT NULL,
    `products_using_label` TEXT NULL,
    `product_label` TEXT NULL,
    `product_label_color` TEXT NOT NULL,
    `categories` TEXT NULL,
    `products_box_title` TEXT NULL,
    `categories_box_title` TEXT NULL,
    `colum` int(11) NULL,
    `header` TEXT NULL,
    `footer` TEXT NULL,
    `featured_type` smallint(3) NOT NULL default '0',
    `featured_products` TEXT NULL,
    `featured_categories` TEXT NULL,
    `featured_title` TEXT NULL,
    `featured_width` int(3) not null DEFAULT '30',
    `submenu_width` int(3) NOT NULL DEFAULT '100',
    `submenu_align` int(3) NOT NULL DEFAULT '0',
    `leftsubmenu_align` int(3) NOT NULL DEFAULT '0',
    `category_type` int(3) NOT NULL DEFAULT '0',
    `view_all` int(3) NOT NULL DEFAULT '0',
    `featured_content` TEXT NULL,
    `main_content` TEXT NULL,
    `featured_column` int(3) NOT NULL DEFAULT '0',
    `category_image` int(3) NOT NULL DEFAULT '0',
    `number_products` int(11)  NOT NULL default '0',

    PRIMARY KEY (`megamenu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    ");
$installer->endSetup();
