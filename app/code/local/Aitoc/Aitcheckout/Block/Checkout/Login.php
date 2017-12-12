<?php

class Aitoc_Aitcheckout_Block_Checkout_Login extends Mage_Checkout_Block_Onepage_Abstract
{
    public function getCaptchaReloadUrl()
    {
        if (!$this->helper('aitcheckout/captcha')->checkIfCaptchaEnabled()) {
            return '';
        }
        $blockPath = Mage::helper('captcha')->getCaptcha('user_login')->getBlockName();
        $block     = $this->getLayout()->createBlock($blockPath);

        return $block->getRefreshUrl();
    }

}
