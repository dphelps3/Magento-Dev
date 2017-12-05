<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Model_Rewrite_CatalogResourceEavMysql4Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product
{
    public function linkSimpleToConfigurable($configurableId, $simpleId)
    {
        $this->_getWriteAdapter()->insert(
            $this->getTable('catalog/product_super_link'), array(
            'product_id' => $simpleId,
            'parent_id' => $configurableId,
            )
        );

        $this->_getWriteAdapter()->insert(
            $this->getTable('catalog/product_relation'), array(
            'child_id' => $simpleId,
            'parent_id' => $configurableId,
            )
        );

        return $this;
    }
}
