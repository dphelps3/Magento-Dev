<?php

class Aitoc_Aitcheckout_Block_Checkout_Giftwrap extends Mage_Core_Block_Template
{
    public function isShow()
    {
        return (Mage::helper('aitcheckout/aitgiftwrap')->isEnabled());
    }

    protected function _toHtml()
    {
        if ($this->isShow()) {
            $html = $this->getLayout()
                ->createBlock('aitgiftwrap/giftwrap_onepage')
                ->setTemplate('aitgiftwrap/giftwrap.phtml')
                ->toHtml();

            if ($html) {
                return $html . parent::_toHtml();
            }
        }

        return '';
    }
}