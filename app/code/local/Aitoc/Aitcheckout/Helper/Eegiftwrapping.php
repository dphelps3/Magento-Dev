<?php

class Aitoc_Aitcheckout_Helper_Eegiftwrapping extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_isEnabled = null;

    /**
     * Check whether the EE_GW module is active or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        if ($this->_isEnabled === null) {
            $this->_isEnabled = (version_compare(Mage::getVersion(), '1.10.0.0', '>=')
                && $this->isModuleEnabled(
                    'Enterprise_GiftWrapping'
                )) ? true : false;
        }

        return $this->_isEnabled;
    }
}