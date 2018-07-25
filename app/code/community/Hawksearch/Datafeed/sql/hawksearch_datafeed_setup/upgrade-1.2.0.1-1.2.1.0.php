<?php
/**
 * Created by PhpStorm.
 * User: mageuser
 * Date: 6/1/16
 * Time: 9:59 AM
 */

/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();
$this->getConnection()
    ->addColumn($this->getTable('catalog/eav_attribute'), 'is_hawksearch', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned' => true,
        'nullable' => false,
        'default' => 0,
        'comment' => 'Export attribute to HawkSearch'
    ));

$this->getConnection()->update($this->getTable('catalog/eav_attribute'),array('is_hawksearch' => 1), 'is_searchable = 1');

$this->endSetup();