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
 * Class Magestore_Sociallogin_Model_Wplogin
 */
class Magestore_Sociallogin_Model_Wplogin extends Mage_Core_Model_Abstract
{

    /**
     * @return LightOpenID
     */
    public function newWp() {
        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'OpenId' . DS . 'openid.php';
        } catch (Exception $e) {
        }

        $openid = new LightOpenID(Mage::app()->getStore()->getUrl());
        return $openid;
    }

    /**
     * @param $name_blog
     * @return null
     */
    public function getWpLoginUrl($name_blog) {
        $wp_id = $this->newWp();
        $wp = $this->setWpIdlogin($wp_id, $name_blog);
        try {
            $loginUrl = $wp->authUrl();
            return $loginUrl;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param $openid
     * @param $name_blog
     * @return mixed
     */
    public function setWpIdlogin($openid, $name_blog) {

        $openid->identity = 'http://' . $name_blog . '.wordpress.com';
        $openid->required = array(
            'namePerson/first',
            'namePerson/last',
            'namePerson/friendly',
            'contact/email',
        );

        $openid->returnUrl = Mage::app()->getStore()->getUrl('sociallogin/wplogin/login');
        return $openid;
    }
}
  
