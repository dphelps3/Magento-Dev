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
 * Class Magestore_Sociallogin_Model_Instagramlogin
 */
class Magestore_Sociallogin_Model_Instagramlogin extends Mage_Core_Model_Abstract
{
    /**
     * @return Instagram
     */
    public function newInstagram() {
        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'instagram' . DS . 'instagram.class.php';
        } catch (Exception $e) {
        }

        $instagram = new Instagram(array(
            'apiKey' => trim(Mage::getStoreConfig('sociallogin/instalogin/consumer_key')),
            'apiSecret' => trim(Mage::getStoreConfig('sociallogin/instalogin/consumer_secret')),
            'apiCallback' => Mage::app()->getStore()->getUrl('sociallogin/instagramlogin/login/', array('_secure' => true)), // must point to success.php
        ));
        return $instagram;
    }

    /**
     * @return string
     */
    public function getInstagramLoginUrl() {
        return $this->newInstagram()->getLoginUrl();
    }
}
  
