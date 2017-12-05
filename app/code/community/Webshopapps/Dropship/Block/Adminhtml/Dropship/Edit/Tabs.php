<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Dropship_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function _construct()
  {
      parent::_construct();
      $this->setId('dropship_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('dropship')->__('Warehouse Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('dropship')->__('Warehouse Information'),
          'title'     => Mage::helper('dropship')->__('Warehouse Information'),
          'content'   => $this->getLayout()->createBlock('dropship/adminhtml_dropship_edit_tab_form')->toHtml(),
      ));
      
       $this->addTab('form_ups', array(
          'label'     => Mage::helper('dropship')->__('UPS Login Details'),
          'title'     => Mage::helper('dropship')->__('UPS Login Details'),
          'content'   => $this->getLayout()->createBlock('dropship/adminhtml_dropship_edit_tab_ups')->toHtml(),
      ));
      
      $this->addTab('form_fedex', array(
          'label'     => Mage::helper('dropship')->__('Fedex Login Details'),
          'title'     => Mage::helper('dropship')->__('Fedex Login Details'),
          'content'   => $this->getLayout()->createBlock('dropship/adminhtml_dropship_edit_tab_fedex')->toHtml(),
      ));
      
      $this->addTab('form_usps', array(
          'label'     => Mage::helper('dropship')->__('USPS Login Details'),
          'title'     => Mage::helper('dropship')->__('USPS Login Details'),
          'content'   => $this->getLayout()->createBlock('dropship/adminhtml_dropship_edit_tab_usps')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}