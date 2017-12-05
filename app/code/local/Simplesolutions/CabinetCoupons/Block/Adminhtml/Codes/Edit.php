<?php
	
class Simplesolutions_CabinetCoupons_Block_Adminhtml_Codes_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "cabinet_coupons_id";
				$this->_blockGroup = "cabinetcoupons";
				$this->_controller = "adminhtml_codes";
				$this->_updateButton("save", "label", Mage::helper("cabinetcoupons")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("cabinetcoupons")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("cabinetcoupons")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
							
						
							jQuery(document).ready(function() {
								jQuery('#cabinet_ids').focus(function() {
									console.log('Open the Popup');
								});
							});
							
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("codes_data") && Mage::registry("codes_data")->getId() ){

				    return Mage::helper("cabinetcoupons")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("codes_data")->getId()));

				} 
				else{

				     return Mage::helper("cabinetcoupons")->__("Add Item");

				}
		}
}