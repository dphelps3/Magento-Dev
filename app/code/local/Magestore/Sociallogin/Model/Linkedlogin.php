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
 * Class Magestore_Sociallogin_Model_Linkedlogin
 */
class Magestore_Sociallogin_Model_Linkedlogin extends Zend_Oauth_Consumer
{
    /**
     * @var array|null
     */
    protected $_options = null;

    /**
     * Magestore_Sociallogin_Model_Linkedlogin constructor.
     */
    public function __construct() {
        $this->_config = new Zend_Oauth_Config;
        $this->_options = array(
            'consumerKey' => Mage::helper('sociallogin')->getLinkedConsumerKey(),
            'consumerSecret' => Mage::helper('sociallogin')->getLinkedConsumerSecret(),
            'version' => '1.0',
            'requestTokenUrl' => 'https://api.linkedin.com/uas/oauth/requestToken?scope=r_emailaddress',
            'accessTokenUrl' => 'https://api.linkedin.com/uas/oauth/accessToken',
            'authorizeUrl' => 'https://www.linkedin.com/uas/oauth/authenticate'
        );
        $this->_config->setOptions($this->_options);
    }

    /**
     * @param $url
     */
    public function setCallbackUrl($url) {
        $this->_config->setCallbackUrl($url);
    }

    /**
     * @return array|null
     */
    public function getOptions() {
        return $this->_options;
    }
}