<?php

class Simplesolutions_HardwareCoupons_Model_Mysql4_Codes extends Mage_Core_Model_Mysql4_Abstract
{
  protected function _construct()
  {
    $this->_init("hardwarecoupons/codes", "hardware_coupons_id");
  }
}
