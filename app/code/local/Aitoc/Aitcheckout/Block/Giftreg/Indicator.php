<?php

class Aitoc_Aitcheckout_Block_Giftreg_Indicator extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if (Mage::helper('aitcheckout/adjgiftregistry')->isEnabled()) {
            return $this->getLayout()
                ->createBlock('adjgiftreg/indicator')
                ->setTemplate('adjgiftreg/indicator.phtml')
                ->toHtml();
        }

        return '';
    }
}