<?php
class NextBits_FormBuilder_Model_Mysql4_Formbuilder_Collection
	extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	
	public function _construct(){
		parent::_construct();
		$this->_init('formbuilder/formbuilder');
	}
	
	
	protected function _afterLoad()
	{
		parent::_afterLoad();
		
		foreach ($this as $item) {
			$fields = Mage::getModel('formbuilder/formfields')->getCollection()->addFilter('form_id',$item->getId())->count();
			$item->setData('fields',$fields);
			$results = Mage::getModel('formbuilder/formbuilderresult')->getCollection()->addFilter('form_id',$item->getId())->count();
			$item->setData('results',$results);
			$last_result = Mage::getModel('formbuilder/formbuilderresult')->getCollection()->addFilter('form_id',$item->getId());
			$last_result->getSelect()->order('created_time desc')->limit(1);
			
			$item->setData('last_result_time',$last_result->getFirstItem()->getData('created_time'));
		}
	}
	
	
}  
?>
