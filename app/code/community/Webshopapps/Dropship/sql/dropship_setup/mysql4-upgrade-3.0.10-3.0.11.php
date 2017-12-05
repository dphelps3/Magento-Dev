<?php

$installer = $this;

$installer->startSetup();

$installer->run("


ALTER IGNORE TABLE {$this->getTable('dropship')} ADD ups_unit_of_measure varchar(10) DEFAULT NULL;


");


$installer->endSetup();