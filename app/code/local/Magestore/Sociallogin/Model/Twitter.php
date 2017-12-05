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
 * Class Magestore_Sociallogin_Model_Twitter
 */
class Magestore_Sociallogin_Model_Twitter extends Zend_Service_Twitter
{
    /**
     * Show extended information on a user
     *
     * @param  int|string $id User ID or name
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return stdClass
     */
    public function userShow($id) {
        $this->_init();
        $path = '1.1/users/show.json';
        $response = $this->_get($path, array('id' => $id));
        return Zend_Json::decode($response->getBody(), Zend_Json::TYPE_OBJECT);
    }
}
