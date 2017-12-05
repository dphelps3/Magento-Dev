<?php

$installer = $this;

$installer->startSetup();

$installer->run("
        ALTER IGNORE TABLE {$this->getTable('dropship')} ADD warehouse_code varchar(225) NULL COMMENT 'webshopapps dropship';
");
		
$installer->endSetup();