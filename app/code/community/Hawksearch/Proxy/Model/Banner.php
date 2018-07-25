<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 2/9/15
 * Time: 9:12 AM
 */

class Hawksearch_Proxy_Model_Banner extends Varien_Object {

	protected function _construct() {
		$helper = Mage::helper('hawksearch_proxy');
		foreach($helper->getResultData()->Data->Merchandising->Items as $banner) {
			$this->setData($this->_underscore($banner->Zone), $banner->Html);
		}
        foreach($helper->getResultData()->Data->FeaturedItems->Items as $banner) {
            $this->setData($this->_underscore($banner->Zone), $banner->Html);
        }
	}
}