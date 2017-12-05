<?php
class NextBits_FormBuilder_Model_Formbuilder
	extends Mage_Core_Model_Abstract
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 0;
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('formbuilder/formbuilder');
	}
	
	public function getAvailableStatuses()
	{
		$statuses = new Varien_Object(array
		(
			self::STATUS_ENABLED => Mage::helper('formbuilder')->__('Enabled'),
			self::STATUS_DISABLED => Mage::helper('formbuilder')->__('Disabled'),
		));
		
		return $statuses->getData();
	}
	
	public function getFieldsToFieldsets($all = false)
	{
		
		//get form fieldsets
		$fieldsets = Mage::getModel('formbuilder/formfieldset')->getCollection()->addFilter('form_id', $this->getId());

		$fieldsets->addFilter('is_status', self::STATUS_ENABLED);

		$fieldsets->getSelect()->order('sort_order asc');
		//echo $fieldsets->getSelect();exit;
		//get form fields
		$fields = Mage::getModel('formbuilder/formfields')->getCollection()->addFilter('form_id', $this->getId());

		$fields->addFilter('status', self::STATUS_ENABLED); 

		$fields->getSelect()->order('sort_order asc');
		//echo $fields->getSelect();exit;
		//fields to fieldsets
		//make zero fieldset
		$fields_to_fieldsets = array ();
		$hidden = array ();
		
		foreach ($fieldsets as $fieldset)
		{
			foreach ($fields as $field)
			{
				if ($field->getFieldsetId() == $fieldset->getId())
				{
					$fields_to_fieldsets[$fieldset->getId()]['fields'][] = $field; 
				}
					
			}
			if (!empty($fields_to_fieldsets[$fieldset->getId()]['fields']))
			{
				$fields_to_fieldsets[$fieldset->getId()]['name'] = $fieldset->getTitle();
				//$fields_to_fieldsets[$fieldset->getId()]['result_display'] = $fieldset->getResultDisplay();
			}
		}
		
		

		return $fields_to_fieldsets;
	}
	
	
	public function toOptionArray()
	{
		$collection = $this->getCollection()->addFilter('is_active', self::STATUS_ENABLED)->addOrder('name', 'asc');
		$option_array = array ();

		foreach ($collection as $form)
			$option_array[] = array
			(
				'value' => $form->getId(),
				'label' => $form->getName()
			);

		return $option_array;
	}

}
?>