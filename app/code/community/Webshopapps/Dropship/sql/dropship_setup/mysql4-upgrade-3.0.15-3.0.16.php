<?php

$installer = $this;

$installer->startSetup();

$installer->run("

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='warehouse';

UPDATE IGNORE {$this->getTable('catalog_eav_attribute')} SET used_in_product_listing=0 WHERE attribute_id=@attribute_id;

");

$installer->endSetup();