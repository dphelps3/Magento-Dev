<?php

/**
 * Created by PhpStorm.
 * User: mageuser
 * Date: 6/2/16
 * Time: 7:37 AM
 */
class Hawksearch_Proxy_Model_System_Config_Layout
extends Mage_Catalog_Model_Category_Attribute_Source_Layout
{
    public function toOptionArray() {
        return $this->getAllOptions();
    }
}