<?php


class Simplesolutions_CabinetCoupons_Block_Adminhtml_Codes extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_codes";
	$this->_blockGroup = "cabinetcoupons";
	$this->_headerText = Mage::helper("cabinetcoupons")->__("Codes Manager");
	$this->_addButtonLabel = Mage::helper("cabinetcoupons")->__("Add New Item");
	parent::__construct();
	
	}

}