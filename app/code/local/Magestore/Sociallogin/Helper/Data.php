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
class Magestore_Sociallogin_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param $email
     * @param $website_id
     * @return mixed
     */
    public function getCustomerByEmail($email, $website_id) {
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('email', $email);
        // ->addFieldToFilter('website_id',$website_id)//add edition
        // ->getFirstItem();
        if (Mage::getStoreConfig('customer/account_share/scope')) {
            $collection->addFieldToFilter('website_id', $website_id);
        }
        return $collection->getFirstItem();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createCustomer($data) {
        $customer = Mage::getModel('customer/customer')
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email']);

        $newPassword = $customer->generatePassword();
        $customer->setPassword($newPassword);
        try {
            $customer->save();
        } catch (Exception $e) {
        }

        return $customer;
    }

    /**
     * @param $data
     * @param $website_id
     * @param $store_id
     * @return mixed
     */
    public function createCustomerMultiWebsite($data, $website_id, $store_id) {
        $customer = Mage::getModel('customer/customer')->setId(null);
        $customer->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setWebsiteId($website_id)
            ->setStoreId($store_id)
            ->save();
        $newPassword = $customer->generatePassword();
        $customer->setPassword($newPassword);
        try {
            $customer->save();
        } catch (Exception $e) {
        }
        return $customer;
    }

    /**
     * @return string
     */
    public function getTwConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/twlogin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getTwConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/twlogin/consumer_secret'));
    }


    /**
     * @return string
     */
    public function getYaAppId() {
        return trim(Mage::getStoreConfig('sociallogin/yalogin/app_id'));
    }

    /**
     * @return string
     */
    public function getYaConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/yalogin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getYaConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/yalogin/consumer_secret'));
    }

    /**
     * @return string
     */
    public function getGoConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/gologin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getGoConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/gologin/consumer_secret'));
    }
    //by King140115
    /**
     * @return string
     */
    public function getVkAppId() {
        return trim(Mage::getStoreConfig('sociallogin/vklogin/app_id'));
    }

    /**
     * @return string
     */
    public function getVkSecureKey() {
        return trim(Mage::getStoreConfig('sociallogin/vklogin/secure_key'));
    }
    //end by King140115

    /**
     * @return string
     */
    public function getAmazonId() {
        return trim(Mage::getStoreConfig('sociallogin/amazonlogin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getAmazonSecret() {
        return trim(Mage::getStoreConfig('sociallogin/amazonlogin/consumer_secret'));
    }

    /**
     * @return string
     */
    public function getAmazonUrlcallback() {
        return trim(Mage::getStoreConfig('sociallogin/amazonlogin/redirect_url'));
    }

    /**
     * @return string
     */
    public function getFbAppId() {
        return trim(Mage::getStoreConfig('sociallogin/fblogin/app_id'));
    }

    /**
     * @return string
     */
    public function getFbAppSecret() {
        return trim(Mage::getStoreConfig('sociallogin/fblogin/app_secret'));
    }

    /**
     * @return mixed
     */
    public function getAuthUrl() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('sociallogin/fblogin/login', array('_secure' => $isSecure, 'auth' => 1));
    }

    /**
     * @return mixed
     */
    public function getDirectLoginUrl() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('sociallogin/fblogin/login', array('_secure' => $isSecure));
    }

    /**
     * @return mixed
     */
    public function getLoginUrl() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('customer/account/login', array('_secure' => $isSecure));
    }

    /**
     * @return mixed
     */
    public function getEditUrl() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('customer/account/edit', array('_secure' => $isSecure));
    }

    // by Hai.Ta
    /**
     * @return string
     */
    public function getFqAppkey() {
        return trim(Mage::getStoreConfig('sociallogin/fqlogin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getFqAppSecret() {
        return trim(Mage::getStoreConfig('sociallogin/fqlogin/consumer_secret'));
    }

    /**
     * @return string
     */
    public function getLiveAppkey() {
        return trim(Mage::getStoreConfig('sociallogin/livelogin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getLiveAppSecret() {
        return trim(Mage::getStoreConfig('sociallogin/livelogin/consumer_secret'));
    }

    /**
     * @return string
     */
    public function getMpConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/mplogin/consumer_key'));
    }

    /**
     * @return string
     */
    public function getMpConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/mplogin/consumer_secret'));
    }

    /**
     * @return mixed
     */
    public function getAuthUrlFq() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('sociallogin/fqlogin/login', array('_secure' => $isSecure, 'auth' => 1));
    }

    /**
     * @return mixed
     */
    public function getAuthUrlLive() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('sociallogin/livelogin/login', array('_secure' => $isSecure, 'auth' => 1));
    }

    /**
     * @return mixed
     */
    public function getAuthUrlMp() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('sociallogin/mplogin/login', array('_secure' => $isSecure, 'auth' => 1));
    }

    /**
     * @return string
     */
    public function getLinkedConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/linklogin/app_id'));
    }

    /**
     * @return string
     */
    public function getLinkedConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/linklogin/secret_key'));
    }

    /**
     * @param $url
     * @return mixed|string
     */
    public function getResponseBody($url) {
        if (ini_get('allow_url_fopen') != 1) {
            @ini_set('allow_url_fopen', '1');
        }
        if (ini_get('allow_url_fopen') == 1) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $contents = curl_exec($ch);
            curl_close($ch);
        } else {
            $contents = file_get_contents($url);
        }

        return $contents;
    }

    /**
     * @return array
     */
    public function getShownPositions() {
        $shownpositions = Mage::getStoreConfig('sociallogin/general/position', Mage::app()->getStore()->getId());
        $shownpositions = explode(',', $shownpositions);
        return $shownpositions;
    }

    /**
     * @param $result
     * @return string
     */
    public function getPerResultStatus($result) {
        $result = str_replace(array('{', '}', '"', ':'), array('', '', '', ','), $result);
        $rs = explode(",", $result);
        if ($rs[10]) {
            return $rs[10];
        } else {
            return "";
        }
    }

    /**
     * @param $result
     * @return string
     */
    public function getPerEmail($result) {
        $result = str_replace(array('"', ':'), array('', ','), $result);
        $rs = explode(",", $result);
        if ($rs[8]) {
            return $rs[8];
        } else {
            return "";
        }
    }

    /**
     * @return string
     */
    public function returnlayout() {
        return '&nbsp;&lt;block name="featuredbrandflexiblebox" type="sociallogin/featuredbrand" template="sociallogin/featuredbrandflexiblebox.phtml"/&gt<br/>';
    }

    /**
     * @return string
     */
    public function returnblock() {
        return '&nbsp;&nbsp{{block type="sociallogin/featuredbrand" template="sociallogin/featuredbrandflexiblebox.phtml"}}<br>';
    }

    /**
     * @return string
     */
    public function returntext() {
        return 'Besides the Brand Listing page, you can show the Featured Brands box in other places by using the following options (recommended for developers)';
    }

    /**
     * @return string
     */
    public function returntemplate() {
        return "&nbsp;&nbsp;\$this->getLayout()->createBlock('sociallogin/featuredbrand')->setTemplate('sociallogin/featuredbrandflexiblebox.phtml')<br/>&nbsp;&nbsp;->tohtml();";
    }
}