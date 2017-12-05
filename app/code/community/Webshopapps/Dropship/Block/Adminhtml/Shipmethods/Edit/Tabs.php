<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Shipmethods_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function _construct()
  {
      parent::_construct();
      $this->setId('shipmethods_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('dropship')->__('Combined Rates Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('dropship')->__('Details'),
          'title'     => Mage::helper('dropship')->__('Details'),
          'content'   => $this->getLayout()->createBlock('dropship/adminhtml_shipmethods_edit_tab_form')->toHtml(),
      ));
      $this->addTab('form_carriers', array(
          'label'     => Mage::helper('dropship')->__('Carrier Mapping'),
          'title'     => Mage::helper('dropship')->__('Carrier Mapping'),
          'content'   => $this->getLayout()->createBlock('dropship/adminhtml_shipmethods_edit_tab_carrierform')->toHtml(),
      ));
      $this->addTab('form_upscarriers', array(
          'label'     => Mage::helper('dropship')->__('Advanced UPS Carrier Mapping'),
          'title'     => Mage::helper('dropship')->__('Advanced UPS Carrier Mapping'),
          'content'   => $this->getLayout()->createBlock('dropship/adminhtml_shipmethods_edit_tab_upscarrierform')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}