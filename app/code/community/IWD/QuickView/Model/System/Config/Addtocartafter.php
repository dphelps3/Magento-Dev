<?php
class IWD_QuickView_Model_System_Config_Addtocartafter
{
    const CONTINUE_SHOP = 'continue';
    const SHOP_CART = 'cart';
    const MESSAGE = 'message';

    public function toOptionArray()
    {
        $helper = Mage::helper('iwd_quickview');
        return array(
            array('value' => self::CONTINUE_SHOP, 'label' => $helper->__('Continue Shopping')),
            array('value' => self::SHOP_CART, 'label' => $helper->__('Go to Shopping Cart')),
            array('value' => self::MESSAGE, 'label' => $helper->__('Show Confirmation Message')),
        );
    }
}