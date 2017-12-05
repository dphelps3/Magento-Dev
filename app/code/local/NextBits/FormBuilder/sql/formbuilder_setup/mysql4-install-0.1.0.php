<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('formbuilder')};
CREATE TABLE {$this->getTable('formbuilder')} (
  	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`code` VARCHAR(255) NOT NULL,
	`redirect_url` TEXT NOT NULL,
	`description` TEXT NOT NULL,
	`success_text` TEXT NOT NULL,
	`approve` TINYINT(1) NOT NULL,
	`capcha` TINYINT(1) NOT NULL,
	`registered_only` TINYINT(1) NOT NULL,
	`send_email` TINYINT(1) NOT NULL,
	`duplicate_email` TINYINT(1) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`created_time` DATETIME NULL DEFAULT NULL,
	`update_time` DATETIME NULL DEFAULT NULL,
	`is_active` TINYINT(1) NOT NULL DEFAULT '1',
	`menu` TINYINT(1) NOT NULL DEFAULT '1',
	`css` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 ");

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('formbuilder/formfields')};
CREATE TABLE {$this->getTable('formbuilder/formfields')} (
  	`field_id` INT(10) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255) NULL DEFAULT NULL,
	`type` VARCHAR(255) NULL DEFAULT NULL,
	`is_require` TINYINT(1) NULL DEFAULT NULL,
	`sort_order` INT(10) NULL DEFAULT NULL,
	`sku` VARCHAR(255) NULL DEFAULT NULL,
	`max_characters` INT(5) NULL DEFAULT NULL,
	`fieldset_id` INT(10) NULL DEFAULT NULL,
	`form_id` INT(10) NULL DEFAULT NULL,
	`file_extension` VARCHAR(50) NULL DEFAULT NULL,
	`image_size_x` SMALLINT(5) NULL DEFAULT NULL,
	`image_size_y` SMALLINT(5) NULL DEFAULT NULL,
	`option` TEXT NULL,
	`status` TINYINT(5) NULL DEFAULT NULL,
	`validator_class` VARCHAR(50) NULL DEFAULT NULL,
	`class` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`field_id`),
	INDEX `FK_formbuilder_fields_formbuilder_fieldset` (`fieldset_id`),
	INDEX `FK_formbuilder_fields_formbuilder` (`form_id`),
	CONSTRAINT `FK_formbuilder_fields_formbuilder` FOREIGN KEY (`form_id`) REFERENCES `{$this->getTable('formbuilder')}` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_formbuilder_fields_formbuilder_fieldset` FOREIGN KEY (`fieldset_id`) REFERENCES `{$this->getTable('formbuilder/formfieldset')}` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 ");

 
 $installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('formbuilder/formfieldset')};
CREATE TABLE {$this->getTable('formbuilder/formfieldset')} (
  	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`form_id` INT(10) NULL DEFAULT NULL,
	`sort_order` INT(10) NULL DEFAULT NULL,
	`title` VARCHAR(255) NULL DEFAULT NULL,
	`is_status` INT(10) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_formbuilder_fieldset_formbuilder` (`form_id`),
	CONSTRAINT `FK_formbuilder_fieldset_formbuilder` FOREIGN KEY (`form_id`) REFERENCES `{$this->getTable('formbuilder')}` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 ");
 
 
 $installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('formbuilder/formbuilderoption')};
CREATE TABLE {$this->getTable('formbuilder/formbuilderoption')} (
  	`option_id` INT(10) NOT NULL AUTO_INCREMENT,
	`field_id` INT(10) NULL DEFAULT NULL,
	`title` VARCHAR(255) NULL DEFAULT NULL,
	`sku` VARCHAR(255) NULL DEFAULT NULL,
	`sort_order` INT(10) NULL DEFAULT NULL,
	`default` INT(10) NULL DEFAULT NULL,
	PRIMARY KEY (`option_id`),
	INDEX `FK_formbuilder_fields_option_formbuilder_fields` (`field_id`),
	CONSTRAINT `FK_formbuilder_fields_option_formbuilder_fields` FOREIGN KEY (`field_id`) REFERENCES {$this->getTable('formbuilder/formfields')} (`field_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 ");
 
 $installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('formbuilder/formbuilderresult')};
CREATE TABLE {$this->getTable('formbuilder/formbuilderresult')} (
  	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`form_id` INT(10) NOT NULL,
	`store_id` INT(10) NOT NULL,
	`customer_id` INT(10) NOT NULL,
	`customer_ip` BIGINT(20) NOT NULL,
	`created_time` DATETIME NOT NULL,
	`updated_time` DATETIME NOT NULL,
	`approved` TINYINT(1) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_formbuilder_result_formbuilder` (`form_id`),
	CONSTRAINT `FK_formbuilder_result_formbuilder` FOREIGN KEY (`form_id`) REFERENCES `{$this->getTable('formbuilder')}` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 ");
 
 
  $installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('formbuilder/formbuilderresultsvalues')};
CREATE TABLE {$this->getTable('formbuilder/formbuilderresultsvalues')} (
  	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`result_id` INT(10) NULL,
	`field_id` INT(10) NULL DEFAULT NULL,
	`value` TEXT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 ");
 
 $installer->endsetup();
 