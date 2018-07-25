<?php
/**
 * Created by PhpStorm.
 * User: mageuser
 * Date: 4/16/18
 * Time: 12:44 PM
 */

class Hawksearch_Datafeed_Model_Product extends Mage_Catalog_Model_Product
{

    protected function _init($resourceModel)
    {
        $this->_setResourceModel($resourceModel, 'hawksearch_datafeed/product_collection');
    }
}
