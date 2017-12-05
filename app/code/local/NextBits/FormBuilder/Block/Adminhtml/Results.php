<?php
class NextBits_FormBuilder_Block_Adminhtml_Results extends Mage_Adminhtml_Block_Widget_Grid_Container{
	public function __construct(){
		$this->_controller = 'adminhtml_results';
		$this->_blockGroup = 'formbuilder';
		$formbuilder = Mage::getModel('formbuilder/formbuilder')->load($this->getRequest()->getParam('formbuilder_id'));
		if(!Mage::registry('form_data')){
			Mage::register('form_data',$formbuilder);
		}
		$this->_headerText = $formbuilder->getName();
		parent::__construct();
		$this->_removeButton('add');
		$this->_addButton('edit', array(
			'label'     => Mage::helper('formbuilder')->__('Edit Form'),
			'onclick'   => 'setLocation(\'' . $this->getEditUrl() .'\')',
			'class'     => 'edit',
		));
	}
	
	public function getEditUrl(){
		return $this->getUrl('*/adminhtml_formbuilder/edit',array('id'=>$this->getRequest()->getParam('formbuilder_id')));
	}
}  
?>
