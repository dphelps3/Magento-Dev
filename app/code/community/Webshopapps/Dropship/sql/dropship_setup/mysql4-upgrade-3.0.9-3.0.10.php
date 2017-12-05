<?php

$installer = $this;

$installer->startSetup();

$installer->run("


ALTER IGNORE TABLE {$this->getTable('dropship')} ADD ups_allowed_methods varchar(225) DEFAULT NULL;


");


$installer->endSetup();