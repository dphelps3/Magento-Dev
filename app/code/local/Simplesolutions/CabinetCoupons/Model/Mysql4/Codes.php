<?php
class Simplesolutions_CabinetCoupons_Model_Mysql4_Codes extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("cabinetcoupons/codes", "cabinet_coupons_id");
    }
}