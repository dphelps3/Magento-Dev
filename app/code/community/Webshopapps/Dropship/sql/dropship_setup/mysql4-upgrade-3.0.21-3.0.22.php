<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD manual_ship INT(1) DEFAULT 0;
");

$installer->endSetup();