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

class Magestore_Sociallogin_Model_Config_Position
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'header', 'label'=>Mage::helper('adminhtml')->__('Header')),
            array('value' => 'before-customer-login', 'label'=>Mage::helper('adminhtml')->__('Above customer login form')),
            array('value' => 'after-customer-login', 'label'=>Mage::helper('adminhtml')->__('Below customer login form')),
            array('value' => 'before-customer-registration', 'label'=>Mage::helper('adminhtml')->__('Above customer registration form')),
            array('value' => 'after-customer-registration', 'label'=>Mage::helper('adminhtml')->__('Below customer registration form')),        
			array('value' => 'popup', 'label'=>Mage::helper('adminhtml')->__('Show popup when click login')),
		);
    }
}