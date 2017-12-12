<?php

class Aitoc_Aitcheckout_Helper_Aitgiftwrap extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_isEnabled = null;

    /**
     * Check whether the GR module is active or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        if ($this->_isEnabled === null) {
            $this->_isEnabled = ($this->isModuleEnabled('Aitoc_Aitgiftwrap')
                && Mage::app()->getLayout()->createBlock(
                    'aitgiftwrap/giftwrap_onepage'
                )->isShow()) ? true : false;
        }

        return $this->_isEnabled;
    }
}