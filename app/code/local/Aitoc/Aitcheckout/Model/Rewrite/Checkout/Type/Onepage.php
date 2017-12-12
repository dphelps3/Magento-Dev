<?php
/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate% */
if (Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')) {
    class Aitoc_Aitcheckout_Model_Rewrite_Checkout_Type_Onepage_Aittmp
        extends AdjustWare_Deliverydate_Model_Rewrite_FrontCheckoutTypeOnepage
    {
    }
} else {
    /* default extends start */

    class Aitoc_Aitcheckout_Model_Rewrite_Checkout_Type_Onepage_Aittmp extends Mage_Checkout_Model_Type_Onepage
    {
    }
    /* default extends end */
}

/* AITOC static rewrite inserts end */

class Aitoc_Aitcheckout_Model_Rewrite_Checkout_Type_Onepage
    extends Aitoc_Aitcheckout_Model_Rewrite_Checkout_Type_Onepage_Aittmp
{

    public function getQuote()
    {
        $quote  = parent::getQuote();
        $action = Mage::app()->getRequest()->getActionName();
        if ('saveOrder' == $action) {
            if (!$quote->validateMinimumAmount()) {
                $quote->setHasError(true);
            }
        }

        return $quote;
    }

    public function saveCustomReview($data)
    {
        Mage::helper('aitcheckout/aitcheckoutfields')->saveCustomData($data);
    }


}