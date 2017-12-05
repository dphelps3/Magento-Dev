<?php


class Simplesolutions_HardwareCoupons_Block_Adminhtml_Codes extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_codes";
	$this->_blockGroup = "hardwarecoupons";
	$this->_headerText = Mage::helper("hardwarecoupons")->__("Codes Manager");
	$this->_addButtonLabel = Mage::helper("hardwarecoupons")->__("Add New Item");
	parent::__construct();

	}

}
