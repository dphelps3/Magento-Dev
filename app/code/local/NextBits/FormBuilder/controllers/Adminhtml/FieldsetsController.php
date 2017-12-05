<?php
class NextBits_FormBuilder_Adminhtml_FieldsetsController
	extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('formbuilder/items');
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Forms'), Mage::helper('adminhtml')->__('Edit Field Set'));
		return $this;
	}
	
	public function indexAction(){
		$this->_initAction();
		$this->renderLayout();
	}
	
	public function editAction(){
		
	}
	
}