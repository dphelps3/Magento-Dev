<?php
class NextBits_FormBuilder_Block_Adminhtml_Results_Grid
	extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('formbulderResuntGrid');
		$this->setDefaultSort('created_time');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
		//$this->setFilterVisibility(false);
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}
	
	protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}
	
	protected function _filterCustomerCondition($collection,$column){
		if (!$value = trim($column->getFilter()->getValue())) {
			return;
		}
		while(strstr($value,"  ")){
			$value = str_replace("  "," ",$value);
		}
		$customers_array = array();
		$name = explode(" ",$value);
		$firstname = $name[0];
		$lastname = $name[count($name)-1];
		$customers = Mage::getModel('customer/customer')->getCollection()
			->addAttributeToFilter('firstname',$firstname);
		if(count($name)==2)
			$customers->addAttributeToFilter('lastname',$lastname);
		foreach($customers as $customer){
			$customers_array[]= $customer->getId();
		}
		$collection->addFieldToFilter('customer_id', array('in' => $customers_array));
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('formbuilder/formbuilderresult')->getCollection()->addFilter('form_id',$this->getRequest()->getParam('formbuilder_id'));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('id',array(
			'header' => Mage::helper('formbuilder')->__('Id'),
			'align'	=> 'right',
			'width'	=> '50px',
			'index'	=> 'id',
		));
		$fields = Mage::getModel('formbuilder/formfields')->getCollection()
			->addFilter('form_id',$this->getRequest()->getParam('formbuilder_id'));
		
		$fields->getSelect()->order('sort_order asc');
		foreach($fields as $field){
			$field_name = $field->getTitle();
			$config = array(
					'header' => $field_name,
					'index' => 'field_'.$field->getFieldId(),
					'sortable' => false,
					'filter' => false,
					'renderer' => 'NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Renderer_Value'
				);
			
			$config = new Varien_Object($config);
			$this->addColumn($field->getFieldId(), $config->getData());
		}
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'        => Mage::helper('formbuilder')->__('Store View'),
				'index'         => 'store_id',
				'type'          => 'store',
				'store_all'     => true,
				'store_view'    => true,
				'sortable'      => false,
				'filter_condition_callback'	=> array($this, '_filterStoreCondition'),
			));
		}
		$config = array(
			'header' => Mage::helper('formbuilder')->__('Customer'),
			'align' => 'left',
			'index' => 'customer_id',
			'renderer' => 'NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Renderer_Customer',
			'filter_condition_callback' => array($this, '_filterCustomerCondition'),
			'sortable' => false,
			//'filter' => false,
		);
		if($this->_isExport){
			$config['renderer'] = false;
		}
		$this->addColumn('customer_id',$config);
		$this->addColumn('ip',array(
			'header' => Mage::helper('formbuilder')->__('IP'),
			'index' => 'ip',
			'sortable' => false,
			'filter' => false,
		));
		
		
		$this->addColumn('created_time', array(
			'header'    => Mage::helper('formbuilder')->__('Date Created'),
			'index'     => 'created_time',
			'type'      => 'datetime',
		));
		$formModel =Mage::getModel('formbuilder/formbuilder')->load($this->getRequest()->getParam('formbuilder_id'));
		if($formModel->getApprove()){
			$this->addColumn('approved', array(
				'header'    => Mage::helper('formbuilder')->__('Approved'),
				'index'     => 'approved',
				'type'      => 'options',
				'options'   => Array("1"=>$this->__('Yes'),"0"=>$this->__('No')),
			));
			
		}
		
		$this->addExportType('*/*/exportCsv', Mage::helper('formbuilder')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('formbuilder')->__('XML'));
		
		return parent::_prepareColumns();
	}
	
	protected function _filterStoreCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}

		$this->getCollection()->addFilter('store_id',$value);
	}
	
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('id');
					
		$this->getMassactionBlock()->addItem('delete', array(
			 'label'=> Mage::helper('formbuilder')->__('Delete'),
			 'url'  => $this->getUrl('*/*/massDelete',array('form_id'=>$this->getRequest()->getParam('formbuilder_id'))),
			 'confirm' => Mage::helper('formbuilder')->__('Are you sure to delete selected results?'),
		));
				
		$formModel =Mage::getModel('formbuilder/formbuilder')->load($this->getRequest()->getParam('formbuilder_id'));
		if($formModel->getApprove()){
			$this->getMassactionBlock()->addItem('approve', array(
				 'label'=> Mage::helper('formbuilder')->__('Approve'),
				 'url'  => $this->getUrl('*/*/massApprove',array('form_id'=>$this->getRequest()->getParam('formbuilder_id'))),
				 'confirm' => Mage::helper('formbuilder')->__('Approve selected results?'),
			));
			
			$this->getMassactionBlock()->addItem('disapprove', array(
				 'label'=> Mage::helper('formbuilder')->__('Disapprove'),
				 'url'  => $this->getUrl('*/*/massDisapprove',array('form_id'=>$this->getRequest()->getParam('formbuilder_id'))),
				 'confirm' => Mage::helper('formbuilder')->__('Disapprove selected results?'),
			));
		}

		return $this;
	}
}
?>
