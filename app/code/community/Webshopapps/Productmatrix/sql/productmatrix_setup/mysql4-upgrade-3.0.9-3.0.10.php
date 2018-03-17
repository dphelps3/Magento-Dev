<?php

$installer = $this;

$installer->startSetup();

$installer->run("

	update {$this->getTable('eav_attribute')}
        set backend_type     = 'decimal'
        where attribute_code = 'shipping_qty';

");

$installer->endSetup();


