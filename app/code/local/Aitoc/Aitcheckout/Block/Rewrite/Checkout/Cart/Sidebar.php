<?php

class Aitoc_Aitcheckout_Block_Rewrite_Checkout_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    protected function _toHtml()
    {
        if ($this->getBlockAlias() == 'topCart'
            && !($this->helper('aitcheckout')->isDisabled()
                || !$this->helper(
                    'aitcheckout'
                )->isShowCartInCheckout())
        ) {
            return '';
        }

        return parent::_toHtml();
    }
}
