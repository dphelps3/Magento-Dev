<?php

$installer = $this;

$installer->startSetup();

$installer->run("


ALTER IGNORE TABLE {$this->getTable('dropship')} ADD ups_shipping_origin varchar(225) DEFAULT NULL;


");


$installer->endSetup();