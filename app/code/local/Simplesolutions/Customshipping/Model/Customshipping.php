<?php

class Simplesolutions_Customshipping_Model_Customshipping extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customshipping/customshipping');
    }
}