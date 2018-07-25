<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 7/22/15
 * Time: 10:50 AM
 */
class Hawksearch_Proxy_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard {

	public function match(Zend_Controller_Request_Http $request) {
		$stem = Mage::getStoreConfig('hawksearch_proxy/proxy/custom_search_route');
		$identifier = trim($request->getPathInfo(), '/');
		$path = explode('/', $identifier);
		if (count($path) == 2 && $path[0] == $stem) {
			/*
			 * so if we are configured to use the category url
			 * siffix, strip it from the end, then replace '-'
			 * with '+'
			 */
			$terms = str_replace('-', ' ', $path[1]);
			if(Mage::getStoreConfigFlag('hawksearch_proxy/proxy/custom_search_use_suffix')){
				$suffix = $helper = Mage::helper('catalog/category')->getCategoryUrlSuffix();
				$ss = substr($terms, strlen($terms) - strlen($suffix));
				if($ss == $suffix) {
					$terms = substr($terms, 0, strlen($terms) - strlen($suffix) - 1);
				} else{
					return false;
				}
			}
			$request->setActionName('index')
				->setParam('q', $terms)
				->setDispatched(true);

			$controllerClassName = $this->_validateControllerClassName('Hawksearch_Proxy', 'catalogsearch_result');
			$controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $this->getFront()->getResponse());
			$controllerInstance->dispatch($request->getActionName());
			return true;
		} elseif(Mage::helper('hawksearch_proxy')->getIsHawkManaged(trim($request->getPathInfo(), '/'))) {
			$request->setRouteName('hawksearch_proxy')->setControllerName('custom')->setActionName('view')
//				->setParam('q', $terms)
				->setDispatched(true);

			//$routers = Mage::getConfig()->getNode('frontend/routers');
			/*
			 * ok, looks like i need a new controller to handle these custom landing pages.
			 * start by adding controllers/Custom/ResultController.php and an indexAction
			 * then we can do something like we have in the catalogsearch_resultController
			 * indexAction, but if not configured, or some other issue, call
			 * $this->_forward('noRoute') and let the system cough up a 404
			 */

			$controllerClassName = $this->_validateControllerClassName('Hawksearch_Proxy', 'custom_result');
			$controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $this->getFront()->getResponse());
            $controllerInstance->dispatch($request->getActionName());
			return true;
		}
		return false;

	}

	public function initCustomSearchRouter(Varien_Event_Observer $observer) {
		if (Mage::getStoreConfigFlag('hawksearch_proxy/general/enabled')) {
			/* @var $front Mage_Core_Controller_Varien_Front */
			$front = $observer->getEvent()->getFront();

			$front->addRouter('hawksearch', $this);
		}
	}
}