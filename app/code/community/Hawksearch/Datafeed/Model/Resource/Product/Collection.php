<?php
/**
 * Created by PhpStorm.
 * User: mageuser
 * Date: 4/16/18
 * Time: 1:07 PM
 */

class Hawksearch_Datafeed_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    public function isEnabledFlat()
    {
        if(Mage::helper('hawksearch_datafeed')->getUseEavTables()){
            return false;
        }
        return parent::isEnabledFlat();
    }
}