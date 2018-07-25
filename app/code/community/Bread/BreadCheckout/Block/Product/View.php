<?php
/**
 * Handles Product View Block
 *
 * @copyright   Bread   2016
 * @author      Joel    @Mediotype
 */
class Bread_BreadCheckout_Block_Product_View extends Mage_Core_Block_Template
{

    protected $_product;

    protected function _construct()
    {
        $this->setBlockCode($this->getBlockCode());
        if ($this->getBlockCode() === Bread_BreadCheckout_Helper_Data::BLOCK_CODE_PRODUCT_VIEW) {
            $this->setAdditionalData(
                array(
                'product_id' => $this->getProduct()->getId()
                )
            );
        }

        parent::_construct();
    }

    /**
     * Get Current Product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (null === $this->_product) {
            $this->_product     = Mage::registry('product');
        }

        return $this->_product;
    }

    /**
     * Get Product Data as JSON
     *
     * @return string
     */
    public function getProductDataJson()
    {
        $product    = $this->getProduct();
        $data       = array(
                        $this->helper('breadcheckout/catalog')->getProductDataArray($product, null)
                    );

        return $this->helper('core')->jsonEncode($data);
    }

    /**
     * Returns empty values so that the page can work the same as the cart page.
     *
     * @return string
     */
    public function getDiscountDataJson()
    {
        $result     = array();
        return $this->helper('core')->jsonEncode($result);
    }

    /**
     * Get Default Customer Shipping Address If It Exists
     *
     * @return string
     */
    public function getShippingAddressData()
    {
        return $this->helper('breadcheckout/Customer')->getShippingAddressData();
    }

    /**
     * Get Billing Address Default Data
     *
     * @return string
     */
    public function getBillingAddressData()
    {
        return $this->helper('breadcheckout/Customer')->getBillingAddressData();
    }

    /**
     * Get As Low As Option Value
     *
     * @return string
     */
    protected function getAsLowAs()
    {
        return ( $this->helper('breadcheckout')->isAsLowAsPDP() ) ? 'true' : 'false';
    }
    
    /**
     * Use new window instead of modal
     * 
     * @return bool  
     */
    public function getShowInWindow()
    {
        return ( $this->helper('breadcheckout')->getShowInWindowPDP() ) ? 'true' : 'false';
    }

    /**
     * Checks Settings For Show On Product Detail Page During Output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helper('breadcheckout')->isEnabledOnPDP()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Get Shipping Estimate Url
     *
     * @return string
     */
    public function getShippingAddressEstimationUrl()
    {
        return $this->helper('breadcheckout')->getShippingEstimateUrl();
    }

    /**
     * Get Tax Estimate URL
     *
     * @return string
     */
    public function getTaxEstimationUrl()
    {
        return $this->helper('breadcheckout')->getTaxEstimateUrl();
    }

    /**
     * Get Validate Order URL
     *
     * @return string
     */
    public function getValidateOrderUrl()
    {
        return $this->helper('breadcheckout')->getValidateOrderURL();
    }

    /**
     * Get Is Button On Product
     *
     * @return string
     */
    public function getIsButtonOnProduct()
    {
        return ( $this->helper('breadcheckout')->isButtonOnProducts() ) ? 'true' : 'false';
    }

    /**
     * Get Default Button Size String For The View
     *
     * @return string
     */
    public function getIsDefaultSize()
    {
        return (string) $this->helper('breadcheckout/Catalog')->getDefaultButtonSizeProductDetailHtml();
    }

    /**
     * Check if checkout through Bread interaction is allowed
     *
     * @return mixed
     */
    public function getAllowCheckout()
    {
        return ($this->helper('breadcheckout')->getAllowCheckoutPDP()) ? 'true' : 'false';
    }

    /**
     * Return Block View Product Code
     *
     * @return string
     */
    public function getBlockCode()
    {
        return (string) $this->helper('breadcheckout')->getBlockCodeProductView();
    }

    /**
     * Get Extra Button Design CSS
     *
     * @return mixed
     */
    public function getButtonDesign()
    {
        return $this->helper('breadcheckout')->getButtonDesign();
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
}
