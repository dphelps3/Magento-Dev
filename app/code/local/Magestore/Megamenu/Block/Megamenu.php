<?php

class Magestore_Megamenu_Block_Megamenu extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
	}
	public function getStoreId(){
		return Mage::app()->getStore()->getId();
	}
	public function getTopMenuCollection(){
		$storeId = $this->getStoreId();
		$collection = Mage::getModel('megamenu/megamenu')->getCollection()
						->addFieldToFilter('megamenu_type',Magestore_Megamenu_Model_Megamenu::TOP_MENU)
						->addFieldToFilter('stores',array(array('finset'=>$storeId),array('finset'=>0)))
						->addFieldToFilter('status',Magestore_Megamenu_Model_Status::STATUS_ENABLED)
						->setOrder('sort_order','ASC');
		return $collection;
	}
	public function getLeftMenuCollection(){
		$storeId = $this->getStoreId();
		$collection = Mage::getModel('megamenu/megamenu')->getCollection()
			->addFieldToFilter('megamenu_type',Magestore_Megamenu_Model_Megamenu::LEFT_MENU)
			->addFieldToFilter('stores',array(array('finset'=>$storeId),array('finset'=>0)))
			->addFieldToFilter('status',Magestore_Megamenu_Model_Status::STATUS_ENABLED)
			->setOrder('sort_order','ASC');
		return $collection;
	}
	public function getContent($item){
		$storeId = $this->getStoreId();
		if(Mage::getStoreConfig('megamenu/general/cache')){
			return  $this->getLayout()->createBlock('cms/block')->setBlockId('mega_item_'.$item->getId().'_'.$storeId)->toHtml();
		}else{
			return  $this->getLayout()->createBlock('megamenu/item')
				->setStore($storeId)
				->setItem($item)
				->setTemplate('ms/megamenu/templates/item.phtml')
				->toHtml();
		}
	}
	public function getSubMenuWidth($items){
		$array=array();
		foreach ($items as $item){
			if($item->getMenuType() != Magestore_Megamenu_Model_Megamenu::ANCHOR_TEXT) $array[] = $item->getSubmenuWidth();
		}
		return json_encode($array);
	}
	public function getEffect(){
		$storeId = $this->getStoreId();
		$array = array(Mage::getStoreConfig('megamenu/general/menu_effect', $storeId),Mage::getStoreConfig('megamenu/mobile_menu/mobile_effect', $storeId));
		return json_encode($array);
	}
	public function getClassMenuType($name){
		$storeId = $this->getStoreId();
		if($name = 'topmenu'){
			$menu_type = Mage::getStoreConfig('megamenu/top_menu/responsive',$storeId);
		}else{
			$menu_type = Mage::getStoreConfig('megamenu/left_menu/responsive',$storeId);
		}

		switch ($menu_type) {
			case Magestore_Megamenu_Model_Megamenu::NO_RESPONSIVE:
				return 'no-responsive';
			default:
				return  '';
		}
	}
}