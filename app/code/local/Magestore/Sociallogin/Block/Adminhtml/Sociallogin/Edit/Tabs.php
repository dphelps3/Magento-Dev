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

class Magestore_Sociallogin_Block_Adminhtml_Twlogin_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * Magestore_Sociallogin_Block_Adminhtml_Twlogin_Edit_Tabs constructor.
     */
    public function __construct()
  {
      parent::__construct();
      $this->setId('twlogin_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('sociallogin')->__('Item Information'));
  }

    /**
     * @return mixed
     */
    protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('sociallogin')->__('Item Information'),
          'title'     => Mage::helper('sociallogin')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('twlogin/adminhtml_twlogin_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}