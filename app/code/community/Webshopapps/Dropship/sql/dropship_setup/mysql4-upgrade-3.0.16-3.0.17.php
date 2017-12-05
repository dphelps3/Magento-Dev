=<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('dropship')} ADD `manualship` tinyint(1) NOT NULL default '1' COMMENT 'webshopapps dropship'");

$installer->endSetup();