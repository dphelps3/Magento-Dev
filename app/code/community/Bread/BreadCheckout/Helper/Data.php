<?php
/**
 * Handles Config & Basic Shared Helper Functionality
 *
 * @author  Bread   copyright   2016
 * @author  Joel    @Mediotype
 */
class Bread_BreadCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{

    const API_SANDBOX_URI                           = "https://api-sandbox.getbread.com/";
    const API_LIVE_URI                              = "https://api.getbread.com/";

    const JS_SANDBOX_URI                            = "https://checkout-sandbox.getbread.com/bread.js";
    const JS_LIVE_URI                               = "https://checkout.getbread.com/bread.js";

    const CLIENT_API_SANDBOX_URI                    = "https://checkout-sandbox.getbread.com/";
    const CLIENT_API_LIVE_URI                       = "https://checkout.getbread.com/";

    const URL_VALIDATE_PAYMENT                      = "bread/Bread/validatePaymentMethod";
    const URL_VALIDATE_ORDER                        = "bread/Bread/validateOrder";
    const URL_VALIDATE_TOTALS                       = "bread/Bread/validateTotals";
    const URL_SHIPPING_ESTIMATE                     = "bread/Bread/shippingEstimation";
    const URL_TAX_ESTIMATE                          = "bread/Bread/taxEstimation";
    const URL_BACKEND_ORDER_VALIDATION              = "bread/Bread/validateBackendOrder";
    const URL_LANDING_PAGE                          = "bread/Bread/landingPage";
    const URL_ADMIN_VALIDAT_PAYMENT                 = "bread/validatePaymentMethod";
    const URL_ADMIN_CREATE_CART                     = "bread/createCart";
    const URL_ADMIN_SEND_MAIL                       = "bread/sendMail";
    const URL_ADMIN_SEND_SMS                        = "bread/sendSms";
    const URL_ADMIN_SEND_BREAD_MAIL                 = "bread/sendBreadMail";
    const URL_ADMIN_SALES_ORDER_CREATE              = "sales_order_create/cancel";
    const URL_GET_ESTIMATE                          = "bread/Bread/getEstimate";

    const XML_CONFIG_MODULE_ACTIVE                  = 'payment/breadcheckout/active';
    const XML_CONFIG_LOG_ENABLED                    = 'payment/bread_advanced/log_enabled';
    const XML_CONFIG_PDP_AS_LOW_AS                  = 'payment/bread_productdetail/as_low_as';
    const XML_CONFIG_CAT_AS_LOW_AS                  = 'payment/bread_categorypage/as_low_as';
    const XML_CONFIG_CP_AS_LOW_AS                   = 'payment/bread_cartpage/as_low_as';
    const XML_CONFIG_PAYMENT_ACTION                 = 'payment/breadcheckout/payment_action';

    const XML_CONFIG_ACTIVE_ON_PDP                  = 'payment/bread_productdetail/enabled_on_product_page';
    const XML_CONFIG_ACTIVE_ON_CAT                  = 'payment/bread_categorypage/enabled_on_category_page';
    const XML_CONFIG_ACTIVE_ON_CART_VIEW            = 'payment/bread_cartpage/enabled_on_cart_page';
    const XML_CONFIG_ENABLE_AS_PAYMENT_METHOD       = 'payment/bread_checkout/display_as_payment_method';

    const XML_CONFIG_HEALTHCARE_MODE                = 'payment/breadcheckout/healthcare_mode';
    const XML_CONFIG_SKIP_REVIEW_STEP               = 'payment/breadcheckout/skip_review_step';

    const XML_CONFIG_CHECKOUT_TITLE                 = 'payment/breadcheckout/title';
    const XML_CONFIG_INCOMPLETE_MSG                 = 'payment/bread_checkout/incomplete_checkout_message';
    
    const XML_CONFIG_API_PUB_KEY                    = 'payment/breadcheckout/api_public_key';
    const XML_CONFIG_API_SECRET_KEY                 = 'payment/breadcheckout/api_secret_key';
    const XML_CONFIG_API_SANDBOX_PUB_KEY            = 'payment/breadcheckout/api_sandbox_public_key';
    const XML_CONFIG_API_SANDBOX_SECRET_KEY         = 'payment/breadcheckout/api_sandbox_secret_key';
    
    const XML_CONFIG_JS_LIB_LOCATION                = 'payment/breadcheckout/js_location';
    const XML_CONFIG_BUTTON_ON_PRODUCTS             = 'payment/bread_productdetail/button_on_products';
    const XML_CONFIG_CAT_LABEL_ONLY                 = 'payment/bread_categorypage/label_only';
    
