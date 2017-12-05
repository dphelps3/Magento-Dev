<?php

class Simplesolutions_HardwareCoupons_Block_Adminhtml_Codes_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "hardware_coupons_id";
				$this->_blockGroup = "hardwarecoupons";
				$this->_controller = "adminhtml_codes";
				$this->_updateButton("save", "label", Mage::helper("hardwarecoupons")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("hardwarecoupons")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("hardwarecoupons")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}


							jQuery(document).ready(function() {
								jQuery('#hardware_ids').focus(function() {
									console.log('Open the Popup');
								});
							});

						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("codes_data") && Mage::registry("codes_data")->getId() ){

				    return Mage::helper("hardwarecoupons")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("codes_data")->getId()));

				}
				else{

				     return Mage::helper("hardwarecoupons")->__("Add Item");

				}
		}
}
