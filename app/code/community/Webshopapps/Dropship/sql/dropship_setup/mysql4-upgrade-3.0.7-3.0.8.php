<?php

$installer = $this;

$installer->startSetup();

$installer->run("


ALTER IGNORE TABLE {$this->getTable('dropship')} ADD fedex_account_id varchar(225) DEFAULT NULL;

ALTER IGNORE TABLE {$this->getTable('dropship')} ADD fedexsoap_key varchar(225) DEFAULT NULL;
ALTER IGNORE TABLE {$this->getTable('dropship')} ADD fedexsoap_password varchar(225) DEFAULT NULL;
ALTER IGNORE TABLE {$this->getTable('dropship')} ADD fedexsoap_meter_number varchar(225) DEFAULT NULL;

ALTER IGNORE TABLE {$this->getTable('dropship')} ADD ups_password varchar(225) DEFAULT NULL;
ALTER IGNORE TABLE {$this->getTable('dropship')} ADD ups_access_license_number varchar(225) DEFAULT NULL;
ALTER IGNORE TABLE {$this->getTable('dropship')} ADD ups_user_id varchar(225) DEFAULT NULL;
ALTER IGNORE TABLE {$this->getTable('dropship')} ADD ups_shipper_number varchar(225) DEFAULT NULL;

ALTER IGNORE TABLE {$this->getTable('dropship')} ADD usps_user_id varchar(225) DEFAULT NULL;


");


$installer->endSetup();