    const XML_CONFIG_CO_CUST_CSS                    = 'payment/bread_checkout/use_pdp_css';
    const XML_CONFIG_CP_CUST_CSS                    = 'payment/bread_cartpage/use_pdp_css';
    const XML_CONFIG_CO_BUTTON_DESIGN               = 'payment/bread_checkout/button_design';
    const XML_CONFIG_CP_BUTTON_DESIGN               = 'payment/bread_cartpage/button_design';
    const XML_CONFIG_PDP_BUTTON_DESIGN              = 'payment/bread_productdetail/button_design';
    const XML_CONFIG_CAT_BUTTON_DESIGN              = 'payment/bread_categorypage/button_design';
    const XML_CONFIG_CO_WINDOW                      = 'payment/bread_checkout/display_new_window';
    const XML_CONFIG_CP_WINDOW                      = 'payment/bread_cartpage/display_new_window';
    const XML_CONFIG_PDP_WINDOW                     = 'payment/bread_productdetail/display_new_window';
    const XML_CONFIG_CAT_WINDOW                     = 'payment/bread_categorypage/display_new_window';
    const XML_CONFIG_API_MODE                       = 'payment/breadcheckout/api_mode';
    const XML_CONFIG_CO_SORT_ORDER                  = 'payment/bread_checkout/sort_order';
    
    const XML_CONFIG_DEFAULT_BS_PDP                 = 'payment/bread_productdetail/use_default_button_size';
    const XML_CONFIG_DEFAULT_BS_CAT                 = 'payment/bread_categorypage/use_default_button_size';
    const XML_CONFIG_DEFAULT_BS_CP                  = 'payment/bread_cartpage/use_default_button_size';
    const XML_CONFIG_DEFAULT_BS_CO                  = 'payment/bread_checkout/use_default_button_size';
    
    const XML_CONFIG_SELECT_CATEGORIES              = 'payment/bread_categorypage/categories';

    const XML_CONFIG_CREATE_CUSTOMER                = 'payment/bread_advanced/create_customer_account';
    const XML_CONFIG_ALLOW_CHECKOUT_PDP             = 'payment/bread_productdetail/allowcheckoutpdp';
    const XML_CONFIG_ALLOW_CHECKOUT_CART            = 'payment/bread_cartpage/allowcheckoutcart';

    //  Targeted financing - cart size
    const XML_CONFIG_ENABLE_CART_SIZE_FINANCING     = 'payment/bread_advanced/cart_size_targeted_financing';
    const XML_CONFIG_CART_SIZE_THRESHOLD            = 'payment/bread_advanced/cart_threshold';
    const XML_CONFIG_CART_SIZE_FINANCING_ID         = 'payment/bread_advanced/cart_size_financing_program_id';

    const BLOCK_CODE_PRODUCT_VIEW                   = 'product_view';
    const BLOCK_CODE_CHECKOUT_OVERVIEW              = 'checkout_overview';

    const API_CART_EXTENSION                        = 'carts/';

    // Bread button locations
    const BUTTON_LOCATION_PRODUCT_VIEW              = 'product';
    const BUTTON_LOCATION_CART_SUMMARY              = 'cart_summary';
    const BUTTON_LOCATION_CHECKOUT                  = 'checkout';
    const BUTTON_LOCATION_FINANCING                 = 'financing';
    const BUTTON_LOCATION_MARKETING                 = 'marketing';
    const BUTTON_LOCATION_CATEGORY                  = 'category';
    const BUTTON_LOCATION_OTHER                     = 'other';

