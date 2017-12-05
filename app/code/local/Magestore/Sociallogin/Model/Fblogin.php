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
 * Class Magestore_Sociallogin_Model_Fblogin
 */
class Magestore_Sociallogin_Model_Fblogin extends Mage_Core_Model_Abstract
{
    /**
     * @return Facebook
     */
    public function newFacebook() {
        error_reporting(E_ALL ^ E_WARNING);
        error_reporting(E_ALL ^ E_NOTICE);
        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Facebook' . DS . 'facebook.php';
        } catch (Exception $e) {
        }

        $facebook = new Facebook(array(
            'appId' => Mage::helper('sociallogin')->getFbAppId(),
            'secret' => Mage::helper('sociallogin')->getFbAppSecret(),
            'cookie' => true,
        ));
        return $facebook;
    }

    /**
     * @return null|the
     */
    public function getFbUser() {
        $facebook = $this->newFacebook();
        $userId = $facebook->getUser();
        $fbme = NULL;

        if ($userId) {
            try {
                $fbme = $facebook->api('/me?fields=email,first_name,last_name');
            } catch (FacebookApiException $e) {
            }
        }

        return $fbme;
    }

    /**
     * @return String
     */
    public function getFbLoginUrl() {
        $facebook = $this->newFacebook();
        $loginUrl = $facebook->getLoginUrl(
            array(
                'display' => 'popup',
                'redirect_uri' => Mage::helper('sociallogin')->getAuthUrl(),
                'scope' => 'email',
            )
        );
        return $loginUrl;
    }
}
  
