<?php
class NextBits_FormBuilder_Model_Mysql4_Formfields
	extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('formbuilder/formfields','field_id');
	}
	
	protected function _afterDelete(Mage_Core_Model_Abstract $object){
		//delete values
		$values = $this->_getReadAdapter()->delete($this->getTable('formbuilder/formbuilderresultsvalues'),'field_id ='. $object->getFieldId());	
		
		return parent::_afterDelete($object);
	}
}  
?>