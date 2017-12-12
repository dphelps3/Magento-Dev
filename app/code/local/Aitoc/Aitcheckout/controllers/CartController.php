<?php

class Aitoc_Aitcheckout_CartController extends Aitoc_Aitcheckout_Controller_Action
{

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();

        return $this;
    }

    protected function _expireAjax()
    {
        if (!$this->_getQuote()->hasItems()
            //|| $this->_getOnepage()->getQuote()->getHasError()
            //|| $this->_getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();

            return true;
        }

        return false;
    }

    /**
     * Initialize giftcart
     */
    public function giftcardPostAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $data        = $this->getRequest()->getPost();
        $currentStep = $data['step'];
        if (isset($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($code)
                    ->addToCart();

                $this->_getQuote()->collectTotals();
                $this->_getQuote()->save();
                $result = array(
                    'error'   => 0,
                    'message' => Mage::helper('aitcheckout')->__(
                        'Gift Card "%s" was added.',
                        Mage::helper('core')->htmlEscape($code)
                    )
                );
                $this->getResponse()
                    ->setBody(
                        Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result))
                    );
            } catch (Mage_Core_Exception $e) {
                $result = array('error' => -1, 'message' => Mage::helper('aitcheckout')->__('Gift Card cannot added.'));

                $this->getResponse()
                    ->setBody(
                        Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result))
                    );
            }
        }
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data        = $this->getRequest()->getPost();
            $currentStep = $data['step'];
            if (!$this->_getQuote()->getItemsCount()) {
                $this->getResponse()
                    ->setBody(
                        Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
                    );

                return;
            }
            $couponCode = (string)$this->getRequest()->getPost('coupon_code', '');
            if ($data['remove_coupon'] == 1) {
                $couponCode = '';
            }
            $oldCouponCode = $this->_getQuote()->getCouponCode();
            if (!strlen($couponCode) && !strlen($oldCouponCode)) {
                $this->getResponse()
                    ->setBody(
                        Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
                    );

                return;
            }


            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($couponCode) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $result = array(
                        'error'   => 0,
                        'message' => Mage::helper('aitcheckout')->__(
                            'Coupon code "%s" was applied.',
                            Mage::helper('core')->htmlEscape($couponCode)
                        )
                    );
                } else {
                    $result = array(
                        'error'   => -1,
                        'message' => Mage::helper('checkout')->__(
                            'Coupon code "%s" is not valid.',
                            Mage::helper('core')->htmlEscape($couponCode)
                        )
                    );
                }
            } else {
                $result = array(
                    'error'   => 1,
                    'message' => Mage::helper('aitcheckout')->__('Coupon code was canceled.')
                );
            }
            $this->getResponse()
                ->setBody(
                    Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result))
                );
        }

    }


    /**
     * Initialize coupon
     */
    public function eegiftwrappingPostAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            //  print_r($data); exit;
            $currentStep = $data['step'];
            Mage::dispatchEvent(
                'checkout_controller_onepage_save_shipping_method',
                array(
                    'request' => $this->getRequest(),
                    'quote'   => $this->_getQuote()
                )
            );

            $result = array();
            $this->getResponse()
                ->setBody(
                    Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep, $result))
                );
        }

    }

    /**
     * Update shoping cart data action
     */
    public function updateItemOptionsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            try {
                $itemId      = (int)$this->getRequest()->getParam('id');
                $productId   = $this->_getQuote()->getItemById($itemId)->getProduct()->getId();
                $stockItem   = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                $increment   = $stockItem->getQtyIncrements() ? $stockItem->getQtyIncrements() : 1;
                $sign        = $this->getRequest()->getParam('sign');
                $data        = $this->getRequest()->getPost();
                $cartData    = $data['cart'];
                $currentStep = $data['step'];
                if (is_array($cartData)) {
                    $filter                   = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $cartData[$itemId]['qty'] = $filter->filter($cartData[$itemId]['qty'] + $sign * $increment);

                    $cart = $this->_getCart();
                    if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                        $cart->getQuote()->setCustomerId(null);
                    }
                    $this->_getQuote()->unsetData('messages');
                    if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.9.1')) {
                        $cartData = $cart->suggestItemsQty($cartData);
                    }
                    $oldQty = $this->_getQuote()->getItemById($itemId)->getQty();

                    $cart->updateItems($cartData);
                    if (Mage::helper('aitcheckout')->isErrorQuoteItemQty()) {
                        //restoring old qty in cart
                        $cartData[$itemId]['qty'] = $oldQty;
                        $cart->updateItems($cartData);
                        //don't allow to save quote and it's items, they can't be changed now
                        $session = $this->_getCart()->getCheckoutSession();
                        if (method_exists($this->_getQuote(), 'preventSaving')) {
                            $this->_getQuote()->preventSaving();
                        }
                        $message = Mage::helper('aitcheckout')->getLastErrorMessage();
                        //for lower magento version error message can be duplicated, so we update it with our one
                        if (!$message || version_compare(Mage::getVersion(), '1.10.0.0', '<')) {
                            $message = Mage::helper('aitcheckout')->__('Cannot update the item.');
                        }
                        Mage::throwException($message);
                    }
                    $cart->save();
                }
                if ($this->_expireAjax()) {
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                if ($this->_getCart()->getCheckoutSession()->getUseNotice(true)) {
                    $this->_getCart()->getCheckoutSession()->addNotice($e->getMessage());
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->_getCart()->getCheckoutSession()->addError($message);
                    }
                }
            } catch (Exception $e) {
                $this->_getCart()->getCheckoutSession()->addException(
                    $e,
                    Mage::helper('aitcheckout')->__('Cannot update the item.')
                );
                Mage::logException($e);
            }
            $this->getResponse()
                ->setBody(
                    Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
                );
        }
    }

    public function updatePostAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            try {
                $currentStep = $this->getRequest()->getPost('step');
                $cartData    = $this->getRequest()->getParam('cart');
                $oldData     = $cartData;
                if (is_array($cartData)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    foreach ($cartData as $index => $data) {
                        if (isset($data['qty'])) {
                            $cartData[$index]['qty'] = $filter->filter($data['qty']);
                            $oldData[$index]['qty']  = $this->_getQuote()->getItemById($index)->getQty();
                        }
                    }
                    $cart = $this->_getCart();
                    if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                        $this->_getQuote()->setCustomerId(null);
                    }
                    $this->_getQuote()->unsetData('messages');
                    if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.9.1')) {
                        $cartData = $cart->suggestItemsQty($cartData);
                    }

                    $cart->updateItems($cartData);
                    if (Mage::helper('aitcheckout')->isErrorQuoteItemQty()) {
                        //restoring old qty in cart
                        $cart->updateItems($oldData);
                        //don't allow to save quote and it's items, they can't be changed now
                        if (method_exists($this->_getQuote(), 'preventSaving')) {
                            $this->_getQuote()->preventSaving();
                        }
                        $message = Mage::helper('aitcheckout')->getLastErrorMessage();
                        //for lower magento version error message can be duplicated, so we update it with our one
                        if (!$message || version_compare(Mage::getVersion(), '1.10.0.0', '<')) {
                            $message = Mage::helper('aitcheckout')->__('Cannot update the item.');
                        }
                        Mage::throwException($message);
                    }
                    $cart->save();
                }
                if ($this->_expireAjax()) {
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getCart()->getCheckoutSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getCart()->getCheckoutSession()->addException(
                    $e,
                    Mage::helper('aitcheckout')->__('Cannot update shopping cart.')
                );
                Mage::logException($e);
            }
            $this->getResponse()
                ->setBody(
                    Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
                );
        }
    }

    /**
     * Delete shopping cart item action
     */
    public function deleteAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $currentStep = $this->getRequest()->getPost('step');
            $id          = (int)$this->getRequest()->getParam('id');
            if ($id) {
                $this->_getCart()->removeItem($id)
                    ->save();
            }
            if ($this->_expireAjax()) {
                return;
            }
            $this->getResponse()
                ->setBody(
                    Mage::helper('core')->jsonEncode($this->_extractStepOutput($currentStep))
                );
        }
    }

    public function getTopCartHtmlAction()
    {
        $html = $this->getLayout()
            ->createBlock('checkout/cart_sidebar')
            ->setTemplate('checkout/cart/cartheader.phtml')
            ->toHtml();

        if ($html) {
            $response = array(
                'content' => $html
            );
        } else {
            $response = array(
                'error' => true
            );
        }

        $this->getResponse()
            ->setBody(
                Mage::helper('core')->jsonEncode($response)
            );
    }

    public function removeAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($code = $this->getRequest()->getParam('giftcard_id')) {
            $this->_removeGiftCard($code);
        } elseif ($code = $this->getRequest()->getParam('storecredit')) {
            //customer balance is the same as store credit
            $this->_removeCustomerBalance();
        } elseif ($code = $this->getRequest()->getParam('reward')) {
            $this->_removeRewardBlock();
        }

        if ($code) {
            if ($this->_expireAjax()) {
                return;
            }
            $this->_getQuote()->collectTotals()->save();
            $this->getResponse()
                ->setBody(
                    Mage::helper('core')->jsonEncode($this->_extractStepOutput('payment'))
                );
        }
    }

    protected function _removeGiftCard($code)
    {
        try {
            Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                ->loadByCode($code)
                ->removeFromCart();
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError(
                $e->getMessage()
            );
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot remove gift card.'));
        }
    }

    protected function _removeRewardBlock()
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()
            || !Mage::helper('enterprise_reward')->getHasRates()
        ) {
            return $this->_redirect('customer/account/');
        }

        if ($this->_getQuote()->getUseRewardPoints()) {
            $this->_getQuote()->setUseRewardPoints(false);
            Mage::getSingleton('checkout/session')->addSuccess(
                $this->__('The reward points have been removed from the order.')
            );
        } else {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Reward points will not be used in this order.')
            );
        }
    }

    protected function _removeCustomerBalance()
    {
        if (!Mage::helper('enterprise_customerbalance')->isEnabled()) {
            $this->_redirect('customer/account/');

            return;
        }

        if ($this->_getQuote()->getUseCustomerBalance()) {
            Mage::getSingleton('checkout/session')->addSuccess(
                $this->__('The store credit payment has been removed from shopping cart.')
            );
            $this->_getQuote()->setUseCustomerBalance(false);
        } else {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Store Credit payment is not being used in your shopping cart.')
            );
        }
    }
}
