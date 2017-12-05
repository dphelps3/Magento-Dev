<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Model_Mysql4_Temporary_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('aiteasyconf/temporary');
    }

    public function filterByMainId($mainId)
    {
        $this->addFieldToFilter('configurable_id', $mainId);
        return $this;
    }

    public function addFieldToSelect($field, $alias = null)
    {
        if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
            return parent::addFieldToSelect($field);
        } else {
            $this->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns($field);
        }

        return $this;
    }
}