<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 9/3/14
 * Time: 2:33 PM
 */
class Hawksearch_Proxy_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_List
{

    private $topseen = false;
    private $pagers = true;

    public function getHawksearchTracking($idx, $pid)
    {
        $tid = $this->getHawkTrackingId();
        if ($tid != '') {
            return sprintf("HawkSearch.link(this,'%s','%d','%s',0)", $tid, $idx, $pid);
        }

        return '';
    }

    public function setPagers($bool)
    {
        $this->pagers = $bool;
    }

    public function getHawkTrackingId()
    {
        $h = Mage::helper('hawksearch_proxy');
        if (!empty($h)) {
            $data = $h->getResultData();
            if ($data) {
                return $data->TrackingId;
            }
        }
        return '';
    }

    public function getToolbarHtml()
    {
        if (!Mage::registry('hawkRegistry')) {
            return parent::getToolbarHtml();
        }
        $helper = Mage::helper('hawksearch_proxy');
        if (!$helper->getIsHawkManaged()) {
            $helper->log('page not managed, returning core pager');
            return parent::getToolbarHtml();
        }
        if ($this->pagers) {

            if ($this->topseen) {
                return '<div id="hawkbottompager">' . $helper->getResultData()->Data->BottomPager . '</div>';
            }
            $this->topseen = true;
            return '<div id="hawktoppager">' . $helper->getResultData()->Data->TopPager . '</div>';
        } else {
            return '';
        }
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        if (!$product->getTypeInstance(true)->hasRequiredOptions($product)) {
            return $this->getAddUrl($product, $additional);
        }
        $additional = array_merge(
            $additional,
            array(Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey())
        );
        if (!isset($additional['_escape'])) {
            $additional['_escape'] = true;
        }
        if (!isset($additional['_query'])) {
            $additional['_query'] = array();
        }
        $additional['_query']['options'] = 'cart';
        return $this->getProductUrl($product, $additional);
    }

    private function getAddUrl($product, $additional = array())
    {
        $routeParams = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core')
                ->urlEncode($this->getCurrentUrl()),
            'product' => $product->getEntityId(),
            Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey()
        );

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_store_to_url'] = true;
        }

        $request = Mage::app()->getRequest();

        if ($request->getRouteName() == 'checkout'
            && $request->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return Mage::getUrl('checkout/cart/add', $routeParams);
    }

    private function getCurrentUrl()
    {
        $request = Mage::app()->getRequest();

        $port = $request->getServer('SERVER_PORT');
        if ($port) {
            $defaultPorts = array(
                Mage_Core_Controller_Request_Http::DEFAULT_HTTP_PORT,
                Mage_Core_Controller_Request_Http::DEFAULT_HTTPS_PORT
            );
            $port = (in_array($port, $defaultPorts)) ? '' : ':' . $port;
        }

        $lpurl = $request->getParam('lpurl');

        if ((empty($lpurl))) {
            $lpurl = $request->getServer('REQUEST_URI');
        }

        $url = $request->getScheme() . '://' . $request->getHttpHost() . $port . $lpurl;
        return $this->escapeUrl($url);
    }
}

