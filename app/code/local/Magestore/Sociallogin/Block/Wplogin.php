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

class Magestore_Sociallogin_Block_Wplogin extends Mage_Core_Block_Template
{
    /**
     * @return mixed
     */
    public function getLoginUrl(){
		return $this->getUrl('sociallogin/wplogin/login');
	}

    /**
     * @return mixed
     */
    public function getAlLoginUrl(){
        return $this->getUrl('sociallogin/wplogin/setBlogName');
    }

    /**
     * @return mixed
     */
    public function getCheckName(){
        return $this->getUrl('sociallogin/wplogin/setBlock');
    }

    /**
     * @return string
     */
    public function getEnterName(){
		return 'ENTER YOUR BLOG NAME';
	}

    /**
     * @return string
     */
    public function getName(){
		return 'Name';
	}
}