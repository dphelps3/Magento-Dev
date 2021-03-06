<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/29/13
 * Time   : 12:05 PM
 * File   : Token.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_Token
{
    protected $_code = "sagepaymentspro";
    protected $_formBlockType = "ebizmarts_sagepaymentspro/payment_form_sagePaymentsProToken";

    public function isEnabled()
    {
        return (bool) (Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_TOKEN)!= false);
    }
}
