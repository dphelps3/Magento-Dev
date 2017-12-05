<?php
	class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
	{
		
		public function __construct()
		{
			parent::__construct();
			$this->_objectId = 'id';
			$this->_blockGroup = 'formbuilder';
			$this->_controller = 'adminhtml_formbuilder';

			if (Mage::registry('form_data') && Mage::registry('form_data')->getId())
			{
			    $this->_removeButton('delete');
				$this->_addButton('duplicate', array
				(
					'label' => Mage::helper('formbuilder')->__('Duplicate Form'),
					'class' => 'add',
					'onclick'   => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
				), -100);

				$this->_addButton('delete', array
				(
					'label' => Mage::helper('formbuilder')->__('Delete Form'),
					'class' => 'delete',
					'onclick' => 'deleteConfirm(\'' . Mage::helper('formbuilder')->__('Are you sure you want to delete the entire form and associated data?') . '\', \'' . $this->getDeleteUrl() . '\')',
				), -1);
				$this->_removeButton('save');
				$click = 'saveAndContinue()';
				$this->_addButton('save', array
				(
					'label' => Mage::helper('formbuilder')->__('Save'),
					'onclick' => $click,
					'class' => 'save',
				), -100);
			}
			else { 
				$this->_removeButton('save'); 
			}

			$click = 'saveAndContinueEdit()';
			
			$this->_addButton('saveandcontinue', array
			(
				'label' => Mage::helper('formbuilder')->__('Save And Continue Edit'),
				'onclick' => $click,
				'class' => 'save',
			), -100);
			
			
			$this->_formScripts[] = "
				function toggleEditor() {
                if (tinyMCE.getInstanceById('description') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'description');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'description');
                }
            }
				function saveAndContinueEdit(){
					editForm.submit($('edit_form').action+'back/edit/');
					var inputs = $$('div.product-custom-options button', 'div.product-custom-options input', 'div.product-custom-options select', 'div.product-custom-options textarea');
					 for (var i=0, l = inputs.length; i < l; i ++) {
						if(inputs[i].hasClassName('validation-failed'))
						{
							ele = inputs[i].up('dd.dd-fieldset');
							Element.addClassName($(ele.id), 'open'); 
							ele2 = inputs[i].up('dd.dd-field');
							if(ele2!=undefined){								
								Element.addClassName($(ele2.id), 'open'); 
							}
							
						}
					 }
				}
				function saveAndContinue(){
					editForm.submit();
					var inputs = $$('div.product-custom-options button', 'div.product-custom-options input', 'div.product-custom-options select', 'div.product-custom-options textarea');
					 for (var i=0, l = inputs.length; i < l; i ++) {
						if(inputs[i].hasClassName('validation-failed'))
						{
							ele = inputs[i].up('dd.dd-fieldset');
							Element.addClassName($(ele.id), 'open'); 
							ele2 = inputs[i].up('dd.dd-field');
							if(ele2!=undefined){								
								Element.addClassName($(ele2.id), 'open'); 
							}
						}
					 }
				}
			";
		}
		public function getDuplicateUrl() { 
			return $this->getUrl('*/adminhtml_formbuilder/duplicate', array ('id' => Mage::registry('form_data')->getId()));	
		}

		public function getAddFieldUrl() { 
				return $this->getUrl('*/adminhtml_fields/edit', array ('form_id' => Mage::registry('form_data')->getId()));
		}

		public function getAddFieldsetUrl() { 
				return $this->getUrl('*/adminhtml_fieldsets/edit', array ('form_id' => Mage::registry('form_data')->getId())); 
		}

		public function getHeaderText()
		{
			if (Mage::registry('form_data') && Mage::registry('form_data')->getId()) { 
				return Mage::helper('formbuilder')->__("Edit '%s' Form", $this->htmlEscape(Mage::registry('form_data')->getName())); 
			}
			else { 
				return Mage::helper('formbuilder')->__('Add Form'); 
			}
		}
	}
	
	
?>