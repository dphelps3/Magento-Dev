<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 7/18/15
 * Time: 7:50 PM
 */



class Hawksearch_Proxy_Model_System_Config_Route extends Mage_Core_Model_Config_Data {
    protected function _beforeSave()
    {
		$val = $this->getValue();
        $helper = Mage::helper('hawksearch_proxy');
		$helper->setStore($this->getStoreCode());
		$helper->log('before save of search route, recievied value: ' . $val);
		if(!$helper->isValidSearchRoute($val)){
			Mage::throwException("invalid search route");
		}
        return parent::_beforeSave();
    }
}
