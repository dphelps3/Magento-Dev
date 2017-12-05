<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales_flat_quote_address')} CHANGE `warehouse` `warehouse` int(5) COMMENT 'webshopapps dropship';
ALTER TABLE {$this->getTable('sales_flat_quote_shipping_rate')} CHANGE `warehouse` `warehouse` int(5) COMMENT 'webshopapps dropship';
ALTER TABLE {$this->getTable('sales_flat_quote_address')} CHANGE `warehouse_shipping_details` `warehouse_shipping_details` text COMMENT 'webshopapps dropship';
ALTER TABLE {$this->getTable('sales_flat_quote_shipping_rate')} CHANGE `warehouse_shipping_details` `warehouse_shipping_details` text COMMENT 'webshopapps dropship';


ALTER TABLE {$this->getTable('sales_flat_quote_address')} CHANGE `warehouse_shipping_html` `warehouse_shipping_html` text COMMENT 'webshopapps dropship';
ALTER TABLE {$this->getTable('sales_flat_order')} CHANGE `warehouse_shipping_html` `warehouse_shipping_html` text COMMENT 'webshopapps dropship';
ALTER TABLE {$this->getTable('sales_flat_quote_item')} CHANGE `warehouse` `warehouse` int(5) COMMENT 'webshopapps dropship';

ALTER TABLE {$this->getTable('sales_flat_order_item')} CHANGE `warehouse` `warehouse` int(5) COMMENT 'webshopapps dropship';

");

if  (Mage::helper('wsalogger')->getNewVersion() >= 8 ) {

	$installer->run("

		ALTER TABLE {$this->getTable('sales_flat_order')} CHANGE `warehouse` `warehouse` int(5) COMMENT 'webshopapps dropship';
		ALTER TABLE {$this->getTable('sales_flat_order')} CHANGE `warehouse_shipping_details` `warehouse_shipping_details` text COMMENT 'webshopapps dropship';
		ALTER TABLE {$this->getTable('sales_flat_shipment_grid')} CHANGE `warehouse` `warehouse` INT( 5 ) NULL DEFAULT NULL COMMENT 'webshopapps_dropship';
		ALTER TABLE {$this->getTable('sales_flat_shipment_grid')} CHANGE `dropship_status` `dropship_status` varchar(50) COMMENT 'webshopapps dropship';
		ALTER TABLE {$this->getTable('sales_flat_shipment')} CHANGE `warehouse` `warehouse` INT( 5 ) NULL DEFAULT NULL COMMENT 'webshopapps_dropship';
		ALTER TABLE {$this->getTable('sales_flat_shipment')} CHANGE `dropship_status` `dropship_status` varchar(50) COMMENT 'webshopapps dropship';
		ALTER TABLE {$this->getTable('sales_flat_shipment')} CHANGE `shipping_description` `shipping_description` varchar(255) COMMENT 'webshopapps dropship';
	");
}





$installer->endSetup();