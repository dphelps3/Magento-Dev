<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 12/18/14
 * Time: 1:57 PM
 */
class Hawksearch_Proxy_Catalog_Block_Category_View extends Mage_Catalog_Block_Category_View {

	protected function _prepareLayout() {
		if (Mage::getStoreConfigFlag('hawksearch_proxy/general/enabled')
			&& Mage::getStoreConfigFlag('hawksearch_proxy/proxy/manage_categories')
		) {
			/** @var Hawksearch_Proxy_Helper_Data $helper */
			$helper = Mage::helper('hawksearch_proxy');

			$alias = $this->getRequest()->getAlias('rewrite_request_path');
			if ($helper->getIsHawkManaged($alias)) {
				$helper->setUri(array('lpurl' => $alias, 'output' => 'custom', 'hawkitemlist' => 'json'));
				$helper->setClientIp($this->getRequest()->getClientIp());
				$helper->setClientUa(Mage::helper('core/http')->getHttpUserAgent());

                if(!Mage::registry('hawkRegistry')){
                    Mage::register('hawkRegistry', true, true);

                    $layout = $this->getLayout();
                    /** @var Mage_Core_Block_Template $hawkfacets */
                    $hawkfacets = $layout->createBlock('core/template', 'hawkfacets', array('template' => 'hawksearch/proxy/left/facets.phtml'));

                    /** @var Mage_Core_Block_Text_List $left */
                    $left = $layout->getBlock('left');

                    /** @var Mage_Core_Block_Text $facets */
                    $facets = $layout->createBlock('core/text', 'facets');
                    $facets->addText($helper->getFacets());
                    $hawkfacets->setChild('facets', $facets);
                    $left->insert($hawkfacets);

                    $category = Mage::registry('current_category');
                    $helper->decorateCategory($category);
                }
			}
		}

        return parent::_prepareLayout();
	}

	public function getProductListHtml() {
		$helper = Mage::helper('hawksearch_proxy');
		if (Mage::getStoreConfigFlag('hawksearch_proxy/proxy/manage_categories') && Mage::getStoreConfigFlag('hawksearch_proxy/general/enabled') && $helper->getIsHawkManaged()) {
			$banner = Mage::getSingleton('hawksearch_proxy/banner');
			$coll = $helper->getProductCollection();

			$products = $this->getLayout()->getBlock('product_list');
			$products->setCollection($coll);
			$products->setHawkHelper($helper);
            $block = $this->getLayout()->createBlock('hawksearch_proxy/html', 'hawksearch.proxy.items.html', array('template' => 'hawksearch/proxy/content/hawkitems.phtml'));
			//return '<div id="hawkbannertop" class="bannerTop">' . $banner->getBannerTop() . '</div><div id="hawkitemlist">' . $this->getChildHtml('product_list') . '</div><div id="hawkbannerbottom" class="bannerBottom">' . $banner->getBannerBottom() . '</div>';
            return $block->toHtml();
		} else {
			return parent::getProductListHtml();
		}

	}
} 