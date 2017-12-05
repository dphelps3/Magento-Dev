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
 * Class Magestore_Sociallogin_Model_Observer
 */
class Magestore_Sociallogin_Model_Observer
{

    /**
     * @param $observer
     */
    public function customer_edit($observer) {
        try {
            $customerId = Mage::getSingleton('core/session')->getCustomerIdSocialLogin();
            if ($customerId) {
                Mage::getSingleton('customer/session')->getCustomer()->setEmail(' ');
            }
            Mage::getSingleton('core/session')->setCustomerIdSocialLogin();
        } catch (Exception $e) {
        }
    }

    /**
     * @param $observer
     */
    public function controller_action_predispatch_adminhtml($observer) {
        $controller = $observer->getControllerAction();
        if ($controller->getRequest()->getControllerName() != 'system_config'
            || $controller->getRequest()->getActionName() != 'edit'
        )
            return;
        $section = $controller->getRequest()->getParam('section');
        if ($section != 'sociallogin')
            return;
    }
}