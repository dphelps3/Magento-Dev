<?php
class NextBits_FormBuilder_Model_Formfields
	extends Mage_Core_Model_Abstract
{
	
	public function toOptionArray()
	{
		$validator = new Varien_Object(array
		(
			''=> Mage::helper('formbuilder')->__(''),
			//'required-entry'=> Mage::helper('formbuilder')->__('Required Entry'),
			'validate-select'=> Mage::helper('formbuilder')->__('Validate Select'),
			//'validate-number'=> Mage::helper('formbuilder')->__('Validate Number'),
			'validate-digits'=> Mage::helper('formbuilder')->__('Validate Digits'),
			'validate-alpha'=> Mage::helper('formbuilder')->__('Validate Alpha'),
			//'validate-code'=> Mage::helper('formbuilder')->__('Validate Code'),
			'validate-alphanum'=> Mage::helper('formbuilder')->__('Validate Alphanum'),
			//'validate-street'=> Mage::helper('formbuilder')->__('Validate Street'),
			//'validate-phoneStrict'=> Mage::helper('formbuilder')->__('Validate PhoneStrict'),
			//'validate-phoneLax'=> Mage::helper('formbuilder')->__('Validate PhoneLax'),
			//'validate-fax'=> Mage::helper('formbuilder')->__('Validate Fax'),
			'validate-date'=> Mage::helper('formbuilder')->__('Validate Date'),
			'validate-email'=> Mage::helper('formbuilder')->__('Validate Email'),
			//'validate-password'=> Mage::helper('formbuilder')->__('Validate Password'),
			//'validate-admin-password'=> Mage::helper('formbuilder')->__('Validate Admin Password'),
			//'validate-cpassword'=> Mage::helper('formbuilder')->__('Validate Cpassword'),
			'validate-url' =>Mage::helper('formbuilder')->__('Validate URL'),
			'validate-clean-url'=>Mage::helper('formbuilder')->__('Validate Clean URL'),
			//'validate-identifier'=>Mage::helper('formbuilder')->__('Validate Indentifier'),
			//'validate-xml-identifier'=>Mage::helper('formbuilder')->__('Validate XML Indentifier'),
			//'validate-ssn'=>Mage::helper('formbuilder')->__('Validate SSN'),
			//'validate-zip'=>Mage::helper('formbuilder')->__('Validate Zip'),
			//'validate-zip-international'=>Mage::helper('formbuilder')->__('Validate Zip Code'),
			//'validate-date-au'=>Mage::helper('formbuilder')->__('Validate Date Format'),
			//'validate-currency-dollar'=>Mage::helper('formbuilder')->__('Validate Currency Dollar'),
			//'validate-one-required'=>Mage::helper('formbuilder')->__('Validate One Required'),
			//'validate-one-required-by-name'=>Mage::helper('formbuilder')->__('Validate One Required By Name'),
			'validate-not-negative-number'=>Mage::helper('formbuilder')->__('Validate Non Negative Number'),
			//'validate-state'=>Mage::helper('formbuilder')->__('Validate State'),
			//'validate-new-password'=>Mage::helper('formbuilder')->__('Validate New Password'),
			'validate-greater-than-zero'=>Mage::helper('formbuilder')->__('Validate Greater Than Zero'),
			'validate-zero-or-greater'=>Mage::helper('formbuilder')->__('Validate Zero or greater'),
			//'validate-cc-number'=>Mage::helper('formbuilder')->__('Validate CC Number'),
			//'validate-cc-type'=>Mage::helper('formbuilder')->__('Validate CC Type'),
			//'validate-cc-type-select'=>Mage::helper('formbuilder')->__('Validate CC Type Select'),
			///'validate-cc-exp'=>Mage::helper('formbuilder')->__('Validate CC EXP'),
			//'validate-cc-cvn'=>Mage::helper('formbuilder')->__('Validate CC CVN'),
			//'validate-data'=>Mage::helper('formbuilder')->__('Validate Data')
		));
		
		return $validator->getData();
	}
	public function _construct()
	{
		parent::_construct();
		$this->_init('formbuilder/formfields');
	}
	
	public function getFilter()
	{
		$filter = new Varien_Filter_Template_Simple();

		$customer = Mage::getSingleton('customer/session')->getCustomer();

		if ($customer->getDefaultBillingAddress())
		{
			foreach ($customer->getDefaultBillingAddress()->getData() as $key => $value)
				$filter->setData($key, $value);
		}
		$filter->setData('firstname', $customer->getFirstname());
		$filter->setData('lastname', $customer->getLastname());
		$filter->setData('email', $customer->getEmail());
		return $filter;
	}
	
	public function getOptionsArray()
	{
		$optionModel = Mage::getModel('formbuilder/formbuilderoption')->getCollection()->addFieldToFilter('field_id',$this->getFieldId());
		$optionModel->setOrder('sort_order','ASC');
		$data=$optionModel->getData();
		$option =array();
		//$option[] = array('label'=>'--Please Select--','value'=>'');
		foreach($data as $key=>$value)
		{
			$option[]=array(
					'label' => $value['title'],
					'value' => $value['sku'],
					'checked' => $value['default']
					);
		}
		return $option;
	}
	public function getRadioOptionsArray()
	{
		$optionModel = Mage::getModel('formbuilder/formbuilderoption')->getCollection()->addFieldToFilter('field_id',$this->getFieldId());
		$optionModel->setOrder('sort_order','ASC');
		$data=$optionModel->getData();
		$option =array();
		foreach($data as $key=>$value)
		{
			$option[$value['option_id']]=array(
					'label' => $value['title'],
					'value' => $value['sku'],
					'checked' => $value['default']
					);
		}
		return $option;
	}
	
	public function toHtml()
	{
		$html = "";
		$filter = $this->getFilter();
		$field_id = "field[" . $this->getFieldId() . "]";
		$field_name = $field_id;
		$field_type = $this->getType();
		if($field_type =='checkbox' ){
			$field_class = "checkbox";
		}
		else if($field_type =='radio'){
			$field_class = "radio";
		}
		else if($field_type =='multiple'){
			$field_class = "multiselect";
		}
		else if($field_type =='file'){
			$field_class = "";
		}
		else
		{
			$field_class = "input-text";
		}
		$field_style = "";
		$validate = "";
		if ($this->getMaxCharacters() >0 )
			$field_class .= " validate-length maximum-length-".$this->getMaxCharacters();
		if ($this->getIsRequire() && $this->getType() !='date_time' && $this->getType() !='checkbox' && $this->getType() !='radio')
			$field_class .= " required-entry";
		else if($this->getType() =='date_time' && $this->getIsRequire())
		{
			$field_class .= " ";
		}
		/*else if($this->getType() =='checkbox' && $this->getIsRequire())
		{
			//$field_class .= " validate-one-required-by-name";
		} */
		if ($this->getClass()) { 
			$field_class .= ' ' . $this->getClass(); 
		}
		if ($this->getValidatorClass()) { 
			$field_class .= ' ' . $this->getValidatorClass(); 
		}
		
		$config = array
		(
			'field' => $this,
			'id' => $this->getFieldId(),
			'field_id' => $field_id,
			'field_type' => $field_type,
			'field_name' => $field_name,
			'field_class' => $field_class,
			//'field_style' => $field_style,
			//'field_value' => $field_value,
			'template' => 'formbuilder/fields/field.phtml'
		);
		
		switch ($field_type)
		{
			case 'field':
				$config['template'] = 'formbuilder/fields/field.phtml';
				break;
			
			case 'html':
				$config['html'] =$this->getHtml(); 
				$config['template'] = 'formbuilder/fields/html.phtml';
				break;
				
				
			case 'area':
				$config['template'] = 'formbuilder/fields/area.phtml';
				break;
				
			case 'date':
			case 'date_time':
			case 'time':
				$config['template'] = 'formbuilder/fields/datetime.phtml';
				break;
			
			case 'file':
				$config['template'] = 'formbuilder/fields/file.phtml';
				break;
			
			case 'drop_down':
				$config['field_options'] = $this->getOptionsArray();
				$config['template'] = 'formbuilder/fields/select.phtml';
				break;
			
			case 'radio':
				$config['field_options'] = $this->getRadioOptionsArray();
				$config['template'] = 'formbuilder/fields/radio.phtml';
				break;
			
			case 'checkbox':
				$config['field_options'] = $this->getRadioOptionsArray();
				$config['template'] = 'formbuilder/fields/checkbox.phtml';
				break;
		
			case 'multiple':
				$config['field_options'] = $this->getRadioOptionsArray();
				$config['template'] = 'formbuilder/fields/multipleselect.phtml';
				break;
		}
		$html = Mage::app()->getLayout()->createBlock('core/template', $field_name, $config)->toHtml();
		$html_object = new Varien_Object(array ('html' => $html));
	
		
		return $html_object->getHtml();
	}
	
	
	
	
}
?>