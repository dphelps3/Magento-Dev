<?php
class NextBits_FormBuilder_Block_Adminhtml_FormBuilder_Renderer_Value
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{	
		
		$field_id = str_replace('field_', '', $this->getColumn()->getIndex());
		$field = Mage::getModel('formbuilder/formfields')->load($field_id);
		$value = $row->getData($this->getColumn()->getIndex());
		$html = '';
		if($field->getType()=='checkbox' || $field->getType()=='multiple' || $field->getType()=='radio'){
			$value = $row->getData($this->getColumn()->getIndex());
			$value = Mage::helper('core')->jsonDecode($value);
			$explode = "";
			if(isset($value)){
				$explode =implode('<br/>',$value);
			}
			$value=$explode;
		}
		if($field->getType()=='date_time')
		{
			$value = $row->getData($this->getColumn()->getIndex());
			$value = Mage::helper('core')->jsonDecode($value);
			$explode = "";
			if(isset($value['date']) && isset($value['hour']) && isset($value['minute']) && isset($value['day_part'])){
				$explode = $value['date'].' '.$value['hour'].':'.$value['minute'].' '.$value['day_part'];
			}
			$value=$explode;
		} 
		if($field->getType()=='time')
		{
			$value = $row->getData($this->getColumn()->getIndex());
			$value = Mage::helper('core')->jsonDecode($value);
			$explode = "";
			if(isset($value['hour']) && isset($value['minute']) && isset($value['day_part'])){
				$explode =$value['hour'].':'.$value['minute'].' '.$value['day_part'];
			}
			$value=$explode;
		} 
		if($field->getType()=='date')
		{
			$value = $row->getData($this->getColumn()->getIndex());
			$value = Mage::helper('core')->jsonDecode($value);
			$explode = "";
			if(isset($value['date'])){
				$explode =$value['date'];
			}
			$value=$explode;
		} 
		if($field->getType()=='file')
		{
			$url=str_replace("\\",'/',$row->getData($this->getColumn()->getIndex()));
			if(isset($url)){
				$fullUrl = explode('=>',$url);				
				if(isset($fullUrl[1])){
					$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'formbuilder'.$fullUrl[1];
				}
				if(isset($fullUrl[0])){
					$html="<a target='_black' href=".$url.">".$fullUrl[0]."</a>";
				}
				$value=$html;
			}else{
				$value = "";
			}
		} 
		return $value;
	}

}
?>