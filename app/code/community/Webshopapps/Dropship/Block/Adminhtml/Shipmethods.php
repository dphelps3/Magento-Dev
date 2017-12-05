<?php


/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Block_Adminhtml_Shipmethods extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function _construct()
  {
    $this->_controller = 'adminhtml_shipmethods';
    $this->_blockGroup = 'dropship';
    $this->_headerText = Mage::helper('dropship')->__('Shipping Method Combiner');
    $this->_addButtonLabel = Mage::helper('dropship')->__('Add Definition');
    parent::_construct();
  }
}