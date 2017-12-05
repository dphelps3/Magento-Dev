<?php
class NextBits_FormBuilder_Model_Mysql4_Formbuilder
	extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('formbuilder/formbuilder','id');
	}
	
	protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		
		$formId=$object->getId();
		// $formId = 5 - log1
		if($formId == 1) {
				Mage::log($object, null, 'aaronFormBuilder.log');
		}
		//Mage::log($formId, null, 'aaronFormBuilder.log');
		if(isset($formId) && !empty($formId))
		{
			$data =$object->getData();
			// $data has the form fields, but not the field options - log1
			//Mage::log($data, null, 'aaronFormBuilder.log');
			if(isset($data['formbuilder']['fieldset']) && !empty($data['formbuilder']['fieldset'])){
				$fieldset = $data['formbuilder']['fieldset'];
			}
			
			if(isset($data['product']['options']) && !empty($data['product']['options'])){
				$fields = $data['product']['options'];
				// $fields has all the fields information- log2
				//Mage::log($fields, null, 'aaronFormBuilder.log');
			}		
			
			$delete=array();
			if(!empty($fieldset)){
				foreach($fieldset as $key=>$value)
				{
					if($value['option_id'] == '0' && $value['is_delete'] ==1)
					{
						unset($fields[$key]);
						continue;
					}
					$flag= false;
					$formfieldsetmodel=Mage::getModel('formbuilder/formfieldset');
					$formfieldsetmodel->setData($value)
									 ->setFormId($object->getId());
					if ($value['option_id'] == '0') {
						$flag=true;
						$formfieldsetmodel->unsetData('id');
					} else {
						$formfieldsetmodel->setId($value['option_id']);
					}
					if ($value['is_delete'] == '1') {
						$delete[]=$key;
						$formfieldsetmodel->delete();
					}else
					{
						$formfieldsetmodel->save();
						$id = $formfieldsetmodel->getId();
						if($flag == true)
						{
							if(is_array($fields) && !empty($fields))
							{
								$values=$fields[$key];
								if(is_array($values) && !empty($values))
								{
									unset($fields[$key]);
									$fields[$id]=$values;
									
								}
							}
						}
					}
				}
			}
		
			$deleteField =array();
			if(isset($fields) && is_array($fields) && !empty($fields))
			{
				
				foreach($fields as $key=>$value)
				{
					foreach($value as $_key=>$_value){
						$formfieldsmodel=Mage::getModel('formbuilder/formfields');
						$formfieldsmodel->setData($_value)
									 ->setFieldsetId($key)
									 ->setFormId($object->getId());
										
						if ($_value['option_id'] == '0') {
						$formfieldsmodel->unsetData('field_id');
						} else {
							$formfieldsmodel->setFieldId($_value['option_id']);
						}
						if ($_value['is_delete'] == '1') {
							if($_value['option_id'] != '0'){
								$deleteField[]=$formfieldsmodel->getFieldId();
								$formfieldsmodel->delete();
							}
						}else if(in_array($key,$delete))
						{
							$formfieldsmodel->delete();
						}
						else
						{
							$formfieldsmodel->save();
							$values = array();
							if(isset($_value['values']) && !empty($_value['values'])){
								$values = $_value['values'];
							}
							if(is_array($values) && !empty($values))
							{	
								$temp = array();
								if(isset($values['default']) && !empty($values['default'])){
									$temp = $values['default'];
								}
								unset($values['default']);
								foreach($values as $_key => $_option)
								{
									if(!isset($_option['duplicate'])){
										if(in_array($_key,$temp))
										{
											$_option['default']=1;
											//echo "done";exit;
										}else
										{
											$_option['default']=0;
										}
									}
									$optionModel=Mage::getModel('formbuilder/formbuilderoption');
									$optionModel->setData($_option)
												->setFieldId($formfieldsmodel->getFieldId());
									if ($_option['option_type_id'] == '-1') {
									$optionModel->unsetData('option_id');
									} else {
										$optionModel->setOptionId($_option['option_type_id']);
									}
									if ($_option['is_delete'] == '1') {
											$optionModel->delete();
									}
									else if(in_array($formfieldsmodel->getFieldId(),$deleteField))
									{
										$optionModel->delete();
									}
									else
									{
										
										$optionModel->save();
									}
								}
							}
						}
					}
				}
			}
			
			foreach($deleteField as $key=>$value)
			{
				$optionModel= Mage::getModel('formbuilder/formbuilderoption')->getCollection()
							->addFieldToFilter('field_id',$key);
				$data = $optionModel->getData();
				foreach($data as $_key=>$_value)
				{
					$optModel= Mage::getModel('formbuilder/formbuilderoption')->load($_value['field_id']);
					$optModel->delete();
					unset($optModel);
				}
				
			}
		}
	}
	
	protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
		
		$formId = $object->getId();
		$formfieldsetmodel=Mage::getModel('formbuilder/formfieldset')->getCollection();
		
		$finalfields =array();
		if(isset($formId) && !empty($formId))
		{
			$formfieldsetmodel->addFieldToFilter('form_id',$formId);
			$formfieldsetmodel->getSelect()->order('sort_order desc');
			$object->setFormFieldData($formfieldsetmodel->getData());
			$fieldsetdata=$formfieldsetmodel->getData();
			$fieldset =array();
			foreach($fieldsetdata as $key=>$data)
			{
				$fieldset[] =$data['id'];
			}
			foreach($fieldset as $value)
			{
				$formfieldsmodel=Mage::getModel('formbuilder/formfields')->getCollection();
				$formfieldsmodel->addFieldToFilter('fieldset_id',$value);
				$formfieldsmodel->getSelect()->order('sort_order desc');
				$finalfields[$value]=$formfieldsmodel->getData();
			}
			
			$object->setFieldsData($finalfields);
		}
		
	}
	
	protected function _afterDelete(Mage_Core_Model_Abstract $object){
		//delete fields
		$fields = Mage::getModel('formbuilder/formfields')->getCollection()->addFilter('form_id',$object->getId());
		foreach($fields as $field){
			$field->delete();
		}
		//delete fieldsets
		$fieldsets = Mage::getModel('formbuilder/formfieldset')->getCollection()->addFilter('form_id',$object->getId());
		foreach($fieldsets as $fieldset){
			$fieldset->delete();
		}
		
		$result = Mage::getModel('formbuilder/formbuilderresult')->getCollection()->addFilter('form_id',$object->getId());
		foreach($result as $_result){
			$_result->delete();
		}
		
		return parent::_afterDelete($object);
	}
}  
?>