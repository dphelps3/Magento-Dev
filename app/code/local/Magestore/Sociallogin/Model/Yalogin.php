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
 * Class Magestore_Sociallogin_Model_Yalogin
 */
class Magestore_Sociallogin_Model_Yalogin extends Mage_Core_Model_Abstract
{
    /**
     * Magestore_Sociallogin_Model_Yalogin constructor.
     */
    public function __construct() {
        require Mage::getBaseDir('lib') . DS . 'Yahoo' . DS . 'Yahoo.inc';
        error_reporting(E_ALL | E_NOTICE); # do not show notices as library is php4 compatable
        ini_set('display_errors', true);
        YahooLogger::setDebug(true);
        YahooLogger::setDebugDestination('LOG');
        // use memcache to store oauth credentials via php native sessions
        ini_set('session.save_handler', 'files');
        session_save_path('/tmp/');
        session_start();

        if (array_key_exists("logout", $_GET)) {
            YahooSession::clearSession();
            //$this->reloadPage();
        }
    }

    /**
     * @return bool
     */
    public function hasSession() {
        $consumerKey = $this->getConsumerKey();
        $consumerSecret = $this->getConsumerSecret();
        $appId = $this->getAppId();
        return YahooSession::hasSession($consumerKey, $consumerSecret, $appId);
    }

    /**
     * @return stdclass
     */
    public function getAuthUrl() {
        $consumerKey = $this->getConsumerKey();
        $consumerSecret = $this->getConsumerSecret();
        $callback = YahooUtil::current_url() . '?in_popup';
        return YahooSession::createAuthorizationUrl($consumerKey, $consumerSecret, $callback);
    }

    /**
     * @return YahooSession
     */
    public function getSession() {
        $consumerKey = $this->getConsumerKey();
        $consumerSecret = $this->getConsumerSecret();
        $appId = $this->getAppId();
        return YahooSession::requireSession($consumerKey, $consumerSecret, $appId);
    }

    /**
     * @return string
     */
    public function getConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/yalogin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/yalogin/consumer_secret'));
    }

    /**
     * @return string
     */
    public function getAppId() {
        return trim(Mage::getStoreConfig('sociallogin/yalogin/app_id'));
    }

}