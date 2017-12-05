<?php

class Simplesolutions_Customshipping_Model_Mysql4_Customshipping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the customshipping_id refers to the key field in your database table.
        $this->_init('customshipping/customshipping', 'id');
    }
}