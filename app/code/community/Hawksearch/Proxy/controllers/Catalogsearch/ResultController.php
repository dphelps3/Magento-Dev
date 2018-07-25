<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 11/11/14
 * Time: 2:28 PM
 */
require_once(Mage::getModuleDir('controllers', 'Mage_CatalogSearch') . DS . 'ResultController.php');

class Hawksearch_Proxy_Catalogsearch_ResultController extends Mage_CatalogSearch_ResultController
{
	public function indexAction(){
		if(Mage::getStoreConfigFlag('hawksearch_proxy/general/enabled')
			&& Mage::getStoreConfigFlag('hawksearch_proxy/proxy/manage_search')) {
			/** @var Hawksearch_Proxy_Helper_Data $helper */
			$helper = Mage::helper('hawksearch_proxy');

			Mage::register('hawkRegistry', true, true);

			$args = array_merge($this->getRequest()->getParams(), array('output' => 'custom', 'hawkitemlist'=>'json'));
			$helper->setUri($args);
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

			/** @var Mage_Core_Block_Template $hawkfacets */
			$hawkfacets = $layout->createBlock('core/template', 'hawkfacets', array('template' => 'hawksearch/proxy/left/facets.phtml' ));

			/** @var Mage_Core_Block_Text_List $left */
			$left = $layout->getBlock('left');
			$content = $layout->getBlock('content');

			/** @var Mage_Catalog_Block_Product_List $hawkitemlist */
			$hawkitemlist = $layout->createBlock('hawksearch_proxy/html', 'hawkitemlist', array('template' => 'hawksearch/proxy/content/hawkitems.phtml'));
			$products = $layout->createBlock('catalog/product_list', 'search_result_list', array('template' => 'catalog/product/list.phtml'));
			$coll = $helper->getProductCollection();
			$products->setCollection($coll);
			//$products->setHawkHelper($helper);
			$hawkitemlist->setChild('search_result_list', $products);

			$content->insert($hawkitemlist);

			/** @var Mage_Core_Block_Text $facets */
			$facets = $layout->createBlock('core/text', 'facets');
			$facets->addText($helper->getFacets());
			$hawkfacets->setChild('facets', $facets);

			if(!empty($left)) {
				$left->insert($hawkfacets);
			}else{
				$content->insert($hawkfacets);
			}

			$this->_initLayoutMessages('catalog/session');
			$this->_initLayoutMessages('checkout/session');

			$this->renderLayout();
		} else {
			Mage::helper('hawksearch_proxy')->log('hawk not managing search');

			parent::indexAction();
		}
	}
} 