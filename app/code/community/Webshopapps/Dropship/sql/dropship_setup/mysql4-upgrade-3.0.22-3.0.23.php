<?php

$installer = $this;

$installer->startSetup();

$installer->run("
        ALTER IGNORE TABLE {$this->getTable('dropship')} ADD fedexsoap_allowed_methods varchar(225) NULL COMMENT 'webshopapps dropship';
				ALTER IGNORE TABLE {$this->getTable('dropship')} ADD usps_allowed_methods varchar(225) NULL COMMENT 'webshopapps dropship';
				ALTER IGNORE TABLE {$this->getTable('dropship')} ADD usps_password varchar(255) NULL COMMENT 'webshopapps dropship';
");

$installer->endSetup();