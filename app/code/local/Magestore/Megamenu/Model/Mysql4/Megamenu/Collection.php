<?php

class Magestore_Megamenu_Model_Mysql4_Megamenu_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('megamenu/megamenu');
	}
	public function addStoreFilter($store)
	{
		$this->addFieldToFilter('stores', array(array('finset' => $store),array('finset' => '0')));
		return $this;
	}
}