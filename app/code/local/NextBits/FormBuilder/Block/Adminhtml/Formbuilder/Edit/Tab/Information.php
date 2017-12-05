<?php
class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit_Tab_Information
	extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout(){
		
		parent::_prepareLayout();
	}	
	
	protected function _prepareForm()
	{
		$model = Mage::getModel('formbuilder/formbuilder');
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('formbuilder_form',array(
			'legend' => Mage::helper('formbuilder')->__('Form Information')
		));
		$fieldset->addField('name','text',array(
			'label' => Mage::helper('formbuilder')->__('Name'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'name'
		));
		
		$fieldset->addField('code','text',array(
			'label' => Mage::helper('formbuilder')->__('Code'),
			'name' => 'code',
			'note' => Mage::helper('formbuilder')->__('Code is used to help identify form'),
		));
		$style = 'height:20em; width:50em;';
		try{
				$config = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
					  array(
						'add_widgets' => true,
					  'add_variables' => true));
					$config->setData(Mage::helper('formbuilder')->recursiveReplace('/formbuilder/', '/'.(string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName').'/', $config->getData()));
		}catch (Exception $ex) {
				  $config = null;
		}
		//$renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element');
		
		$descField = $fieldset->addField('description','editor',array(
			'label' => Mage::helper('formbuilder')->__('Description'),
			'required' => false,
			'name' => 'description',
			'style'     =>$style,
			'note' => Mage::helper('formbuilder')->__('This text will appear under the form name'),
			'wysiwyg' => true,
			'config' => $config,
		));
		
		$succField = $fieldset->addField('success_text','editor',array(
			'label' => Mage::helper('formbuilder')->__('Success text'),
			'required' => false,
			'name' => 'success_text',
			'style' => $style,
			'note' => Mage::helper('formbuilder')->__('This text will be displayed after the form completion'),
			'wysiwyg' => true,
			'config' => $config,
		));
		 
		
		
		$fieldset->addField('css','textarea',array(
			'label' => Mage::helper('formbuilder')->__('Form CSS'),
			'name' => 'css',
			/* 'note' => Mage::helper('formbuilder')->__('Form CSS'), */
		));
		$fieldset->addField('menu', 'select', array(
			'label'     => Mage::helper('formbuilder')->__('Display in Admin Menu'),
			'title'     => Mage::helper('formbuilder')->__('Display in Admin Menu'),
			'name'      => 'menu',
			'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));
		
		$fieldset->addField('is_active', 'select', array(
			'label'     => Mage::helper('formbuilder')->__('Status'),
			'title'     => Mage::helper('formbuilder')->__('Status'),
			'name'      => 'is_active',
			'required'  => false,
			'options'   => $model->getAvailableStatuses(),
		));
		 
		 if (!Mage::registry('form_data')->getId()) {
			$model->setData('is_active', '0');
		}
		
		if(Mage::getSingleton('adminhtml/session')->getFormBuilderData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getFormBuilderData());
			Mage::getSingleton('adminhtml/session')->setFormBuilderData(null);
		} elseif(Mage::registry('form_data')){
			$form->setValues(Mage::registry('form_data')->getData());
		}
	
		
		return parent::_prepareForm();
	}
}  
?>
