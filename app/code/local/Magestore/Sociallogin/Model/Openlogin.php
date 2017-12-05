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
 * Class Magestore_Sociallogin_Model_Openlogin
 */
class Magestore_Sociallogin_Model_Openlogin extends Mage_Core_Model_Abstract
{

    /**
     * @return LightOpenID
     */
    public function newMy() {
        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'OpenId' . DS . 'openid.php';
        } catch (Exception $e) {
        }
        $openid = new LightOpenID(Mage::app()->getStore()->getUrl());
        return $openid;
    }

    /**
     * @param $identity
     * @return mixed
     */
    public function getOpenLoginUrl($identity) {
        $my_id = $this->newMy();
        $my = $this->setOpenIdlogin($my_id, $identity);
        $loginUrl = $my->authUrl();
        return $loginUrl;
    }

    /**
     * @param $openid
     * @param $identity
     * @return mixed
     */
    public function setOpenIdlogin($openid, $identity) {
        $openid->identity = "http://" . $identity . ".myopenid.com";
        $openid->required = array(
            'namePerson/first',
            'namePerson/last',
            'namePerson/friendly',
            'contact/email',
            'namePerson'
        );
        $openid->returnUrl = Mage::app()->getStore()->getUrl('sociallogin/openlogin/login');
        return $openid;
    }
}
  
