<?php
require_once('Mage/Wishlist/controllers/IndexController.php');

class IWD_QuickView_IndexController extends Mage_Wishlist_IndexController
{
    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     */
    public function cartAction()
    {

        $responseData = array();
        $responseData['error'] = false;
        $responseData['redirect_to_cart'] = false;

        if (!$this->_validateFormKey()) {
            $responseData['error'] = true;
            $responseData['message'] = $this->__("Quick view Invalid Form Key");
//            return $this->_redirect('*/*');
        }
        $itemId = (int)$this->getRequest()->getParam('item');

        /* @var $item Mage_Wishlist_Model_Item */
        $item = Mage::getModel('wishlist/item')->load($itemId);
        if (!$item->getId()) {
            $responseData['error'] = true;
            $responseData['message'] = $this->__("Item is not exist");
//            return $this->_redirect('*/*');
        }
        $wishlist = $this->_getWishlist($item->getWishlistId());
        if (!$wishlist) {
            $responseData['error'] = true;
            $responseData['message'] = $this->__("Error!");
//            return $this->_redirect('*/*');
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->_processLocalizedQty($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        /* @var $session Mage_Wishlist_Model_Session */
        $session = Mage::getSingleton('wishlist/session');
        $cart = Mage::getSingleton('checkout/cart');

        $redirectUrl = Mage::getUrl('*/*');

        try {
            $options = Mage::getModel('wishlist/item_option')->getCollection()
                ->addItemFilter(array($itemId));
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                array('current_config' => $item->getBuyRequest())
            );

            $item->mergeBuyRequest($buyRequest);
            if ($item->addToCart($cart, true)) {
                $cart->save()->getQuote()->collectTotals();
            }

            $wishlist->save();
            Mage::helper('wishlist')->calculate();

            if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
//                $responseData['redirect_to_cart'] = $_SERVER['REQUEST_URI'];
//                $this->_redirectUrl(Mage::helper('checkout/cart')->getCartUrl());
            }
            Mage::helper('wishlist')->calculate();

            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($item->getProductId());
            $productName = Mage::helper('core')->escapeHtml($product->getName());
            $message = $this->__('%s was added to your shopping cart.', $productName);
            Mage::getSingleton('catalog/session')->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                $session->addError($this->__('This product(s) is currently out of stock'));
            } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            } else {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $session->addException($e, $this->__('Cannot add item to shopping cart'));
        }

        Mage::helper('wishlist')->calculate();
        try {
            $_product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($item->getProductId());
            $responseData['error'] = false;

            $_helper = Mage::helper('iwd_quickview/render');

            $responseData['confirmation'] = $_helper->_renderConfirmationCart($_product);
            $responseData['shopping_cart'] = $_helper->_renderShoppingCart(true);
            $responseData['after'] = Mage::getStoreConfig(IWD_QuickView_Helper_Settings::AAC_AFTER_ADD_TO_CART);
            $responseData['header'] = $_helper->_renderHeader();
            $responseData['dropdown'] = $_helper->_renderDropdown();
        } catch (Exception $e) {
            $responseData['error'] = true;
            $response['message'] = $this->__('Cannot remove the item.');
            Mage::logException($e);
        }
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
//        return $this->_redirectUrl($redirectUrl);
    }
}