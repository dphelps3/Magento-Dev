<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$formfieldTable = $this->getTable('formbuilder/formfields');
$installer->startSetup();

$installer->run("
ALTER TABLE `{$formfieldTable}`
    ADD `html` TEXT NULL DEFAULT NULL;
");

$installer->endSetup();
