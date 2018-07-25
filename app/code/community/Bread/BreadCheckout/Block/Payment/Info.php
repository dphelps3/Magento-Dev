<?php
/**
 * Payment Info Block
 *
 * @author  Bread   copyright   2016
 * @author  Joel    @Mediotype
 * @author  Miranda @Mediotype
 */
class Bread_BreadCheckout_Block_Payment_Info extends Mage_Payment_Block_Info
{
    /**
     * Display Information For Admin View
     *
     * @param null $transport
     * @return null|Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);

        $transport->addData(
            array(
                Mage::helper('breadcheckout')->__('Financing Tx Id') => $this->getTransactionId()
            )
        );

        return $transport;
    }

    /**
     * Get Bread Transaction ID
     *
     * @return string
     */
    protected function getTransactionId()
    {
        $info = unserialize($this->getInfo()->getAdditionalData());

        if (isset($info['transaction_id'])) {
            return $info['transaction_id'];
        } elseif (Mage::getSingleton('checkout/session')->getBreadTransactionId()) {
            return Mage::getSingleton('checkout/session')->getBreadTransactionId();
        } else {
            return Mage::helper('breadcheckout')->__('N/A');
        }
    }
}
