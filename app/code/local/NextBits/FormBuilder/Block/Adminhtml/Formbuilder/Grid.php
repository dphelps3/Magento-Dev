<?php
class NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('formbuilderGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('formbuilder/formbuilder')->getCollection();
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
		
		$this->addColumn('name',array(
			'header' => Mage::helper('formbuilder')->__('Name'),
			'align' => 'left',
			'index' => 'name',
		));
		
		$this->addColumn('fields',array(
			'header' => Mage::helper('formbuilder')->__('Fields'),
			'align' => 'right',
			'index' => 'fields',
			'type'	=> 'number',
			'sortable' => false,
			'filter' => false
		));
		
		$this->addColumn('is_active', array(
			'header'    => Mage::helper('formbuilder')->__('Status'),
			'index'     => 'is_active',
			'type'      => 'options',
			'options'   => Mage::getModel('formbuilder/formbuilder')->getAvailableStatuses(),
		));
		
		$this->addColumn('created_time', array(
			'header'    => Mage::helper('formbuilder')->__('Date Created'),
			'index'     => 'created_time',
			'type'      => 'datetime',
		));

		$this->addColumn('update_time', array(
			'header'    => Mage::helper('formbuilder')->__('Last Modified'),
			'index'     => 'update_time',
			'type'      => 'datetime',
		));
		
		$this->addColumn('last_result_time', array(
			'header'    => Mage::helper('formbuilder')->__('Last Result'),
			'index'     => 'last_result_time',
			'filter' => false,
			'sortable' => false,
			'type'      => 'datetime',
		));
		
		/* $this->addColumn('action',
			array(
				'header'    =>  Mage::helper('formbuilder')->__('Action'),
				'width'     => '100',
				'filter'    => false,
				'sortable'  => false,
				'renderer'	=> 'NextBits_FormBuilder_Block_Adminhtml_Formbuilder_Renderer_Action',
				'is_system' => true,
		));
 */
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('id');

		
		$this->getMassactionBlock()->addItem('delete', array(
			 'label'=> Mage::helper('formbuilder')->__('Delete'),
			 'url'  => $this->getUrl('*/*/massDelete'),
			 'confirm' => Mage::helper('formbuilder')->__('Are you sure to delete selected elements?')
		));
		
		$statuses = Mage::getModel("formbuilder/formbuilder")->getAvailableStatuses();
		
		$this->getMassactionBlock()->addItem('status', array(
			 'label'=> Mage::helper('formbuilder')->__('Change status'),
			 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			 'additional' => array(
					'visibility' => array(
						 'name' => 'status',
						 'type' => 'select',
						 'class' => 'required-entry',
						 'label' => Mage::helper('formbuilder')->__('Status'),
						 'values' => $statuses
					 )
			 )
		));
	
		return $this;
	}
	
}
?>