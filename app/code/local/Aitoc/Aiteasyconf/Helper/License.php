<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Helper_License extends Aitoc_Aitsys_Helper_License
{
    public function getRuleTotalCount($ruleCode)
    {
        switch ($ruleCode) {
            case 'product':
                $count = $this->getProductCount();
                break;
            default:
                $count = 0;
                break;
        }
        return $count;
    }

    public function getProductCount()
    {
        return Mage::getModel('catalog/product')
            ->getCollection()
            ->addFieldToFilter('type_id', array('eq' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE))
            ->load()
            ->count();
    }

}
