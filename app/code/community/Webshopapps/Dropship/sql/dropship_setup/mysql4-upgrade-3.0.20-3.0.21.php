<?php

$installer = $this;

$installer->startSetup();

$installer->run("
        ALTER IGNORE TABLE {$this->getTable('dropship')} ADD max_package_weight varchar(225) NULL COMMENT 'webshopapps dropship';
");
		
$installer->endSetup();