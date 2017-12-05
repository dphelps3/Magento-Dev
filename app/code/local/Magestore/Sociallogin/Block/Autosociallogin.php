<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */
class Magestore_Sociallogin_Block_Autosociallogin extends Magestore_Sociallogin_Block_Sociallogin
{
    /**
     * @return array
     */
    public function getShownPositions() {
        $shownpositions = Mage::getStoreConfig('sociallogin/general/position', Mage::app()->getStore()->getId());
        $shownpositions = explode(',', $shownpositions);
        return $shownpositions;
    }

    /**
     * @return bool
     */
    public function isShow() {
        if (in_array($this->getBlockPosition(), $this->getShownPositions())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function _beforeToHtml() {
        if (!$this->isShow()) {
            $this->setTemplate(null);
        }
        return parent::_beforeToHtml();
    }
}