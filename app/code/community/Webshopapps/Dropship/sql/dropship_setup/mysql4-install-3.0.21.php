<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('dropship')} (
  `dropship_id` int(11) unsigned NOT NULL auto_increment COMMENT 'webshopapps dropship',
  `title` varchar(255) NOT NULL default '' COMMENT 'webshopapps dropship',
  `description` varchar(255) NOT NULL default '' COMMENT 'webshopapps dropship',
  `warehouse_code` varchar(255) NULL COMMENT 'webshopapps dropship',
  `status` smallint(6) NOT NULL default '0' COMMENT 'webshopapps dropship',
  `country` varchar(255) NULL COMMENT 'webshopapps dropship',
  `region` varchar(255) NULL COMMENT 'webshopapps dropship',
  `zipcode` varchar(30) NULL COMMENT 'webshopapps dropship',
  `street` varchar(60) NULL COMMENT 'webshopapps dropship',
  `city` varchar(30) NULL COMMENT 'webshopapps dropship',
  `email` varchar (255) NULL COMMENT 'webshopapps dropship',
  `contact` varchar (255) NULL COMMENT 'webshopapps dropship',
  `manualmail` tinyint(1) NOT NULL default '1' COMMENT 'webshopapps dropship',
  `manualship` tinyint(1) NOT NULL default '1' COMMENT 'webshopapps dropship',
  `created_time` datetime NULL COMMENT 'webshopapps dropship',
  `update_time` datetime NULL COMMENT 'webshopapps dropship',
  `latitude` decimal(15,10) NOT NULL COMMENT 'webshopapps dropship',
  `longitude` decimal(15,10) NOT NULL COMMENT 'webshopapps dropship',
  `ismetapak` tinyint(1) NOT NULL default '0' COMMENT 'webshopapps dropship',
   `geo_address` text NULL COMMENT 'webshopapps dropship',
   `fedex_account_id` varchar(255) NULL COMMENT 'webshopapps dropship',
   `fedexsoap_key` varchar(255) NULL COMMENT 'webshopapps dropship',
   `fedexsoap_password` varchar(255) NULL COMMENT 'webshopapps dropship',
   `fedexsoap_meter_number` varchar(255) NULL COMMENT 'webshopapps dropship',
   `ups_password` varchar(255) NULL COMMENT 'webshopapps dropship',
   `ups_access_license_number` varchar(255) NULL COMMENT 'webshopapps dropship',
   `ups_user_id` varchar(255) NULL COMMENT 'webshopapps dropship',
   `ups_shipper_number` varchar(255) NULL COMMENT 'webshopapps dropship',
   `ups_shipping_origin` varchar(255) NULL COMMENT 'webshopapps dropship',
   `max_package_weight` varchar(255) NULL COMMENT 'webshopapps dropship',
   `ups_allowed_methods` varchar(255) NULL COMMENT 'webshopapps dropship',
   `ups_unit_of_measure` varchar(10) NULL COMMENT 'webshopapps dropship',
   `usps_user_id` varchar(255) NULL COMMENT 'webshopapps dropship',
  PRIMARY KEY (`dropship_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='webshopapps dropship';

CREATE TABLE IF NOT EXISTS {$this->getTable('dropship_shipping')} (
    `dropship_id` int(11)  unsigned NOT NULL default '0' COMMENT 'webshopapps dropship',
    `shipping_id` varchar(255)  NULL COMMENT 'webshopapps dropship',
    PRIMARY KEY  (`dropship_id`,`shipping_id`),
    CONSTRAINT `FK_DROPSHIP_SHIPPING_DROPSHIP` FOREIGN KEY (`dropship_id`) REFERENCES {$this->getTable('dropship')} (`dropship_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='webshopapps dropship';

CREATE TABLE IF NOT EXISTS {$this->getTable('webshopapps_shipmethods')} (
  `shipmethods_id` int(11) unsigned NOT NULL auto_increment COMMENT 'webshopapps dropship',
  `title` varchar(255) NOT NULL default '' COMMENT 'webshopapps dropship',
  `description` varchar(255) NOT NULL default '' COMMENT 'webshopapps dropship',
  `created_time` datetime NULL COMMENT 'webshopapps dropship',
  `update_time` datetime NULL COMMENT 'webshopapps dropship',
  PRIMARY KEY (`shipmethods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='webshopapps dropship';

CREATE TABLE IF NOT EXISTS {$this->getTable('webshopapps_shipmethods_carriers')} (
    `shipmethods_id` int(11)  unsigned NOT NULL default '0' COMMENT 'webshopapps dropship',
    `carrier_code` varchar(255)  NULL COMMENT 'webshopapps dropship',
    `carrier_title` varchar(255)  NULL COMMENT 'webshopapps dropship',
    `warehouse` int(5) NULL COMMENT 'webshopapps dropship',
     PRIMARY KEY  (`shipmethods_id`,`carrier_code`,`warehouse`),
   CONSTRAINT `FK_WEBSHOPAPPS_SHIPMETHODS_CARRIERS` FOREIGN KEY (`shipmethods_id`) REFERENCES {$this->getTable('webshopapps_shipmethods')} (`shipmethods_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='webshopapps dropship';

CREATE TABLE IF NOT EXISTS {$this->getTable('webshopapps_sm_upscarriers')} (
    `upscarriers_id` int(11)  unsigned NOT NULL default '0' COMMENT 'webshopapps dropship',
    `origin` varchar(255)  NULL COMMENT 'webshopapps dropship',
    `carrier_title` varchar(255)  NULL COMMENT 'webshopapps dropship',
    `warehouse` int(5) NULL COMMENT 'webshopapps dropship',
     PRIMARY KEY  (`upscarriers_id`,`origin`,`warehouse`),
   CONSTRAINT `FK_WEBSHOPAPPS_SM_UPSCARRIERS` FOREIGN KEY (`upscarriers_id`) REFERENCES {$this->getTable('webshopapps_shipmethods')} (`shipmethods_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='webshopapps dropship';



select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id  and attribute_set_name='Default';

insert ignore into {$this->getTable('eav_attribute_group')}
    set attribute_set_id 	= @attribute_set_id,
    	attribute_group_name	= 'Shipping',
    	sort_order		= 99;

select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='Shipping' and attribute_set_id = @attribute_set_id;

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'warehouse',
    	backend_type	= 'varchar',
    	frontend_input	= 'select',
    	source_model    = 'dropship/dropship',
    	frontend_label	= 'Warehouse';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='warehouse' and entity_type_id = @entity_type_id;

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 0,
    	is_filterable_in_search	= 0;


insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

");

if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {

	$wareAttr = array(
		'type'    	=> Varien_Db_Ddl_Table::TYPE_INTEGER,
		'comment' 	=> 'Warehouse',
		'length'  	=> '5',
		'nullable' 	=> 'false',
		'default' 	=> '0');

	$wareDetails =  array(
		'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
		'comment' 	=> 'Warehouse Details',
		'nullable' 	=> 'true',
			);

	$wareHtml =  array(
		'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
		'comment' 	=> 'Warehouse Html',
		'nullable' 	=> 'true',
			);

	$dropshipStatus = array(
		'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'	=> 50,
		'comment' 	=> 'Dropship Status',
		'nullable' 	=> 'true',);


	$installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'warehouse', $wareAttr );
	$installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'warehouse_shipping_details',$wareDetails);
	$installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'),'warehouse',$wareAttr);
	$installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'),'warehouse_shipping_details',$wareDetails);
	$installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'warehouse_shipping_html',$wareHtml);
	$installer->getConnection()->addColumn($installer->getTable('sales/quote_item'),'warehouse',$wareAttr);
	$installer->getConnection()->addColumn($installer->getTable('sales/order_item'),'warehouse',$wareAttr);

} else {
	$installer->run("
		ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD warehouse int(5) COMMENT 'webshopapps dropship';
		ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD warehouse_shipping_details text COMMENT 'webshopapps dropship';
		ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD warehouse int(5) COMMENT 'webshopapps dropship';
		ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD warehouse_shipping_details text COMMENT 'webshopapps dropship';
		ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD warehouse_shipping_html text COMMENT 'webshopapps dropship';
		ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_item')}  ADD warehouse int(5) COMMENT 'webshopapps dropship';
		ALTER IGNORE TABLE {$this->getTable('sales_flat_order_item')}  ADD warehouse int(5) COMMENT 'webshopapps dropship';
	");

}


if  (Mage::helper('wsalogger')->getNewVersion() >= 8 ) {
	if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
		$installer->getConnection()->addColumn($installer->getTable('sales/order'),'warehouse',$wareAttr);
		$installer->getConnection()->addColumn($installer->getTable('sales/order'),'warehouse_shipping_html',$wareHtml);
		$installer->getConnection()->addColumn($installer->getTable('sales/order'),'warehouse_shipping_details',$wareDetails);
		$installer->getConnection()->addColumn($installer->getTable('sales/shipment_grid'),'warehouse',$wareAttr);
		$installer->getConnection()->addColumn($installer->getTable('sales/shipment_grid'),'dropship_status',$dropshipStatus);
		$installer->getConnection()->addColumn($installer->getTable('sales/shipment'),'warehouse',$wareAttr);
		$installer->getConnection()->addColumn($installer->getTable('sales/shipment'),'dropship_status',$dropshipStatus);
		$installer->getConnection()->addColumn($installer->getTable('sales/shipment'),'shipping_description',array(
			'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
			'length'	=> 255,
			'comment' 	=> 'Shipping Description',
			'nullable' 	=> 'true',));
	} else {
		$installer->run("
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD warehouse int(5) COMMENT 'webshopapps dropship';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD warehouse_shipping_details text COMMENT 'webshopapps dropship';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD warehouse_shipping_html text COMMENT 'webshopapps dropship';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_shipment_grid')}  ADD warehouse int(5) COMMENT 'webshopapps dropship';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_shipment_grid')}  ADD dropship_status varchar(50) COMMENT 'webshopapps dropship';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_shipment')}  ADD dropship_status varchar(50) COMMENT 'webshopapps dropship';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_shipment')}  ADD warehouse int(5) COMMENT 'webshopapps dropship';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_shipment')}  ADD shipping_description varchar(255) COMMENT 'webshopapps dropship';
		");
	}

}

$installer->run("

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'warehouse',
    	backend_type	= 'int',
    	frontend_input	= 'text';

select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id;
select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='warehouse';
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'warehouse_shipping_details',
		backend_type	= 'text',
    	frontend_input	= 'text';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='warehouse_shipping_details';
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

delete from {$this->getTable('core_config_data')} where path like 'carriers/mergedrates%';

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='shipment';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'dropship_status',
    	backend_type	= 'varchar',
    	frontend_input	= 'text';


insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'warehouse',
    	backend_type	= 'int',
    	frontend_input	= 'text';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'shipping_description',
    	backend_type	= 'varchar',
    	frontend_input	= 'text';

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';

insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'warehouse_shipping_html',
		backend_type	= 'text',
    	frontend_input	= 'text';
");


if  (Mage::helper('wsalogger')->isEnterpriseEdition()&& Mage::helper('wsalogger')->getNewVersion() > 10 ) {
	$installer->getConnection()->addColumn($installer->getTable('enterprise_sales_shipment_grid_archive'),'warehouse',$wareAttr);
	$installer->getConnection()->addColumn($installer->getTable('enterprise_sales_shipment_grid_archive'),'dropship_status',$dropshipStatus);
}

$installer->endSetup();