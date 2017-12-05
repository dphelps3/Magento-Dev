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
 * Class Magestore_Sociallogin_Model_Selogin
 */
class Magestore_Sociallogin_Model_Selogin extends Mage_Core_Model_Abstract
{

    /**
     * @return LightOpenID
     */
    public function newSe() {
        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'OpenId' . DS . 'openid.php';
        } catch (Exception $e) {
        }

        $openid = new LightOpenID(Mage::app()->getStore()->getUrl());
        return $openid;
    }

    /**
     * @param $name
     * @return null
     */
    public function getSeLoginUrl($name) {
        $aol_id = $this->newSe();
        $aol = $this->setSeIdlogin($aol_id, $name);
        try {
            $loginUrl = $aol->authUrl();
            return $loginUrl;
        } catch (Exception $e) {
            return null;
        }

    }

    /**
     * @param $openid
     * @param $name
     * @return mixed
     */
    public function setSeIdlogin($openid, $name) {

        $openid->identity = 'https://openid.stackexchange.com';
        $openid->required = array(
            'namePerson/first',
            'namePerson/last',
            'namePerson/friendly',
            'contact/email',
        );

        $openid->returnUrl = Mage::app()->getStore()->getUrl('sociallogin/selogin/login');
        return $openid;
    }

}
  
