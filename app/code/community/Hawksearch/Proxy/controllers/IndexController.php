<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 7/3/14
 * Time: 9:16 AM
 */
class Hawksearch_Proxy_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {

		$this->loadLayout();
		$layout = $this->getLayout();

		$root = $layout->getBlock('root');
		$params = $this->getRequest()->getParams();
		$root->setJsonpCallback($params['callback']);

		$root->getChild('resulthtml')->setPagers(false);
		/** @var Hawksearch_Proxy_Block_Html $html */
		$html = $root->getChildHtml('resulthtml');

		$obj = array(
            'Success' => 'true',
            'html' => $html,
            'location' => ''
        );
        $obj = array_merge($obj, Mage::helper('hawksearch_proxy')->getProductHrefList());
		$root->setJsonpObject($obj);

		$this->renderLayout();
	}

}