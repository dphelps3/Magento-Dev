<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 8/27/14
 * Time: 9:16 AM
 */
class Hawksearch_Proxy_Block_Html extends Mage_Core_Block_Template {
	/** @var Hawksearch_Proxy_Helper_Data $helper */
	private $helper;

	function _construct() {
		$this->helper = Mage::helper('hawksearch_proxy');
		$this->helper->setUri($this->getRequest()->getParams());
		$this->helper->setClientIp($this->getRequest()->getClientIp());
		$this->helper->setClientUa(Mage::helper('core/http')->getHttpUserAgent());
		$this->helper->setIsHawkManaged(true);
		return parent::_construct();
	}
	function getFacets() {
		return $this->helper->getResultData()->Data->Facets;
	}
	function getItemList() {
		$products = $this->getLayout()->createBlock('catalog/product_list', 'product_list', array('template' => 'catalog/product/list.phtml'));
//        $na = $this->getLayout()->createBlock('core/text_list', 'product_list.name.after', array('as' => 'name.after'));
//        $products->setChild('name.after', $na);
//        $pid = $this->getLayout()->createBlock('core/template', 'product_id', array('output' => 'toHtml', 'template' => 'path/to/template.phtml'));
//        $na->insert($pid);

		$coll = $this->helper->getProductCollection();
        Mage::register('hawkRegistry', true, true);
		$products->setCollection($coll);

		return $products->toHtml();
	}
	function getTopPager() {
		return $this->helper->getResultData()->Data->TopPager;
	}
	function getBottomPager() {
		return $this->helper->getResultData()->Data->BottomPager;
	}
	function getMetaRobots() {
		return $this->helper->getResultData()->MetaRobots;
	}
	function getHeaderTitle() {
		return $this->helper->getResultData()->HeaderTitle;
	}
	function getMetaDescription() {
		return $this->helper->getResultData()->MetaDescription;
	}
	function getMetaKeywords() {
		return $this->helper->getResultData()->MetaKeywords;
	}
	function getRelCanonical() {
		return $this->helper->getResultData()->RelCanonical;
	}
	function getTopText() {
		return $this->helper->getResultData()->Data->TopText;
	}
	function getRelated() {
		return $this->helper->getResultData()->Data->Related;
	}
	function getBreadCrumb() {
		return $this->helper->getResultData()->Data->BreadCrumb;
	}
	function getTitle() {
		return $this->helper->getResultData()->Data->Title;
	}
}