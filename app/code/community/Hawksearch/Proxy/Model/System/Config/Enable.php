<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 8/5/14
 * Time: 11:01 AM
 */

class Hawksearch_Proxy_Model_System_Config_Enable extends Mage_Core_Model_Config_Data {
	public function save() {
		$enable = $this->getValue();
		$params = Mage::app()->getRequest()->getParams();

		$engine_name = $params['groups']['proxy']['fields']['engine_name']['value'];
		if($enable && $engine_name == '') {
			Mage::throwException("Cannot enable Hawksearch Proxy module without an Engine Name");
		}

		$searchEnabled = Mage::getStoreConfig('hawksearch_search/general/enabled', $this->getScopeId());
		if($enable && $searchEnabled){
			Mage::getSingleton('core/session')->addNotice('Hawksearch JavaScript module disabled! You will need to enable the Javascript module manually if you disable this module.');

			/** @var Mage_Core_Model_Resource_Config $config */
			$config = Mage::getConfig()->getResourceModel();
			$config->saveConfig('hawksearch_search/general/enabled', '0', $this->getScope(), $this->getScopeId());
		}
		return parent::save();
	}

} 