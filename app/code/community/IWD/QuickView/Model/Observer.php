<?php
class IWD_QuickView_Model_Observer
{
    private $_allowTypes = array(
        'simple',
        'grouped',
        'configurable',
        'virtual',
        'bundle',
        'downloadable',
        'giftcard',
    );

    public function checkRequiredModules($observer)
    {
        $cache = Mage::app()->getCache();

        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            if (!Mage::getConfig()->getModuleConfig('IWD_All')->is('active', 'true')) {
                if ($cache->load("iwd_qv") === false) {
                    $message = 'Important: Please setup IWD_ALL in order to finish <strong>IWD Quick View</strong> installation.<br />
					Please download <a href="http://iwdextensions.com/media/modules/iwd_all.tgz" target="_blank">IWD_ALL</a> and set it up via Magento Connect.<br />
					Please refer link to <a href="https://docs.google.com/document/d/1UjKKMBoJlSLPru6FanetfEBI5GlOdVjQOM_hb3kn8p0/edit" target="_blank">installation guide</a>';

                    Mage::getSingleton('adminhtml/session')->addNotice($message);
                    $cache->save('true', 'iwd_qv', array("iwd_qv"), $lifeTime = 10);
                }
            } else {
                $version = Mage::getConfig()->getModuleConfig('IWD_All')->version;
                if(version_compare($version, "2.0.0", "<")){
                    $message = 'Important: Please update IWD_ALL extension because some features of <strong>IWD Quick View</strong> can be not available.<br />
					Please download <a href="http://iwdextensions.com/media/modules/iwd_all.tgz" target="_blank">IWD_ALL</a> and set it up via Magento Connect.<br />
					Please refer link to <a href="https://docs.google.com/document/d/1UjKKMBoJlSLPru6FanetfEBI5GlOdVjQOM_hb3kn8p0/edit" target="_blank">installation guide</a>';

                    Mage::getSingleton('adminhtml/session')->addNotice($message);
                    $cache->save('true', 'iwd_qv', array("iwd_qv"), $lifeTime = 10);
                }
            }
        }
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /** EVENT WHEN PRODUCT HAS BEEN ADDED TO SHOPPING CART **/
    public function checkoutCartAddProductComplete($observer)
    {
        if (!Mage::helper('iwd_quickview/settings')->isExtensionEnable()) {
            return;
        }

        $request = $observer->getData('request');
        $response = $observer->getData('response');
        $requestProduct = $request->getParam('product');
        $responseAjax = array();

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $wishListIdByCustomer = Mage::getSingleton('wishlist/wishlist')->loadByCustomer($customerId)->getId();
            $productId = (int) $requestProduct;

            $wishListItemId = Mage::getModel('wishlist/item')->getCollection()
                ->addFieldToFilter('wishlist_id',$wishListIdByCustomer)
                ->addFieldToFilter('product_id',$productId);
            foreach ($wishListItemId as $item) {
                $responseAjax['itemId'] = $item->getId();
                $item->delete();
            }
        }


