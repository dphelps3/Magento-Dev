<?php

class Simplesolutions_HardwareCoupons_Block_Adminhtml_Codes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("codesGrid");
				$this->setDefaultSort("hardware_coupons_id");
				$this->setDefaultDir("ASC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("hardwarecoupons/codes")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("hardware_coupons_id", array(
				"header" => Mage::helper("hardwarecoupons")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "hardware_coupons_id",
				));

				$this->addColumn("name", array(
				"header" => Mage::helper("hardwarecoupons")->__("Name"),
				"index" => "name",
				));
					$this->addColumn('start_date', array(
						'header'    => Mage::helper('hardwarecoupons')->__('Start Date'),
						'index'     => 'start_date',
						'type'      => 'datetime',
					));
					$this->addColumn('end_date', array(
						'header'    => Mage::helper('hardwarecoupons')->__('End Date'),
						'index'     => 'end_date',
						'type'      => 'datetime',
					));
				$this->addColumn("hardware_ids", array(
				"header" => Mage::helper("hardwarecoupons")->__("Hardware Line Ids"),
				"index" => "hardware_ids",
				));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}




}
