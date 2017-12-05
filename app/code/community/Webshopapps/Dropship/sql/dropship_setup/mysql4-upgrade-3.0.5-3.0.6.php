<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER IGNORE TABLE {$this->getTable('webshopapps_shipmethods_carriers')}  ADD warehouse int(5) NULL;
ALTER TABLE {$this->getTable('webshopapps_shipmethods_carriers')} DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `shipmethods_id` , `carrier_code` , `warehouse` ) 


");


$installer->endSetup();