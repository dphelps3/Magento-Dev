<?php

if (version_compare(Mage::getVersion(), '1.12.0.0', '<')) {
    class Aitoc_Aitcheckout_Block_Captcha extends Mage_Core_Block_Template
    {

    }
} else {
    class Aitoc_Aitcheckout_Block_Captcha extends Mage_Captcha_Block_Captcha
    {

        protected function _prepareLayout()
        {
            $headBlock = $this->getLayout()->getBlock('head');
            if ($headBlock) {
                $headBlock->addJs('mage/captcha.js');
            }

            return parent::_prepareLayout();
        }

    }
}