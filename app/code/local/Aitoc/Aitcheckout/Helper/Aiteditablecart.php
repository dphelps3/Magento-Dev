<?php

class Aitoc_Aitcheckout_Helper_Aiteditablecart extends Aitoc_Aitcheckout_Helper_Abstract
{
    protected $_isEnabled = null;

    /**
     * Check whether the CCE module is active or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        if ($this->_isEnabled === null) {
            $this->_isEnabled = $this->isModuleEnabled('Aitoc_Aiteditablecart') ? true : false;
        }

        return $this->_isEnabled;
    }
}