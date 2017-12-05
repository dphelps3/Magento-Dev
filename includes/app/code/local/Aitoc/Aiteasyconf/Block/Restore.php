<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Block_Restore extends Mage_Adminhtml_Block_Widget
{
    private $_detected = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aiteasyconf/restore.phtml');
    }

    public function getDetected()
    {
        if (null === $this->_detected) {
            $this->_detected = Mage::getModel('aiteasyconf/temporary')->detect($this->getId());
        }

        return $this->_detected;
    }

    public function _toHtml()
    {
        if ($this->getDetected()) {
            return parent::_toHtml();
        }

        return '';
    }

    protected function _getHelper()
    {
        return Mage::helper('aiteasyconf');
    }
}