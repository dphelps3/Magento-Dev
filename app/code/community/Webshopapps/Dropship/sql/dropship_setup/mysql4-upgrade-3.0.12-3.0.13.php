<?php

$installer = $this;

$installer->startSetup();

$installer->run("


CREATE TABLE {$this->getTable('webshopapps_sm_upscarriers')} (
    `upscarriers_id` int(11)  unsigned NOT NULL default '0',
    `origin` varchar(255)  NULL,
    `carrier_title` varchar(255)  NULL,
    `warehouse` int(5) NULL,
     PRIMARY KEY  (`upscarriers_id`,`origin`,`warehouse`),
   CONSTRAINT `FK_WEBSHOPAPPS_SM_UPSCARRIERS` FOREIGN KEY (`upscarriers_id`) REFERENCES {$this->getTable('webshopapps_shipmethods')} (`shipmethods_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");


$installer->endSetup();