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
 * Class Magestore_Sociallogin_Model_Authorlogin
 */
class Magestore_Sociallogin_Model_Authorlogin extends Mage_Core_Model_Abstract
{
    /**
     *
     */
    public function _construct() {
        // Note that the membership_id refers to the key field in your database table.
        $this->_init('sociallogin/authorlogin', 'author_customer_id');
    }

    /**
     * @param null $authorId
     * @return bool
     */
    public function addCustomer($authorId = null) {
        $customer = Mage::getModel('customer/customer')->getCollection()
            ->getLastItem();
        $customer_id = $customer->getId();
        $model = Mage::getModel('sociallogin/authorlogin');
        $model->setData('author_id', $authorId)
            ->setData('customer_id', $customer_id)
            ->save();
        return true;
    }

    /**
     * @param $authorId
     * @return mixed
     */
    public function checkCustomer($authorId) {
        $model = Mage::getModel('sociallogin/authorlogin')->getCollection()
            ->addFieldToFilter('author_id', $authorId)
            ->getLastItem();
        return $model->getData('customer_id');
    }
}