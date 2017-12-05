<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Class Magestore_Sociallogin_Model_Twlogin
 */
class Magestore_Sociallogin_Model_Twlogin extends Zend_Oauth_Consumer
{

    /**
     * @var array|null
     */
    protected $_options = null;

    /**
     * Magestore_Sociallogin_Model_Twlogin constructor.
     */
    public function __construct() {
        $this->_config = new Zend_Oauth_Config;
        $this->_options = array(
            'consumerKey' => Mage::helper('sociallogin')->getTwConsumerKey(),
            'consumerSecret' => Mage::helper('sociallogin')->getTwConsumerSecret(),
            //'siteUrl'           => 'http://localhost/oss/magento14_3/index.php',
            'signatureMethod' => 'HMAC-SHA1',
            'version' => '1.0',
            'requestTokenUrl' => 'https://api.twitter.com/oauth/request_token',
            'accessTokenUrl' => 'https://api.twitter.com/oauth/access_token',
            'authorizeUrl' => 'https://api.twitter.com/oauth/authorize'
        );

        $this->_config->setOptions($this->_options);
    }

    /**
     * @param $url
     */
    public function setCallbackUrl($url) {
        $this->_config->setCallbackUrl($url);
    }
}
  
