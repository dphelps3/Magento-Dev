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
 * Class Magestore_Sociallogin_Model_Mplogin
 */
class Magestore_Sociallogin_Model_Mplogin extends Mage_Core_Model_Abstract
{

    //static public $token;

    /**
     * @param null $token
     * @return OAuth1Client
     */
    public function newMp($token = null) {

        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Author' . DS . 'OAuth.php';
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Author' . DS . 'OAuth1Client.php';
        } catch (Exception $e) {
        }
        try {
            if ($token) {
                $mp = new OAuth1Client(
                    Mage::helper('sociallogin')->getMpConsumerKey(),
                    Mage::helper('sociallogin')->getMpConsumerSecret(),
                    $token['oauth_token'],
                    $token['oauth_token_secret']
                );
            } else {
                $mp = new OAuth1Client(
                    Mage::helper('sociallogin')->getMpConsumerKey(),
                    Mage::helper('sociallogin')->getMpConsumerSecret()
                );
            }
            $mp->api_base_url = "http://api.myspace.com/v1/";
            $mp->authorize_url = "http://api.myspace.com/authorize";
            $mp->request_token_url = "http://api.myspace.com/request_token";
            $mp->access_token_url = "http://api.myspace.com/access_token";
            return $mp;
        } catch (Exception $e) {
            return null;
        }
    }


    /**
     * @return string
     */
    public function getUrlAuthorCode() {
        $mp = $this->newMp();
        $token = $mp->requestToken(Mage::helper('sociallogin')->getAuthUrlMp());
        Mage::getSingleton('core/session')->setRequestToken($token);
        return $mp->authorizeUrl($token);
    }
}

  
