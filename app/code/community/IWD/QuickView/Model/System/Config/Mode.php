<?php
class IWD_QuickView_Model_System_Config_Mode
{
    const QUICKVIEW = 'qv';
    const AAC = 'aac';
    const QUICKVIEW_AAC = 'qv_aac';

    public function toOptionArray()
    {
        $helper = Mage::helper('iwd_quickview');
        return array(
            array('value' => self::QUICKVIEW, 'label' => $helper->__('Quick View')),
            array('value' => self::AAC, 'label' => $helper->__('Ajax Add To Cart')),
            array('value' => self::QUICKVIEW_AAC, 'label' => $helper->__('Quick View + Ajax Add To Cart')),
        );
    }
}