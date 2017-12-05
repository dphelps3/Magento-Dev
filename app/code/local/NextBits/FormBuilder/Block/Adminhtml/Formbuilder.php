<?php
class NextBits_FormBuilder_Block_Adminhtml_Formbuilder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_formbuilder';
		$this->_blockGroup = 'formbuilder';
		$this->_headerText = Mage::helper('formbuilder')->__('Manage Forms');
		$this->_addButtonLabel = Mage::helper('formbuilder')->__('Add New Form');
		parent::__construct();
	}
}  

?>