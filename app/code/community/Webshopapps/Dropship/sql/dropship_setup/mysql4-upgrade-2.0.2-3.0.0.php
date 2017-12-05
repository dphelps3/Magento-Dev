<?php

$installer = $this;

$installer->startSetup();

$installer->run("

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';


alter table {$this->getTable('dropship')}
add  `street` varchar(60) NULL,
add  `latitude` decimal(15,10) NOT NULL,
add  `longitude` decimal(15,10) NOT NULL,
add   `geo_address` text NULL
   ;


select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id  and attribute_set_name='Default';
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='Shipping' and attribute_set_id = @attribute_set_id;

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
	attribute_code 	= 'oversized_shipping',
	backend_type	= 'int',
	frontend_input	= 'boolean',
	is_required	= 0,
	is_user_defined	= 1,
	frontend_label	= 'Oversized';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='oversized_shipping';

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 0;

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
	attribute_code 	= 'overnight_delivery',
	backend_type	= 'int',
	frontend_input	= 'boolean',
	is_required	= 0,
	is_user_defined	= 1,
	frontend_label	= 'Overnight Delivery Allowed';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='overnight_delivery';

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 0;

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD warehouse_shipping_details text;

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';


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

    ");

$installer->endSetup();