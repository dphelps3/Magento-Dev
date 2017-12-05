<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD warehouse int(5);

delete from {$this->getTable('core_config_data')} where path like 'carriers/mergedrates%';


    ");
$installer->endSetup();