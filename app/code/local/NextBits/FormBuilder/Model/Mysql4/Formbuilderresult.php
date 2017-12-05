<?php
class NextBits_FormBuilder_Model_Mysql4_Formbuilderresult
	extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('formbuilder/formbuilderresult','id');
	}
	
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		if (! $object->getId() && $object->getCreatedTime() == "") {
			$object->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
		}
		
		$object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
		
		return $this;
	}
	
	protected function _afterSave(Mage_Core_Model_Abstract $object){
		//insert field value
		if(count($object->getData('fields'))>0){
			foreach($object->getData('fields') as $field_id=>$value){
				if(is_array($value)){
					$value = Mage::helper('core')->jsonEncode($value);
				}
				$field = Mage::getModel('formbuilder/formfields')->load($field_id);
				$select = $this->_getReadAdapter()->select()
					->from($this->getTable('formbuilder/formbuilderresultsvalues'))
					->where('result_id = ?', $object->getId())
					->where('field_id = ?', $field_id);	
				$result_value = $this->_getReadAdapter()->fetchAll($select);
				if(!empty($result_value[0])){
					$this->_getWriteAdapter()->update($this->getTable('formbuilder/formbuilderresultsvalues'),array(
						"value" => $value,
						),						
						"id = ". $result_value[0]['id']
					);
					
				} else {
					$this->_getWriteAdapter()->insert($this->getTable('formbuilder/formbuilderresultsvalues'),array(
						"result_id" => $object->getId(),
						"field_id" => $field_id,
						"value" => $value,
					));
				}
			}
		}
		$formId =$object->getData('form_id');
		$formModel=Mage::getModel('formbuilder/formbuilder')->load($formId);
		
		$sendEmail =$formModel->getSendEmail();
		if($sendEmail ==1)
		{
			$this->sendEmail($formModel,$object,'admin');
		}
		$dumplicateEmail =$formModel->getDuplicateEmail();
		if($dumplicateEmail ==1)
		{
			$this->sendEmail($formModel,$object,'customer');
		}
		return parent::_afterSave($object);
	}
	
	
	protected function _afterDelete(Mage_Core_Model_Abstract $object){
		//delete values
	
		$values = $this->_getReadAdapter()->delete($this->getTable('formbuilder/formbuilderresultsvalues'),
			'result_id = '. $object->getId()
		);	
		
		$dir =  Mage::getBaseDir('media') . DS . 'formbuilder' . DS . $object->getFormId() ;
		//$this->rrmdir($dir);
	
		return parent::_afterDelete($object);
	}
	
	public function sendEmail($formModel,$data,$recipient)
	{
		if($recipient =='admin'){
			$fieldId = $formModel->getSendEmailFieldId();
			$email = $data['fields'][$fieldId];
			if(!empty($email))
			{
				if(Mage::helper('customer')->isLoggedIn()){
					$sender['name'] = Mage::getSingleton('customer/session')->getCustomer()->getName();
				}else
				{
					$sender['name'] = $email; 
				}
				$sender['email'] = $email; 
			}else if (Mage::helper('customer')->isLoggedIn()) { 
				$sender['name'] = Mage::getSingleton('customer/session')->getCustomer()->getName();
				$sender['email'] = Mage::getSingleton('customer/session')->getCustomer()->getEmail(); 
			}
			else { 
				$sender['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
				$sender['name'] = Mage::getStoreConfig('trans_email/ident_general/email');
			}
		}else
		{
				$sender['name'] = Mage::app()->getStore()->getFrontendName();
				$sender['email'] = $formModel->getEmail();
		}
		$formName =$formModel->getName();
		$store_name = Mage::app()->getStore()->getName();
		$store_group = Mage::app()->getStore()->getFrontendName();
		if ($recipient == 'customer'){
			$subject = Mage::helper('formbuilder')->__("You have submitted '%s' form on %s website", $formName, $store_name);
		}else
		{
			$subject = Mage::helper('formbuilder')->__("Form '%s' submitted", $formModel->getName());
		}
		
		$storeId = Mage::app()->getStore()->getId();
		$html='';
		if ($recipient == 'admin')
		{
			if ($store_group)
				$html .= Mage::helper('formbuilder')->__('Store group') . ": " . $store_group . "<br />";

			if ($store_name)
				$html .= Mage::helper('formbuilder')->__('Store name') . ": " . $store_name . "<br />";
				$html .= Mage::helper('formbuilder')->__('IP') . ": " . long2ip($data->getCustomerIp()) . "<br />";
		}
		$html .= Mage::helper('formbuilder')->__('Date') . ": " . Mage::helper('core')->formatDate($data->getCreatedTime(), 'medium', true) . "<br />";
		$html .= "<br />";
		$head_html = "";
		$head_html = $html;
		$html = "";
		$fields_to_fieldsets = Mage::getModel('formbuilder/formbuilder')->load($data->getFormId())->getFieldsToFieldsets(true);
		
		foreach ($fields_to_fieldsets as $fieldset)
		{
			$k = false;
			$field_html = "";

			foreach ($fieldset['fields'] as $field)
			{
				
				$value = $data->getFields();  
				if(isset($value[$field->getId()]))
				$value=$value[$field->getId()];
				else
				$value='';
				$field_name = $field->getTitle();
				if (!empty($value))
				{
						if($field->getType() =='checkbox' || $field->getType() =='multiple' || $field->getType() =='radio')
						{
							$value =implode('<br/>',$value);
						}
						if($field->getType() =='date_time')
						{
							$value =implode('<br/>',$value);
							$value =$value['date'].' '.$value['hour'].':'.$value['minute'].' '.$value['day_part'];
						}
						if($field->getType() =='time')
						{
							$value =implode('<br/>',$value);
							$value =$value['hour'].':'.$value['minute'].' '.$value['day_part'];
						}
						
						if($field->getType() =='date')
						{
							$value =$value['date'];
						}
						if($field->getType()=='file')
						{
							$url=str_replace("\\",'/',$value);
							$fullUrl =explode('=>',$url);
							$url =Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'formbuilder'.$fullUrl[1];
							$url="<a target='_black' href=".$url.">".$fullUrl[0]."</a>";
							$value=$url;
						} 
						$field_html .= '<b>' . $field_name . '</b><br/>';
						$field_html .= $value . "<br /><br />";
				}
			}

			if (!empty($fieldset['name']))
				$field_html = '<h2>' . $fieldset['name'] . '</h2>' . $field_html;
			$html .= $field_html;
		}
		$html = $head_html .$html;
		$email  =$formModel->getEmail();
		$vars = Array
		(
			'form_subject' => $subject,
			'form_name' => $formModel->getName(),
			'form_result' => $html,
		);
		
		$vars['noreply'] = Mage::helper('formbuilder')->__('Please, don`t reply to this e-mail!');
		$email_array = explode(',', $email);
		$name='admin';
		$templateId = 'forms_results';
		if ($recipient == 'customer')
		{
			$fieldId = $formModel->getSendEmailFieldId();
			$email = $data['fields'][$fieldId];
			if(!empty($email))
			{
				$email = $email;
			}else if(Mage::helper('customer')->isLoggedIn()){
			$email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
			}
			if(!empty($email)){
			$templateId = 'results_customer';
			$success = Mage::getModel('core/email_template')->setTemplateSubject($subject)->sendTransactional($templateId, $sender, trim($email), $name, $vars, $storeId); 
			}
		}
		if ($recipient == 'admin'){
			foreach ($email_array as $email) { 
				$success = Mage::getModel('core/email_template')->setTemplateSubject($subject)->sendTransactional($templateId, $sender, trim($email), $name, $vars, $storeId); 
			}
		}
		
	}
	

}  
?>