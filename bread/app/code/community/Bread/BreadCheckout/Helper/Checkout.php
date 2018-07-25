<?php
/**
 * Functions for use specifically for validating Bread payment with Magento checkout
 *
 * @author      Bread   copyright   2016
 * @author      Dale    @Mediotype
 */
class Bread_BreadCheckout_Helper_Checkout extends Bread_BreadCheckout_Helper_Data
{

    const BREAD_CONST = "bread_transaction_amount";

    /**
     * Save payment amount authorized by Bread to checkout session
     *
     * @param int $amount
     */
    public function setBreadTransactionAmount($amount)
    {
        $checkout = Mage::getSingleton('checkout/session');
        $checkout->setData($this::BREAD_CONST, $amount);
    }

    /**
     * Retrieve payment amount previously authorized by Bread
     *
     * @return int
     */
    public function getBreadTransactionAmount()
    {
        $amount = Mage::getSingleton('checkout/session')->getData($this::BREAD_CONST);

        return ($amount == null)?0:$amount;
    }

    /**
     * Verify that Magento's quote amount matches the amount
     * authorized by Bread
     *
     * @return bool
     */
    public function validateTransactionAmount($transactionId)
    {
        $breadAmount = $this->getBreadTransactionAmount();
        $quoteTotal  = Mage::helper('breadcheckout/quote')->getGrandTotal();
        
        if ($breadAmount == 0 || ((int)trim($breadAmount) != (int)trim($quoteTotal))) {
            $info = Mage::getModel('breadcheckout/payment_api_client')->getInfo($transactionId);
            $this->setBreadTransactionAmount($info->adjustedTotal);
            $breadAmount = $info->adjustedTotal;
        }

        return ((int)trim($breadAmount) == (int)trim($quoteTotal));
    }

}
