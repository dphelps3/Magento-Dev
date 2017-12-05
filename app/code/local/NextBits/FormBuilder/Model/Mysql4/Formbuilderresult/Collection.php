<?php
class NextBits_FormBuilder_Model_Mysql4_Formbuilderresult_Collection
	extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	
	public function _construct(){
		parent::_construct();
		$this->_init('formbuilder/formbuilderresult');
	}
	
	protected function _afterLoad()
	{
		parent::_afterLoad(); 
		foreach ($this as $item) {
			$query = $this->getConnection()->select()
				->from($this->getTable('formbuilder/formbuilderresultsvalues'))
				->where($this->getTable('formbuilder/formbuilderresultsvalues').'.result_id = '.$item->getId())
				;
			
			$results = $this->getConnection()->fetchAll($query);
			foreach($results as $result){
				$item->setData('field_'.$result['field_id'],trim($result['value']));
			}
			
			$item->setData('ip',long2ip($item->getCustomerIp()));
			
		}
	
		return $this;
	}
	
	
}  
?>
