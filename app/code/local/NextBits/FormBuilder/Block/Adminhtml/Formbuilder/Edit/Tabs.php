<?php
class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
		parent::__construct();
		$this->setId('formbuilder_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('formbuilder')->__('Form Information'));
	}
	
	protected function _beforeToHtml()
	{
		
		$this->addTab('form_information',array(
			'label' => Mage::helper('formbuilder')->__('Information'),
			'title' => Mage::helper('formbuilder')->__('Information'),
			'content' => $this->getLayout()->createBlock('formbuilder/adminhtml_formbuilder_edit_tab_information')->toHtml(),
		));
		
		$this->addTab('form_settings',array(
			'label' => Mage::helper('formbuilder')->__('Settings'),
			'title' => Mage::helper('formbuilder')->__('Settings'),
			'content' => $this->getLayout()->createBlock('formbuilder/adminhtml_formbuilder_edit_tab_settings')->toHtml(),
		));
		
		if(Mage::registry('form_data') && Mage::registry('form_data')->getId() ){
			$this->addTab('form_fieldsets',array(
				'label' => Mage::helper('formbuilder')->__('Fields Manager'),
				'title' => Mage::helper('formbuilder')->__('Fields Manager'),
				'content' => $this->getLayout()->createBlock('formbuilder/adminhtml_formbuilder_edit_tab_formmaker')->toHtml(),
				//'active'    => $active
			));
			
		} 
		
		if($this->getRequest()->getParam('tab')){
			$this->setActiveTab($this->getRequest()->getParam('tab'));
		}
		
	return parent::_beforeToHtml();
	}
}
?>
