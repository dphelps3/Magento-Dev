<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 8/11/15
 * Time: 4:59 PM
 */

class Hawksearch_Proxy_Custom_ResultController extends Mage_Core_Controller_Front_Action {

	public function viewAction() {
		/** @var Hawksearch_Proxy_Helper_Data $helper */
		$helper = Mage::helper('hawksearch_proxy');
		if(Mage::getStoreConfigFlag('hawksearch_proxy/general/enabled')
			&& $helper->getIsHawkManaged(trim($this->getRequest()->getPathInfo(), '/'))) {

			$category = Mage::getModel('catalog/category');
			Mage::register('current_category', $category);

			$helper->setUri(array('lpurl' => $this->getRequest()->getPathInfo(), 'output' => 'custom', 'hawkitemlist' => 'json'));
			$helper->setClientIp($this->getRequest()->getClientIp());
			$helper->setClientUa(Mage::helper('core/http')->getHttpUserAgent());

			if($helper->getLocation() != ""){
				$helper->log(sprintf('Redirecting to location: %s', $helper->getLocation()));
				return $this->_redirectUrl($helper->getLocation());
			}

			$helper->setIsHawkManaged(true);

            $this->loadLayout();
			/** @var Mage_Core_Model_Layout $layout */
			$layout = $this->getLayout();
            $template = Mage::getStoreConfig('hawksearch_proxy/proxy/custom_landing_page_layout');
            $this->getLayout()->helper('page/layout')->applyTemplate($template);
			$this->_initLayoutMessages('catalog/session');
			$this->_initLayoutMessages('checkout/session');

			$this->renderLayout();
		}
	}
}
