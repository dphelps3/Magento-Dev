<?php

class IWD_QuickView_Block_Wishlist_Cart extends Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Cart
{
       /**
     * @return string
     */
    public function _toHtml()
    {
        $blockName = $this->getNameInLayout();
        if($blockName ==  "customer.wishlist.item.cart") {
            $this->setTemplate('iwd/quickview/wishlist/cart.phtml');
        }
        return parent::_toHtml();
    }
}