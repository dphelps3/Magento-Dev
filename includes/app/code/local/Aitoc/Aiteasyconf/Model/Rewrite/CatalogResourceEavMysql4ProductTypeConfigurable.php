<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Model_Rewrite_CatalogResourceEavMysql4ProductTypeConfigurable extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable
{
    // if we saved smth in easyconf tab - do not save Associated Products tab
    // it's data is different from easyconf and saving it will rewrite easyconf changes
    public function saveProducts($mainProductId, $productIds)
    {
        if (1 == Mage::app()->getRequest()->getParam('aiteasyconf_has_changes')
            || (1 == Mage::app()->getRequest()->getParam('aitmatrix_is_processed'))
        ) {
            return $this;
        }
        return parent::saveProducts($mainProductId, $productIds);
    }
}
