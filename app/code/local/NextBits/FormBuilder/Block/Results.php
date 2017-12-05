<?php 
class NextBits_FormBuilder_Block_Results extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{	
	protected $_collection;
	protected function _construct() {
		parent::_construct();
	}
	
	protected function _prepareLayout()
    {
        parent::_prepareLayout();
		$formModel = Mage::getModel('formbuilder/formbuilder')->load($this->getData('result_form_id'));
		if(!empty($formModel)){
		Mage::register('formresult_form',$formModel);
		$collection = Mage::getModel('formbuilder/formbuilderresult')->getCollection();
		$collection->addFieldToFilter('form_id',$this->getData('result_form_id'));
		$formModel = Mage::getModel('formbuilder/formbuilder')->load($this->getData('result_form_id'));
		$aprove = $formModel->getApprove();
		if($aprove == 1)
		$collection->addFieldToFilter('approved',1);
		$collection->setOrder('id');
        $this->setCollection($collection);
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
		if($this->getData('page_size'))
		{
			$size =array($this->getData('page_size') => $this->getData('page_size'));
		}else
		{
			$size =array(5=>5);
		}
        $pager->setAvailableLimit($size);
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
		}else
		{
			return $this;
		}
    }
	
	 public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	
	public function getFields()
	{
		$collection = new Varien_Data_Collection();
		$ids=explode(',',$this->getData('field_ids'));
		foreach($ids as $_id){
			$fieldModel = Mage::getModel('formbuilder/formfields')->load($_id);
			$varienObject = new Varien_Object();
			$varienObject->setData($fieldModel->getData());
			$collection->addItem($varienObject);

		}
		
		return $collection;
	}
	
	
}
?>