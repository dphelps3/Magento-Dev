<?php

class Simplesolutions_CabinetCoupons_Block_Adminhtml_Codes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("codesGrid");
				$this->setDefaultSort("cabinet_coupons_id");
				$this->setDefaultDir("ASC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("cabinetcoupons/codes")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("cabinet_coupons_id", array(
				"header" => Mage::helper("cabinetcoupons")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "cabinet_coupons_id",
				));
                
				$this->addColumn("name", array(
				"header" => Mage::helper("cabinetcoupons")->__("Name"),
				"index" => "name",
				));
					$this->addColumn('start_date', array(
						'header'    => Mage::helper('cabinetcoupons')->__('Start Date'),
						'index'     => 'start_date',
						'type'      => 'datetime',
					));
					$this->addColumn('end_date', array(
						'header'    => Mage::helper('cabinetcoupons')->__('End Date'),
						'index'     => 'end_date',
						'type'      => 'datetime',
					));
				$this->addColumn("cabinet_ids", array(
				"header" => Mage::helper("cabinetcoupons")->__("Cabinet Line Ids"),
				"index" => "cabinet_ids",
				));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		

}