<?php

class Aitoc_Aitcheckout_Block_Rewrite_PaypalPayflowAdvancedIframe extends Mage_Paypal_Block_Payflow_Advanced_Iframe
{

    public function setTemplate($template)
    {
        if (($template == 'paypal/payflowadvanced/redirect.phtml') && !Mage::helper('aitcheckout')->isDisabled()) {
            $template = 'aitcheckout/paypal/payflowadvanced/redirect.phtml';
        }

        return parent::setTemplate($template);
    }

    public function getAitCheckoutRedirectUrl()
    {
        if (Mage::helper('aitcheckout')->isShowCheckoutInCart()) {
            $url = $this->getUrl(Mage::helper('aitcheckout')->getCartUrl());
        } else {
            $url = $this->getUrl(Mage::helper('aitcheckout')->getCheckoutUrl());
        }

        return $url;
    }

}
