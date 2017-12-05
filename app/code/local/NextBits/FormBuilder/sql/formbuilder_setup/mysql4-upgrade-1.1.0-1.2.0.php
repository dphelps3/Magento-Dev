<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$formfieldTable = $this->getTable('formbuilder/formbuilder');
$installer->startSetup();

$installer->run("
ALTER TABLE `{$formfieldTable}`
    ADD `send_email_field_id` VARCHAR(255) NOT NULL;
");

$installer->endSetup();
