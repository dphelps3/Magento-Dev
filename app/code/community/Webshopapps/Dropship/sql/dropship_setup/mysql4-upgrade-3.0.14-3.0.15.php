<?php

$installer = $this;

$installer->startSetup();

if  (Mage::helper('wsalogger')->isEnterpriseEdition()&& Mage::helper('wsalogger')->getNewVersion() > 10 ) {
	$installer->run("
		ALTER TABLE {$this->getTable('enterprise_sales_shipment_grid_archive')} ADD `warehouse` INT( 5 ) NULL DEFAULT NULL COMMENT 'webshopapps_dropship';
		ALTER TABLE {$this->getTable('enterprise_sales_shipment_grid_archive')} ADD `dropship_status` varchar(50) COMMENT 'webshopapps dropship';
	");
		
}


$installer->endSetup();