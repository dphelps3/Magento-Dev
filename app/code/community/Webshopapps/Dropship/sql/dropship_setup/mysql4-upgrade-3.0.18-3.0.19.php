<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('dropship')} ADD `ismetapak` tinyint(1) NOT NULL default '0' COMMENT 'webshopapps dropship'");
		
$installer->endSetup();