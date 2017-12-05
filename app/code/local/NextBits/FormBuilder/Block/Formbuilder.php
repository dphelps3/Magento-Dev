<?php
	class NextBits_FormBuilder_Block_Formbuilder extends Mage_Core_Block_Template
	{
		protected $_form;
		
		public function __construct()
		{
			  parent::__construct();

			  $data =  Mage::getSingleton('core/session')->getFormData(true);
			  $data = new Varien_Object($data);
			  Mage::register('formbuilder',$data);
			  $this->setTemplate('formbuilder/default.phtml')
				  //->assign('value', $data)
				  ->assign('messages', Mage::getSingleton('core/session')->getMessages(true));
		} 
		
		protected function _toHtml()
		{
		
			if (!Mage::registry('form_preview'))
			$this->initForm();	
			return parent::_toHtml();
		}
		public function getForm()
		{
			return $this->_form;
		}
		public function setForm($form)
		{
			$this->_form = $form;
			return $this;
		}
		protected function _prepareLayout()
		{
			parent::_prepareLayout();

			if (Mage::registry('form_preview')){
				
				//$this->initForm();
				
				if ($this->getLayout()->getBlock('head'))
					$this->getLayout()->getBlock('head')->setTitle($this->getForm()->getName());
			}
		}
			
		protected function initForm()
		{

				$show_success = false;
				$data = $this->getFormData();

				//get form data
				$form = Mage::getModel('formbuilder/formbuilder')->load($data['form_id']);
				$this->setForm($form);
				$form->setDescription(Mage::helper('cms')->getPageTemplateProcessor()->filter($form->getDescription()));
				$form->setSuccessText(Mage::helper('cms')->getPageTemplateProcessor()->filter($form->getSuccessText()));
				if (!Mage::registry('form'))
					Mage::register('form', $form);
				Mage::register('fields_to_fieldsets', $form->getFieldsToFieldsets());

			return $this;
		}
		
		public function getFormData()
		{
			$data = $this->getRequest()->getParams();
			
			if (isset($data['id'])) { 
					$data['form_id'] = $data['id']; 
			}
			if ($this->getData('form_id')) { $data['form_id'] = $this->getData('form_id'); }
			return $data;
		}
		
		public function getFormAction()
		{	
			return Mage::getUrl('formbuilder/index/submit');
		}
		
		public function getEnctype()
		{
			return 'multipart/form-data'; 
		}
	}

?>