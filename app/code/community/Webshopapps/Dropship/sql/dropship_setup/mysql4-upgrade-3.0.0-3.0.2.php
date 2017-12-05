<?php

$installer = $this;

$installer->startSetup();



if  (Mage::helper('wsacommon')->getVersion() >= 1.8 || Mage::helper('wsacommon')->getVersion()==1.10) {
	$installer->run("
		ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD warehouse int(5);
		ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD warehouse_shipping_details text;
	");

}

$installer->endSetup();