        if($request->getParam('iwd_qv_mode') == 'qv')
        {
            $after = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::QV_AFTER_ADD_TO_CART);
            $showDropdown = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::QV_SHOW_DROPDOWN);
        }
        else
        {
            $after = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::AAC_AFTER_ADD_TO_CART);
            $showDropdown = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::AAC_SHOW_DROPDOWN);
        }

        $is_iwd_b2b_extension = $request->getParam('b2b', false);
        if ($is_iwd_b2b_extension) {
            return;
        }

        $is_iwd_qv_extension = $request->getParam('iwd_qv', false);
        if ($is_iwd_qv_extension) {


            try {
                $_helper = Mage::helper('iwd_quickview/render');
                $_product = $observer->getData('product');
                $is_shopping_cart = $request->getParam('cart', false);

                $responseAjax['after'] = $after;
                if($after != IWD_QuickView_Model_System_Config_Addtocartafter::SHOP_CART){
                    $responseAjax['confirmation'] = $_helper->_renderConfirmationCart($_product);
                    $responseAjax['shopping_cart'] = $_helper->_renderShoppingCart($is_shopping_cart);
                    $responseAjax['error'] = false;
                    $responseAjax['header'] = $_helper->_renderHeader();
                    $responseAjax['dropdown'] = $_helper->_renderDropdown();
                    $responseAjax['show_dropdown'] = (bool)$showDropdown;
                    $responseAjax['item_id'] = "tetetetettetete";
                }
            } catch (Exception $e) {
                $responseAjax['error'] = true;
                $responseAjax['error'] = $e->getMessage();
                Mage::logException($e);
            }
            $this->_getSession()->setNoCartRedirect(true);

            $response->setHeader('Content-type', 'application/json', true);
            $response->setBody(Mage::helper('core')->jsonEncode($responseAjax));
        }
    }

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /** PRODUCT VIEW **/
    public function productView($observer)
    {
        if (!Mage::helper('iwd_quickview/settings')->isExtensionEnable()) {
            return;
        }

        $frontendController = $observer->getData('controller_action');
        $request = $frontendController->getRequest();
        $response = $frontendController->getResponse();

        $is_iwd_b2b_extension = $request->getParam('b2b', false);
        if ($is_iwd_b2b_extension) {
            return;
        }

        $is_iwd_qv_extension = $request->getParam('iwd_qv', false);
        $is_ajax_request = $request->isAjax();
        if ($is_iwd_qv_extension && $is_ajax_request) {
            $product = Mage::registry('current_product');
            $typeProduct = $product->getTypeId();
            //show only supported product types
            if (in_array($typeProduct, $this->_allowTypes)) {
                $_helper = Mage::helper('iwd_quickview/render');
                $responseData = array();
                try {
                    $responseData['content'] = $_helper->_renderProductOptions($typeProduct);
                    $responseData['error'] = false;
                } catch (Exception $e) {
                    $responseData['error'] = true;
                    $responseData['error'] = $e->getMessage();
                    Mage::logException($e);
                }
            } else {
                //TODO if type id not supported return response with redirect url
                $responseData['error'] = true;
                $responseData['redirect'] = $product->getProductUrl();
            }

            $response->clearAllHeaders();
            $response->setHeader('Content-type', 'application/json', true);
            $response->setBody(Mage::helper('core')->jsonEncode($responseData));
            return;
        }
    }

    /** CHANGE RESPONSE IF PRODUCT UPDATE **/
    public function productupdate($observer) {
        if (!Mage::helper('iwd_quickview/settings')->isExtensionEnable()) {
            return;
        }

        /* @var $controller Mage_Core_Controller_Varien_Action */
        $controller = $observer->getEvent()->getData('controller_action');
        $id = (int)$controller->getRequest()->getParam('id');
        $cart = Mage::getSingleton('checkout/cart');
        /**
         * catch server validation failed - then redirect back
         */
        $quoteErrors = $cart->getQuote()->getErrors();
        $sessionErrors = Mage::getSingleton('checkout/session')->getMessages()->getItems('error');
        if( !empty($quoteErrors) || !empty($sessionErrors)) {
//                $controller->_goBack();
            $controller->getResponse()->setRedirect(Mage::getUrl('checkout/cart/configure', array(
                'id' => $id,
                'iwd_qv' => 'true',
                'cart' => 'true',
                'edit' => 'true',
                'iwd_qv_mode' => 'aac',
            )) , 302);
            return false;
        }
        $result = Mage::helper('core')->jsonDecode(
            $controller->getResponse()->getBody('default'),
            Zend_Json::TYPE_ARRAY
        );


        $this->_initLayoutMessages('checkout/session');
        $responseData  = array();
        $_helper = Mage::helper('iwd_quickview/render');
        $responseData['error'] = false;
        $responseData['after'] = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::AAC_AFTER_ADD_TO_CART);
        $responseData['confirmation'] = $_helper->renderConfirmationCart(false, true);
        $responseData['header']	= $_helper->_renderHeader();
        $responseData['dropdown'] = $_helper->_renderDropdown();
        $isShoppingCart = $controller->getRequest()->getParam('cart', false);
        $responseData['shopping_cart'] = $_helper->_renderShoppingCart($isShoppingCart);

        $controller->getResponse()->clearHeader('Location');
        $controller->getResponse()->setHttpResponseCode(200);
        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));

        return false;
    }

    protected function _initLayoutMessages($messagesStorage){
        if (!is_array($messagesStorage)) {
            $messagesStorage = array($messagesStorage);
        }
        foreach ($messagesStorage as $storageName) {
            $storage = Mage::getSingleton($storageName);
            if ($storage) {
                $block = Mage::app()->getLayout()->getMessagesBlock();
                $block->addMessages($storage->getMessages(true));
                $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
                $block->addStorageType($storageName);
            }
            else {
                Mage::throwException(
                    Mage::helper('core')->__('Invalid messages storage "%s" for layout messages initialization', (string) $storageName)
                );
            }
        }
        return $this;
    }

    /** IF REQUEST EDIT PRODUCT PAGE **/
    public function productConfigure($observer){

//        if (!Mage::getStoreConfig ( 'aac/global/status' )){
//            return;
//        }

        $frontendController	=	$observer->getData('controller_action');
        $request =	$frontendController->getRequest();
        $response =	$frontendController->getResponse();

        $isAjaxModule = $request->getParam('iwd_qv', false);
        $isAjaxRequest = $request->isAjax();
        if ($isAjaxModule && $isAjaxRequest) {
            $product = Mage::registry ( 'current_product' );
            $typeProduct = $product->getTypeId();
            //show only supported product types
            if (in_array($typeProduct, $this->_allowTypes)){
                $_helper = Mage::helper('iwd_quickview/render');
                $responseData = array();
                try{

                    $responseData['content'] =  $_helper->_renderProductOptions($typeProduct, true);
                    $responseData['error'] = false;
                }catch (Exception $e){
                    $responseData['error'] = true;
                    $responseData['error'] = $e->getMessage();
                    Mage::logException($e);
                }
            }else{
                //TODO if type id not supported return response with redirect url
                $responseData['error'] = true;
                $responseData['redirect'] = $product->getProductUrl();;

            }
            $response->clearAllHeaders();
            $response->setHeader('Content-type','application/json', true);
            $response->setBody(Mage::helper('core')->jsonEncode($responseData));
            return;
        }

    }
}