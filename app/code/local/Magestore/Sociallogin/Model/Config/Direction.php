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

class Magestore_Sociallogin_Model_Config_Direction
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'left', 'label'=>Mage::helper('adminhtml')->__('Left to Right')),
            array('value' => 'right', 'label'=>Mage::helper('adminhtml')->__('Right to Left')),
		);
    }
}