    /**
     * Is module active?
     *
     * @param null $store
     * @return bool
     */
    public function isActive($store = null)
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_CONFIG_MODULE_ACTIVE, $store);
    }

    /**
     * Is Logging Enabled
     *
     * @param null $store
     * @return bool
     */
    public function logEnabled($store = null)
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_CONFIG_LOG_ENABLED, $store);
    }

    /**
     * Skip Review Step
     *
     * @param null $store
     * @return bool
     */
    public function skipReview($store = null)
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_CONFIG_SKIP_REVIEW_STEP, $store);
    }

    /**
     * Is Healthcare mode?
     *
     * @param null $store
     * @return bool
     */
    public function isHealthcare($store = null)
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_CONFIG_HEALTHCARE_MODE, $store);
    }

    /**
     * Get API Pub Key
     *
     * @param null $store
     * @return mixed
     */
    public function getApiPublicKey($store = null)
    {
        if (Mage::getStoreConfigFlag(self::XML_CONFIG_API_MODE, $store)) {
            return Mage::getStoreConfig(self::XML_CONFIG_API_PUB_KEY, $store);
        } else {
            return Mage::getStoreConfig(self::XML_CONFIG_API_SANDBOX_PUB_KEY, $store);
        }
    }

    /**
     * Get API Secret Key
     *
     * @param null $store
     * @return string
     */
    public function getApiSecretKey($store = null)
    {
        if (Mage::getStoreConfigFlag(self::XML_CONFIG_API_MODE, $store)) {
            return (string) Mage::helper('core')
                ->decrypt(Mage::getStoreConfig(self::XML_CONFIG_API_SECRET_KEY, $store));
        } else {
            return (string) Mage::helper('core')
                ->decrypt(Mage::getStoreConfig(self::XML_CONFIG_API_SANDBOX_SECRET_KEY, $store));
        }
    }

    /**
     * Get JS Lib Location
     *
     * @param null $store
     * @return mixed
     */
    public function getJsLibLocation($store = null)
    {
        if (Mage::getStoreConfigFlag(self::XML_CONFIG_API_MODE, $store)) {
            return self::JS_LIVE_URI;
        } else {
            return self::JS_SANDBOX_URI;
        }
    }

    /**
     * Get API Url
     *
     * @param null $store
     * @return mixed
     */
    public function getTransactionApiUrl($store = null)
    {
        if (Mage::getStoreConfigFlag(self::XML_CONFIG_API_MODE, $store)) {
            return self::API_LIVE_URI;
        } else {
            return self::API_SANDBOX_URI;
        }
    }

    /**
     * Get cart API Url
     *
     * @param null $store
     * @return mixed
     */
    public function getCartCreateApiUrl($store = null)
    {
        return $this->getTransactionApiUrl($store).self::API_CART_EXTENSION;
    }

    /**
     * get Payment URL
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        $isSecure = Mage::app()->getFrontController()->getRequest()->isSecure();
        return Mage::getModel('core/url')->getUrl(self::URL_VALIDATE_PAYMENT, array('_secure'=>$isSecure));
    }

    /**
     * Get The Validate Order URL
     *
     * @return string
     */
    public function getValidateOrderURL()
    {
        $isSecure = Mage::app()->getFrontController()->getRequest()->isSecure();
        return Mage::getModel('core/url')->getUrl(self::URL_VALIDATE_ORDER, array('_secure'=>$isSecure));
    }

    /**
     * Get The Validate Order URL
     *
     * @return string
     */
    public function getValidateBackendOrderURL()
    {
        return Mage::getModel('core/url')->getUrl(self::URL_BACKEND_ORDER_VALIDATION);
    }

    /**
     * Get The Validate Order URL
     *
     * @return string
     */
    public function getAdminCreateOrderURL()
    {
        return self::URL_ADMIN_SALES_ORDER_CREATE;
    }

    /**
     * Get The Validate Order URL
     *
     * @return string
     */
    public function getLandingPageURL($error=false)
    {
        return Mage::getModel('core/url')->getUrl(self::URL_LANDING_PAGE, $error ? array("error"=>$error) : array());
    }

    /**
     * Get The Validate Totals URL
     *
     * @return string
     */
    public function getValidateTotalsUrl()
    {
        $isSecure = Mage::app()->getFrontController()->getRequest()->isSecure();
        return Mage::getModel('core/url')->getUrl(self::URL_VALIDATE_TOTALS, array('_secure'=>$isSecure));
    }

    /**
     * Get Shipping Address Estimate URL
     *
     */
    public function getShippingEstimateUrl()
    {
        $isSecure = Mage::app()->getFrontController()->getRequest()->isSecure();
        return Mage::getModel('core/url')->getUrl(self::URL_SHIPPING_ESTIMATE, array('_secure'=>$isSecure));
    }

    /**
     * Get The Tax Estimate URL
     *
     * @return string
     */
    public function getTaxEstimateUrl()
    {
        $isSecure = Mage::app()->getFrontController()->getRequest()->isSecure();
        return Mage::getModel('core/url')->getUrl(self::URL_TAX_ESTIMATE, array('_secure'=>$isSecure));
    }

    /**
     * Get Admin URL Path for Block Context Url Call
     *
     * @return string
     */
    public function getAdminFormUrlPath()
    {
        return self::URL_ADMIN_VALIDAT_PAYMENT;
    }

    /**
     * Get Admin URL Path for cart creation
     *
     * @return string
     */
    public function getAdminCartCreatePath()
    {
        return self::URL_ADMIN_CREATE_CART;
    }

    /**
     * Get Admin URL Path for Confirmation Email Sending
     *
     * @return string
     */
    public function getAdminSendEmailPath()
    {
        return self::URL_ADMIN_SEND_MAIL;
    }

    /**
     * Get Admin URL Path for Bread Cart SMS Sending
     *
     * @return string
     */
    public function getAdminSendSmsPath()
    {
        return self::URL_ADMIN_SEND_SMS;
    }

    /**
     * Get Admin URL Path for Bread Cart Email Sending
     *
     * @return string
     */
    public function getAdminSendBreadEmailPath()
    {
        return self::URL_ADMIN_SEND_BREAD_MAIL;
    }

    /**
     * Get media URL Path
     *
     * @return string
     */
    public function getMediaPath($image)
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $image;

    }

    /**
     * Auth or Auth & Settle
     *
     * @param null $store
     * @return string
     */
    public function getPaymentAction($store = null)
    {
        return (string) Mage::getStoreConfig(self::XML_CONFIG_PAYMENT_ACTION, $store);
    }

    /**
     * Payment Method Title During Checkout
     *
     * @param null $store
     * @return string
     */
    public function getPaymentMethodTitle($store = null)
    {
        return (string) $this->__(Mage::getStoreConfig(self::XML_CONFIG_CHECKOUT_TITLE), $store);
    }

    /**
     * Is Customer Account Created During Bread Work Flow?
     *
     * @param null $store
     * @return bool
     */
    public function isAutoCreateCustomerAccountEnabled($store = null)
    {
        return (bool) ($this->isActive($store) && Mage::getStoreConfig(self::XML_CONFIG_CREATE_CUSTOMER, $store));
    }

    /**
     * Is button on product page?
     *
     * @param null $store
     * @return bool
     */
    public function isButtonOnProducts($store = null)
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_CONFIG_BUTTON_ON_PRODUCTS, $store);
    }

    /**
     * Is label only on product page?
     *
     * @param null $store
     * @return bool
     */
    public function isLabelOnlyOnCategories($store = null)
    {
        if(Mage::getStoreConfigFlag(self::XML_CONFIG_CAT_LABEL_ONLY, $store))
            return 'true';
        else
            return 'false';
    }

    /**
     *
     *
     * @param null $store
     * @return bool
     */
    public function isEnabledOnPDP($store = null)
    {
        //Is customer logged in and has a non US default billing address?

        return (bool) ($this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_ACTIVE_ON_PDP, $store));
    }

    /**
     * Is button enabled on category pages
     *
     * @param null $store
     * @return bool
     */
    public function isEnabledOnCAT($store = null)
    {
        //Is customer logged in and has a non US default billing address?

        return (bool) ($this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_ACTIVE_ON_CAT, $store));
    }

    /**
     * Enable button view on cart page
     *
     * @param null $store
     * @return bool
     */
    public function isEnabledOnCOP($store = null)
    {
        //Is customer logged in and has a non US default billing address?

        return (bool) ($this->isActive($store) &&
            Mage::getStoreConfigFlag(self::XML_CONFIG_ACTIVE_ON_CART_VIEW, $store));
    }

    /**
     * Use Bread As Payment Method In Checkout?
     *
     * @param null $store
     * @return bool
     */
    public function isPaymentMethodAtCheckout($store = null)
    {
        return (bool) ($this->isActive($store) &&
            Mage::getStoreConfigFlag(self::XML_CONFIG_ENABLE_AS_PAYMENT_METHOD, $store));
    }

    /**
     * Use As Low As Pricing View?
     *
     * @param null $store
     * @return bool
     */
    public function isAsLowAsPDP($store = null)
    {
        return (bool) ($this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_PDP_AS_LOW_AS, $store));
    }

    /**
     * Use As Low As Pricing View?
     *
     * @param null $store
     * @return bool
     */
    public function isAsLowAsCAT($store = null)
    {
        if($this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_CAT_AS_LOW_AS, $store))
            return 'true';
        else
            return 'false';
    }
    
    public function isAsLowAsCP($store = null)
    {
        return (bool) ($this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_CP_AS_LOW_AS, $store));
    }

    /**
     * Allow Checkout From Bread Pop Up on PDP
     *
     * @param null $store
     * @return bool
     */
    public function getAllowCheckoutPDP($store = null)
    {
        return (bool) ($this->isActive($store) && !$this->isHealthcare()
            && Mage::getStoreConfigFlag(self::XML_CONFIG_ALLOW_CHECKOUT_PDP, $store));
    }

    /**
     * Allow Checkout From Bread On Cart Page
     *
     * @param null $store
     * @return bool
     */
    public function getAllowCheckoutCP($store = null)
    {
        return (bool) ($this->isActive($store) && !$this->isHealthcare()
            && Mage::getStoreConfigFlag(self::XML_CONFIG_ALLOW_CHECKOUT_CART, $store));
    }

    /**
     * Get Product View Block Code
     *
     * @return string
     */
    public function getBlockCodeProductView()
    {
        return  (string) self::BLOCK_CODE_PRODUCT_VIEW;
    }

    /**
     * Get Checkout Overview Block Code
     *
     * @return string
     */
    public function getBlockCodeCheckoutOverview()
    {
        return (string) self::BLOCK_CODE_CHECKOUT_OVERVIEW;
    }

    /**
     * Get Custom Button Design
     *
     * @param null $store
     * @return mixed
     */
    public function getButtonDesign($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PDP_BUTTON_DESIGN, $store);
    }

    /**
     * Get Custom Button Design for Category Page
     *
     * @param null $store
     * @return mixed
     */
    public function getCATButtonDesign($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_CAT_BUTTON_DESIGN, $store);
    }
    
    /**
     * Check If PDP Button Design Is Used On Checkout
     * 
     * @param null $store
     * @return bool 
     */
    public function getUseCustomCssCheckout($store = null)
    {
        return (bool) Mage::getStoreConfig(self::XML_CONFIG_CO_CUST_CSS, $store);
    }
    
    /**
     * Check If PDP Button Design Is Used On Checkout Cart Page
     * 
     * @param null $store
     * @return bool
     */
    public function getUseCustomCssCart($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_CP_CUST_CSS, $store);
    }
    
    /**
     * Get Checkout Button Design
     * 
     * @param null $store
     * @return mixed  
     */
    public function getCheckoutButtonDesign($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_CO_BUTTON_DESIGN, $store);
    }
    
    /**
     * Get Checkout Cart Button Design
     * 
     * @param null $store
     * @return mixed  
     */
    public function getCartButtonDesign($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_CP_BUTTON_DESIGN, $store);
    }

    /**
     * Check If Default Button Size Is Used On Product Detail Page
     *
     * @param null $store
     * @return bool
     */
    public function useDefaultButtonSizeProductDetail($store = null)
    {
        return (bool) ($this->isActive($store) && Mage::getStoreConfig(self::XML_CONFIG_DEFAULT_BS_PDP, $store));
    }

    /**
     * Check If Default Button Size Is Used On Category Page
     *
     * @param null $store
     * @return bool
     */
    public function useDefaultButtonSizeCategory($store = null)
    {
        if($this->isActive($store) && Mage::getStoreConfig(self::XML_CONFIG_DEFAULT_BS_CAT, $store))
            return 'data-bread-default-size="true"';
        else
            return '';
    }
    
    /**
     * Check If Default Button Size Is Used On Cart Overview Page
     *
     * @param null $store
     * @return bool
     */
    public function useDefaultButtonSizeCart($store = null)
    {
        return (bool) ($this->isActive($store) && Mage::getStoreConfig(self::XML_CONFIG_DEFAULT_BS_CP, $store));
    }
    
    /**
     * Check If Default Button Size Is Used On Checkout
     *
     * @param null $store
     * @return bool
     */
    public function useDefaultButtonSizeCheckout($store = null)
    {
        return (bool) ($this->isActive($store) && Mage::getStoreConfig(self::XML_CONFIG_DEFAULT_BS_CO, $store));
    }
    
    /**
     * Check If Open In Window on Product Detail Page
     * 
     * @param null $store
     * @return boolean  
     */
    public function getShowInWindowPDP($store = null)
    {
        return (bool) Mage::getStoreConfig(self::XML_CONFIG_PDP_WINDOW, $store); 
    }

    /**
     * Check If Open In Window on Category Page
     *
     * @param null $store
     * @return boolean
     */
    public function getShowInWindowCAT($store = null)
    {
        if(Mage::getStoreConfig(self::XML_CONFIG_CAT_WINDOW, $store))
            return 'true';
        else
            return 'false';
    }
    
    /**
     * Check If Open In Window on Checkout Page
     * 
     * @param null $store
     * @return boolean
     */
    public function getShowInWindowCO($store = null)
    {
        return (bool) Mage::getStoreConfig(self::XML_CONFIG_CO_WINDOW, $store);
    }
    
    /**
     * Check If Open In Window on Cart Overview Page
     * 
     * @param null $store
     * @return boolean  
     */
    public function getShowInWindowCP($store = null)
    {
        return (bool) Mage::getStoreConfig(self::XML_CONFIG_CP_WINDOW, $store);
    }

    /**
     * Incomplete Checkout Message For Payment Method Form
     *
     * @param null $store
     * @return string
     */
    public function getIncompleteCheckoutMsg($store = null)
    {
        return "";
    }

    /**
     * Get Default Country
     *
     * @param null $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return 'US';
    }

    /**
     * Check if Called From Admin Or Not
     *
     * @return bool
     */
    public function isInAdmin()
    {
        return (bool) Mage::app()->getStore()->isAdmin();
    }

    /**
     * Check if cart size targeted financing is enabled
     *
     * @return bool
     */
    public function isCartSizeTargetedFinancing($store = null)
    {
        return (bool) ($this->isActive($store) &&
            Mage::getStoreConfigFlag(self::XML_CONFIG_ENABLE_CART_SIZE_FINANCING, $store));
    }

    /**
     * Get cart size over which targeted financing is enabled
     *
     * @return string
     */
    public function getCartSizeThreshold($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_CART_SIZE_THRESHOLD, $store);
    }

    /**
     * Get financing ID associated with cart size threshold
     *
     * @return string
     */
    public function getCartSizeFinancingId($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_CART_SIZE_FINANCING_ID, $store);
    }

    /**
     * Convert price to price in cents
     *
     * @return string
     */
    public function getCentsFromPrice($price = 0)
    {
        return (int)(floatval($price) * 100);
    }



    /**
     * Get estimate API url
     *
     * @param null $store
     * @return mixed
     */
    public function getCreditTermsUrl($store = null)
    {
        return $this->getClientTransactionApiUrl($store).self::API_CREDIT_TERMS_EXTENSION;
    }

    /**
     * Get bread categories
     *
     * @param null $store
     * @return mixed
     */
    public function getBreadCategories($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_SELECT_CATEGORIES, $store);
    }

    /**
     * Get store scope in system config
     *
     * @return string
     */

    public function getConfigStoreScope()
    {
        if (($code = Mage::getSingleton('adminhtml/config_data')->getStore()) !== "") {
            $storeId = Mage::getModel('core/store')->load($code)->getId();
        } elseif (($code = Mage::getSingleton('adminhtml/config_data')->getWebsite()) !== "") {
            $websiteId = Mage::getModel('core/website')->load($code)->getId();
            $storeId = Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
        } else {
            $storeId = 0;
        }

        return $storeId;
    }

    /**
     * Get button location string for product page
     *
     * @return string
     */
    public function getProductViewLocation()
    {
        return self::BUTTON_LOCATION_PRODUCT_VIEW;
    }

    /**
     * Get button location string for cart summary page
     *
     * @return string
     */
    public function getCartSummaryLocation()
    {
        return self::BUTTON_LOCATION_CART_SUMMARY;
    }

    /**
     * Get button location string for checkout page
     *
     * @return string
     */
    public function getCheckoutLocation()
    {
        return self::BUTTON_LOCATION_CHECKOUT;
    }

    /**
     * Get button location string for financing page
     *
     * @return string
     */
    public function getFinancingLocation()
    {
        return self::BUTTON_LOCATION_FINANCING;
    }

    /**
     * Get button location string for marketing page
     *
     * @return string
     */
    public function getMarketingLocation()
    {
        return self::BUTTON_LOCATION_MARKETING;
    }

    /**
     * Get button location string for category page
     *
     * @return string
     */
    public function getCategoryPageLocation()
    {
        return self::BUTTON_LOCATION_CATEGORY;
    }

    /**
     * Get button location string for other purposes
     *
     * @return string
     */
    public function getOtherLocation()
    {
        return self::BUTTON_LOCATION_OTHER;
    }

    public function log($data, $logFile = 'bread-payment.log')
    {
        if ($this->logEnabled()) {
            Mage::log($data, null, $logFile);
        }
    }
}
