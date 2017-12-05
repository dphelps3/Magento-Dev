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
class Magestore_Sociallogin_Block_Adminhtml_Twlogin extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Magestore_Sociallogin_Block_Adminhtml_Twlogin constructor.
     */
    public function __construct() {
        $this->_controller = 'adminhtml_twlogin';
        $this->_blockGroup = 'sociallogin';
        $this->_headerText = Mage::helper('sociallogin')->__('Item Manager');
        $this->_addButtonLabel = Mage::helper('sociallogin')->__('Add Item');
        parent::__construct();
    }
}