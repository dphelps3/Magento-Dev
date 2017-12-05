<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD warehouse_shipping_details text;


");


$installer->endSetup();