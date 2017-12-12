<?php

class Aitoc_Aitcheckout_Helper_Data extends Aitoc_Aitcheckout_Helper_Abstract
{

    const IS_SHOW_CHECKOUT_OUTSIDE_CART = 0;
    const IS_SHOW_CHECKOUT_IN_CART = 1;
    const IS_SHOW_CART_IN_CHECKOUT = 2;
    const IS_DISABLED = 3;

    const DESIGN_DEFAULT = 0;
    const DESIGN_COMPACT = 1;
    const DESIGN_COMPACT_V2 = 2;

    const CONDITIONS_DEFAULT = 0;
    const CONDITIONS_POPUP = 1;

    const CHECKBOX_AVAILABLE_INSTANTLY = 0;
    const CHECKBOX_VIEWING_REQUIRED = 1;

    protected $_checkoutShowMethod = null;
    protected $_designType = null;
    protected $_lastErrorMessage = false;

    protected $_rule = null;

    protected function _getCheckoutShowMethod()
    {
        if (is_null($this->_checkoutShowMethod)) {
            $this->_checkoutShowMethod = Mage::getStoreConfig('checkout/aitcheckout/checkout_show_method');
        }

        return $this->_checkoutShowMethod;
    }

    protected function _getDesignType()
    {
        if (is_null($this->_designType)) {
            $this->_designType = Mage::getStoreConfig('checkout/aitcheckout/design_type');
        }

        return $this->_designType;
    }

    public function isShowCheckoutInCart()
    {
        return (self::IS_SHOW_CHECKOUT_IN_CART == $this->_getCheckoutShowMethod());
    }

    public function isDisabled()
    {
        return (!$this->_checkRule() || self::IS_DISABLED == $this->_getCheckoutShowMethod()
            || !Mage::getStoreConfigFlag('checkout/options/onepage_checkout_enabled'));
    }

    public function isShowCheckoutOutsideCart()
    {
        return (self::IS_SHOW_CHECKOUT_OUTSIDE_CART == $this->_getCheckoutShowMethod());
    }

    public function isShowCartInCheckout()
    {
        return (self::IS_SHOW_CART_IN_CHECKOUT == $this->_getCheckoutShowMethod());
    }

    public function isCompactDesign()
    {
        return in_array($this->_getDesignType(), array(self::DESIGN_COMPACT, self::DESIGN_COMPACT_V2));
    }

    public function getDesignTypeId()
    {
        return $this->_getDesignType();
    }

    public function isShowProceedToCheckoutButton()
    {
        if ($this->isShowCheckoutInCart()) {
            return Mage::getStoreConfig('checkout/aitcheckout/show_proceed_to_checkout_button');
        }

        return true;
    }

    public function isShowCheckoutTitle()
    {
        return Mage::getStoreConfigFlag('checkout/aitcheckout/show_title');
    }

    public function getCheckoutTitle()
    {
        return Mage::getStoreConfig('checkout/aitcheckout/title_text');
    }

    public function getCheckoutUrl()
    {
        if ($this->isShowCheckoutInCart()) {
            return $this->getCartUrl();
        }

        return 'aitcheckout/checkout';
    }

    public function getCartUrl()
    {
        if ($this->isShowCartInCheckout()) {
            return $this->getCheckoutUrl();
        }

        return 'checkout/cart';
    }

    public function isCartPage()
    {
        $page    = $_SERVER['REQUEST_URI'];
        $onepage = "checkout/cart";

        return strpos($page, $onepage) ? true : false;
    }

    private function _checkRule()
    {
        if (is_null($this->_rule)) {
            $this->_rule = true;
            /* {#AITOC_COMMENT_END#}
            $iStoreId = Mage::app()->getStore()->getId();
            $iSiteId  = Mage::app()->getWebsite()->getId();
            $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckout')->getLicense()->getPerformer();
            $ruler     = $performer->getRuler();
            if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
            {
                $this->_rule = false;
            }
            {#AITOC_COMMENT_START#} */
        }

        return $this->_rule;
    }

    public function canEditCartItems()
    {
        if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.5')) {
            return true;
        }

