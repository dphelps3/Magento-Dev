<?php
class IWD_QuickView_Helper_Settings extends Mage_Core_Helper_Abstract
{
    const GENERAL_ENABLED               = "iwd_quickview/general/enable";

    const QV_SELECTOR_PRODUCT  = "iwd_quickview/qv/selector";
    const QV_AFTER_ADD_TO_CART     = "iwd_quickview/qv/addtocart_after";
    const QV_SHOW_DROPDOWN = 'iwd_quickview/qv/show_dropdown';
    const AAC_SELECTOR_PRODUCT  = "iwd_quickview/aac/selector";
    const AAC_AFTER_ADD_TO_CART     = "iwd_quickview/aac/addtocart_after";
    const AAC_SHOW_DROPDOWN = 'iwd_quickview/aac/show_dropdown';

    const DESIGN_PRODUCT_NAME_ENABLE    = "iwd_quickview/design_product_name/enable";
    /* ... */





    public function isExtensionEnable(){
        return Mage::getStoreConfig(self::GENERAL_ENABLED) ? true : false;
    }
}