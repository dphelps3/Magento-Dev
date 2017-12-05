<?php

class NextBits_FormBuilder_Block_Capcha_Zend extends Mage_Core_Block_Template
{
    
   
    protected $_template = 'captcha/zend.phtml';

    /**
     * @var string
     */
    protected $_captcha;

    /**
     * Returns template path
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->getIsAjax() ? '' : $this->_template;
    }

    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     */
    public function getRefreshUrl()
    {
        return Mage::getUrl(
            Mage::app()->getStore()->isAdmin() ? 'adminhtml/refresh/refresh' : 'formbuilder/index/capcha',
            array('_secure' => Mage::app()->getStore()->isCurrentlySecure())
        );
    }

    /**
     * Renders captcha HTML (if required)
     *
     * @return string
     */
    protected function _toHtml()
    {
        //if ($this->getCaptchaModel()->isRequired()) {
            $this->getCaptchaModel()->generate();
            return parent::_toHtml();
        //}
       // return '';
    }

    /**
     * Returns captcha model
     *
     * @return Mage_Captcha_Model_Abstract
     */
    public function getCaptchaModel()
    {
        return Mage::helper('captcha')->getCaptcha($this->getFormId());
    }


}
