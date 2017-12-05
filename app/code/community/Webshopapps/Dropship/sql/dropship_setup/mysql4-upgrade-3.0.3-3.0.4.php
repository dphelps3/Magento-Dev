<?php

$installer = $this;

$installer->startSetup();

$installer->run("

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

");

$conn = $installer->getConnection();

if  (Mage::helper('wsacommon')->getVersion() >= 1.8 || Mage::helper('wsacommon')->getVersion()==1.10) {


	if (!$conn->tableColumnExists($this->getTable('sales_flat_shipment_grid'), 'dropship_status')) {
		$conn->addColumn($this->getTable('sales_flat_shipment_grid'), 'dropship_status', 'varchar(50)');
	}
	if (!$conn->tableColumnExists($this->getTable('sales_flat_shipment_grid'), 'warehouse')) {
		$conn->addColumn($this->getTable('sales_flat_shipment_grid'), 'warehouse', 'int(5)');
	}
	if (!$conn->tableColumnExists($this->getTable('sales_flat_shipment'), 'dropship_status')) {
		$conn->addColumn($this->getTable('sales_flat_shipment'), 'dropship_status', 'varchar(50)');
	}
	if (!$conn->tableColumnExists($this->getTable('sales_flat_shipment'), 'warehouse')) {
		$conn->addColumn($this->getTable('sales_flat_shipment'), 'warehouse', 'int(5)');
	}
	if (!$conn->tableColumnExists($this->getTable('sales_flat_shipment'), 'shipping_description')) {
		$conn->addColumn($this->getTable('sales_flat_shipment'), 'shipping_description', 'varchar(255)');
	}
}

if (!$conn->tableColumnExists($this->getTable('dropship'), 'manualmail')) {
	$conn->addColumn($this->getTable('dropship'), 'manualmail', 'tinyint(1) NOT NULL default \'1\'');
}

$installer->endSetup();