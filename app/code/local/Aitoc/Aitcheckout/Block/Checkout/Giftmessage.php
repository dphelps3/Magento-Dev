<?php

class Aitoc_Aitcheckout_Block_Checkout_Giftmessage extends Mage_Core_Block_Template
{
    public function getContainerId()
    {
        $targetDiv = 'allow-gift-options-container';
        if (version_compare(Mage::getVersion(), '1.10.0.0', '<')) {
            $targetDiv = 'allow-gift-message-container';
        }

        return $targetDiv;
    }

    public function getTargetCheckboxId()
    {
        $targetId = 'allow_gift_options';
        if (version_compare(Mage::getVersion(), '1.10.0.0', '<')) {
            $targetId = 'allow_gift_messages';
        }

        return $targetId;
    }

    protected function _toHtml()
    {
        if (Mage::helper('aitcheckout/data')->isDisabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}