<?php
class NextBits_FormBuilder_Model_Mysql4_Formfieldset
	extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('formbuilder/formfieldset','id');
	}
	
	
	protected function _afterDelete(Mage_Core_Model_Abstract $object){
		//set fields fieldset_id to null
		$fields = Mage::getModel('formbuilder/formfields')->getCollection()->addFilter('fieldset_id',$object->getId());
		foreach($fields as $field){
			$field->delete();
		}

		

		return parent::_afterDelete($object);
	}	
}  
?>