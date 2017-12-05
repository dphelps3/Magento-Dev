<?php


/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Dropship extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function _construct()
  {
    $this->_controller = 'adminhtml_dropship';
    $this->_blockGroup = 'dropship';
    $this->_headerText = Mage::helper('dropship')->__('Warehouse Manager');
    $this->_addButtonLabel = Mage::helper('dropship')->__('Add Warehouse');
    parent::_construct();
  }
}