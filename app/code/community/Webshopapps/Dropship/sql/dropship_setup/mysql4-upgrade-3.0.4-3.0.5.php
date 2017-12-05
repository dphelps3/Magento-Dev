<?php

$installer = $this;

$installer->startSetup();

$installer->run("


    	
ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_item')}  ADD warehouse int(5);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order_item')}  ADD warehouse int(5);    	

");


$installer->endSetup();