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

require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Oauth2' . DS . 'service' . DS . 'Google_ServiceResource.php';
require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Oauth2' . DS . 'service' . DS . 'Google_Service.php';
require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Oauth2' . DS . 'service' . DS . 'Google_Model.php';
require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Oauth2' . DS . 'contrib' . DS . 'Google_Oauth2Service.php';
require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Oauth2' . DS . 'Google_Client.php';

/**
 * Class Magestore_Sociallogin_Model_Gologin
 */
class Magestore_Sociallogin_Model_Gologin extends Google_Client
{

    /**
     * @var null
     */
    protected $_options = null;

    /**
     * Magestore_Sociallogin_Model_Gologin constructor.
     */
    public function __construct() {
        $this->_config = new Google_Client;
        $this->_config->setClientId(Mage::helper('sociallogin')->getGoConsumerKey());
        $this->_config->setClientSecret(Mage::helper('sociallogin')->getGoConsumerSecret());
        $this->_config->setRedirectUri(Mage::app()->getStore()->getUrl('sociallogin/gologin/user', array('_secure' => true)));
    }
}
  
