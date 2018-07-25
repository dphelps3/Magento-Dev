<?php
/**
 * Handles Payment Form in Checkout
 *
 * @copyright   Bread   2016
 * @author      Joel    @Mediotype
 * Class Bread_BreadCheckout_Block_Payment_Form
 */
class Bread_BreadCheckout_Block_Payment_Form extends Mage_Payment_Block_Form
{
    protected $_quote;      /** @var $_quote Mage_Sales_Model_Quote */

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('breadcheckout/form.phtml');
    }

    /**
     * Get Bread Formatted Shipping Address Data
     *
     * @return string
     */
    public function getShippingAddressData()
    {
        $shippingAddress    = $this->_getQuote()->getShippingAddress();

        if ($shippingAddress->getStreet(-1) === null) {
            return 'false';
        }

        $breadAddressData   = $this->helper('breadcheckout/Quote')->getFormattedShippingAddressData($shippingAddress);

        return $this->helper('core')->jsonEncode($breadAddressData);
    }

    /**
     * Get Bread Formatted Billing Address Data
     *
     * @return string
     */
    public function getBillingAddressData()
    {
        $billingAddress     = $this->_getQuote()->getBillingAddress();

        if ($billingAddress->getStreet(-1) === null) {
            return 'false';
        }

        $breadAddressData   = $this->helper('breadcheckout/Quote')->getFormattedBillingAddressData($billingAddress);

        return $this->helper('core')->jsonEncode($breadAddressData);
    }

    /**
     * Get Tax Amount From Quote
     *
     * @return float
     */
    public function getTaxValue()
    {
        return $this->helper('breadcheckout/Quote')->getTaxValue();
    }

    /**
     * Get Grand Total From Quote
     *
     * @return mixed
     */
    public function getGrandTotal()
    {
        return $this->helper('breadcheckout/Quote')->getGrandTotal();
    }

    /**
     * Get Discount Data From Quote as JSON
     *
     * @return string
     */
    public function getDiscountDataJson()
    {
        $discountData   = $this->helper('breadcheckout/Quote')->getDiscountData();

        return $this->helper('core')->jsonEncode($discountData);
    }

    /**
     * Get Items Data From Quote As JSON
     *
     * @return string JSON String or Empty JSON Array String
     */
    public function getItemsData()
    {
        $itemsData      = $this->helper('breadcheckout/Quote')->getQuoteItemsData();

        return $this->helper('core')->jsonEncode($itemsData);
    }

    /**
     * Get Bread Formatted Shipping Options Information
     *
     * @return string
     */
    public function getShippingOptions()
    {
        $shippingAddress        = $this->_getQuote()->getShippingAddress();

        if (!$shippingAddress->getShippingMethod()) {
            return 'false';
        }

        $data   = $this->helper('breadcheckout/quote')->getFormattedShippingOptionsData($shippingAddress);

        return $this->helper('core')->jsonEncode($data);
    }

    /**
     * Get Incomplete Checkout Messaging
     *
     * @return string
     */
    public function getIncompleteCheckoutMessage()
    {
        return $this->helper('breadcheckout')->getIncompleteCheckoutMsg();
    }

    /**
     * Get Incomplete Checkout Messaging
     *
     * @return string
     */
    public function getSkipReview()
    {
        return $this->helper('breadcheckout')->skipReview();
    }

    /**
     * Add context URL based on frontend or admin
     *
     * @param $route
     * @return string
     */
    public function getContextUrl($route)
    {
        $isSecure = Mage::app()->getFrontController()->getRequest()->isSecure();
        if (Mage::app()->getStore()->isAdmin()) {
            $adminUrl = Mage::getModel('adminhtml/url')->getUrl('adminhtml/' . $route, array('_secure'=>true));
            return $adminUrl;
        } else {
            return $this->getUrl($route, array('_secure'=>true));
        }
    }

    /**
     * Is Default Size Handling
     *
     * @return string
     */
    public function getIsDefaultSize()
    {
        return $this->helper('breadcheckout/Catalog')->getDefaultButtonSizeCheckoutHtml();

    }

    /**
     * Get validate payment URL
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        return $this->helper('breadcheckout')->getPaymentUrl();
    }

    /**
     * Get tx ID from Quote
     *
     * @return mixed
     */
    public function getBreadTransactionId()
    {
        return $this->_getQuote()->getBreadTransactionId();
    }

    /**
     * Get Additional Design (CSS) From the Admin
     *
     * @return mixed
     */
    public function getButtonDesign()
    {
        if ($this->helper('breadcheckout')->getUseCustomCssCheckout()) {
            return $this->helper('breadcheckout')->getButtonDesign();
        } else {
            return $this->helper('breadcheckout')->getCheckoutButtonDesign();
        }
    }
    
    /**
     * Use new window instead of modal
     * 
     * @return unknown  
     */
    public function getShowInWindow()
    {
        return ( $this->helper('breadcheckout')->getShowInWindowCO() ) ? 'true' : 'false';
    }

    /**
     * Get validate totals url
     * 
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->helper('breadcheckout')->getValidateTotalsUrl();
    }
    
    /**
     * Flag Indicative Pricing Or Not
     *
     * @return string
     */
    protected function getAsLowAs()
    {
        return ( $this->helper('breadcheckout')->isAsLowAsCP() ) ? 'true' : 'false';
    }

    /**
     * Get Session Quote Object for Frontend or Admin
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        if ($this->_quote == null) {
                $this->_quote       = $this->helper('breadcheckout/Quote')->getSessionQuote();
        }

        return $this->_quote;
    }

    /**
     * Check if Cart Size financing is enabled
     *
     * @return bool
     */
    public function isCartSizeTargetedFinancing()
    {
        return $this->helper('breadcheckout')->isCartSizeTargetedFinancing();
    }

    /**
     * Get cart size over which targeted financing is enabled
     *
     * @return string
     */
    public function getCartSizeThreshold()
    {
        return $this->helper('breadcheckout')->getCartSizeThreshold();
    }

    /**
     * Get financing ID associated with cart size threshold
     *
     * @return string
     */
    public function getCartSizeFinancingId()
    {
        return $this->helper('breadcheckout')->getCartSizeFinancingId();

    }

    public function hasMethodTitle()
    {
        return true;
    }

    public function getMethodTitle()
    {
        if( (Mage::getModel('checkout/cart')->getQuote()->getGrandTotal()) >= 250.0000) {

            $title = $this->getMethod()->getTitle();

            try {
                $estimate = Mage::helper("breadcheckout/quote")->getQuoteEstimate();
                $title .= ' ' . $this->__("as low as %s/month", $estimate);
            } catch (Exception $e) {
                Mage::helper('breadcheckout')->log($e->getMessage(), 'bread-exception.log');
            }


            return $title;
        }
    }

}