        return false;
    }

    public function getReviewTotalsColspan()
    {
        if ($this->isCompactDesign() && $this->isShowCartInCheckout() && !$this->isSaveOrderAction()) {
            return 2;
        }
        $isDisplayTax         = Mage::helper('tax')->displayCartBothPrices() ? 2 : 0;
        $isAllowWishlist      = Mage::helper('wishlist')->isAllowInCart() ? 1 : 0;
        $canEditCartItems     = $this->canEditCartItems() ? 1 : 0;
        $isShowCheckoutInCart = ($this->isShowCartInCheckout() && !$this->isSaveOrderAction()) ? 1 : 0;

        return (($this->isShowCartInCheckout() && !$this->isSaveOrderAction()) ? 5 : 3)
        + $isAllowWishlist * $isShowCheckoutInCart
        + $canEditCartItems * $isShowCheckoutInCart
        + $isDisplayTax;
    }

    public function getDefaultCountry()
    {
        if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('>=1.5')) {
            if (!method_exists(Mage::helper('core'), "getDefaultCountry")) {
                return Mage::getStoreConfig('general/country/default');
            }

            return Mage::helper('core')->getDefaultCountry();
        } else {
            return Mage::getStoreConfig('general/country/default');
        }
    }

    /**
     * Get quote checkout method
     *
     * @return string
     */
    public function getCheckoutMethod($onepage)
    {
        if (Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion('=1.4.1')) {
            return $onepage->getCheckoutMethod();
        }

        return $onepage->getCheckoutMehod();
    }


    /**
     *
     * @return boolean
     */
    public function isPlaceOrderDisabled()
    {
        return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
    }

    public function isErrorQuoteItemQty()
    {
        $errorCode = '';

        if (version_compare(Mage::getVersion(), '1.10.0.0', '<')) {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();

            if (isset($quote)) {
                $messages = $quote->getMessages();

                if (isset($messages) && count($messages) > 0) {
                    $errorCode = Mage::helper('cataloginventory')->__(
                        'Some of the products cannot be ordered in requested quantity.'
                    );
                    foreach ($messages as $error) {
                        if ($error->getCode() == $errorCode) {
                            $this->_lastErrorMessage = $errorCode;

                            return true;
                        }
                    }
                }
            }
        } elseif (version_compare(Mage::getVersion(), '1.11.0.0', '>=')) {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();

            if (isset($quote)) {
                $quoteItems = $quote->getAllItems();
                foreach ($quoteItems as $quoteItem) {
                    if ($quoteItem->getHasError()) {
                        $errors = $quoteItem->getErrorInfos();
                        foreach ($errors as $error) {
                            if (isset($error['code'])
                                && $error['code'] == Mage_CatalogInventory_Helper_Data::ERROR_QTY
                            ) {
                                $this->_lastErrorMessage = $error['message'];

                                return true;
                            }
                        }
                    }
                }
            }
        }
        $this->_lastErrorMessage = false;

        return false;
    }

    public function getLastErrorMessage()
    {
        return $this->_lastErrorMessage;
    }

    /**
     * @return boolean
     */
    public function isSaveOrderAction()
    {
        return (Mage::app()->getRequest()->getActionName() == 'saveOrder');
    }

    public function isPaypalAdvancedAvailable()
    {
        return version_compare(Mage::getVersion(), '1.10.0.1', 'ge');
    }

    /**
     * @return boolean
     */
    public function requireLoggedInCheckout()
    {
        if ((!Mage::getStoreConfig('checkout/options/guest_checkout'))
            && Mage::getStoreConfig(
                'checkout/options/customer_must_be_logged'
            )
        ) {
            return true;
        }

        return false;
    }

    public function loginToHtml($aitcheckout)
    {

        $aitcheckoutHelper       = Mage::helper('aitcheckout');
        $requireLoggedInCheckout = $aitcheckoutHelper->requireLoggedInCheckout();
        $loginBlock              = $aitcheckout->getChild('reqlogin');
        if ($requireLoggedInCheckout && $loginBlock && !$loginBlock->isCustomerLoggedIn()) {
            $html        = $loginBlock->toHtml();
            $for_replace = $loginBlock->getUrl('customer/account/forgotpassword');
            $replacement = '#" id="forgotB" onclick="return false;';
            $html        = str_replace($loginBlock->getMessagesBlock()->getGroupedHtml(), '', $html);

            return str_replace($for_replace, $replacement, $html);
        }

        return false;
    }

    public function isNeedRedirectToSecure()
    {
        $checkoutUrlHttps = Mage::getUrl($this->getCheckoutUrl(), array('_secure' => true));
        $isSecure         = Mage::app()->getStore()->isCurrentlySecure();
        if (!$isSecure && (strpos($checkoutUrlHttps, 'https:') !== false)) {
            return true;
        }

        return false;
    }

    public function switchCartGiftTemplate()
    {
        if ($this->isDisabled() || (!$this->isShowCheckoutInCart())) {
            return '';
        }

        return 'aitcheckout/giftregistry/customer/checkout/onepage.phtml';
    }

    public function setDefaultShippingMethod()
    {
        $shippingMethod = Mage::getStoreConfig(
            'checkout/aitcheckout/default_shipping_method',
            Mage::app()->getStore()->getId()
        );
        if (!empty($shippingMethod)) {
            $quote       = Mage::getSingleton('checkout/type_onepage')->getQuote();
            $oldShipping = $quote->getShippingAddress()->getShippingMethod();
            if (empty($oldShipping)) {
                $quote->getShippingAddress()->setShippingMethod($shippingMethod);
            }
        }
    }

}
