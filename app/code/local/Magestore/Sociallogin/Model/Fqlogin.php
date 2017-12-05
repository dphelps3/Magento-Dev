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
 * Class Magestore_Sociallogin_Model_Fqlogin
 */
class Magestore_Sociallogin_Model_Fqlogin extends Mage_Core_Model_Abstract
{
    /**
     * @return FoursquareApi
     */
    public function newFoursquare() {

        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'Foursquare' . DS . 'FoursquareAPI.class.php';
        } catch (Exception $e) {
        }

        $foursquare = new FoursquareApi(
            Mage::helper('sociallogin')->getFqAppkey(),
            Mage::helper('sociallogin')->getFqAppSecret(),
            urlencode(Mage::helper('sociallogin')->getAuthUrlFq())
        );
        return $foursquare;
    }

    /**
     * @return String
     */
    public function getFqLoginUrl() {
        $foursquare = $this->newFoursquare();
        $loginUrl = $foursquare->AuthenticationLink();
        return $loginUrl;
    }
}
  
