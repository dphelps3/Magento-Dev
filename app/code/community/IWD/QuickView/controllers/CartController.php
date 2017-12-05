<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class IWD_QuickView_CartController extends Mage_Checkout_CartController
{
//    protected function _getCart()
//    {
//        return Mage::getSingleton('checkout/cart');
//    }

//    protected function _getSession()
//    {
//        return Mage::getSingleton('checkout/session');
//    }

//    protected function _getQuote()
//    {
//        return $this->_getCart()->getQuote();
//    }

//    protected function _initProduct()
//    {
//        $productId = (int)$this->getRequest()->getParam('product');
//        if ($productId) {
//            $product = Mage::getModel('catalog/product')
//                ->setStoreId(Mage::app()->getStore()->getId())
//                ->load($productId);
//            if ($product->getId()) {
//                return $product;
//            }
//        }
//        return false;
//    }

    public function getUrlAction()
    {
        $params = $this->getRequest()->getParams();
        $product = $this->_initProduct();

        $responseData = array();
        if ($product) {
            $responseData['error'] = false;
            $responseData['url'] = $product->getProductUrl() . '?options=cart';
        } else {
            $responseData['error'] = true;
            $responseData['message'] = $this->__('Cannot add the item to shopping cart.');
        }

        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
    }

    /**
     * REMOVE ITEM FROM CHECKOUT CART
     */
    public function removeAction()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->norouteAction();
            return;
        }
        $id = (int)$this->getRequest()->getParam('id');
        $responseData = array();
        if ($id) {
            try {

                $this->_getCart()->removeItem($id)->save();
                $responseData['error'] = false;

                $_helper = Mage::helper('iwd_quickview/render');

                $responseData['shopping_cart'] = $_helper->_renderShoppingCart(true);
                $responseData['header'] = $_helper->_renderHeader();
                $responseData['dropdown'] = $_helper->_renderDropdown();
            } catch (Exception $e) {
                $responseData['error'] = true;
                $response['message'] = $this->__('Cannot remove the item.');
                Mage::logException($e);
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
    }

    public function addAction()
    {
        // use standard logic
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
            $url = $this->_getSession()->getRedirectUrl(true);

            // IF THIS IS AJAX REQUEST AND FROM IWD QUICK VIEW POPUP - LETS STAY IN POPUP
            if($this->getRequest()->isAjax() && $this->getRequest()->getParam('iwd_qv')) {
                $url .= '?iwd_qv=1&ajax=1';
                if($this->getRequest()->getParam('iwd_qv_mode', null) !== null)
                    $url .= '&iwd_qv_mode=' . $this->getRequest()->getParam('iwd_qv_mode');
            }
            // END - IF THIS IS AJAX REQUEST AND FROM IWD QUICK VIEW POPUP - LETS STAY IN POPUP

            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
}