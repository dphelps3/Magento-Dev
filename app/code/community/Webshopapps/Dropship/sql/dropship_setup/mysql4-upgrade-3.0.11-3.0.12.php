<?php

$installer = $this;

$installer->startSetup();

$installer->run("



select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';


insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'warehouse_shipping_html',
		backend_type	= 'text',
    	frontend_input	= 'text';

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD warehouse_shipping_html text;
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD warehouse_shipping_html text;
    	

");


$installer->endSetup();