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
 * Class Magestore_Sociallogin_Model_Livelogin
 */
class Magestore_Sociallogin_Model_Livelogin extends Mage_Core_Model_Abstract
{
    /**
     * @return OAuth2Client
     */
    public function newLive() {
        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Author' . DS . 'OAuth2Client.php';
        } catch (Exception $e) {
        }
        try {
            $live = new OAuth2Client(
                Mage::helper('sociallogin')->getLiveAppkey(),
                Mage::helper('sociallogin')->getLiveAppSecret(),
                Mage::helper('sociallogin')->getAuthUrlLive()
            );
            $live->api_base_url = "https://apis.live.net/v5.0/";
            $live->authorize_url = "https://login.live.com/oauth20_authorize.srf";
            $live->token_url = "https://login.live.com/oauth20_token.srf";
            $live->out = "https://login.live.com/oauth20_logout.srf";
            return $live;
        } catch (Exception $e) {
            return null;
        }

    }

    /**
     * @return string
     */
    public function getUrlAuthorCode() {
        $live = $this->newLive();
        return $live->authorizeUrl();
    }
}

  
