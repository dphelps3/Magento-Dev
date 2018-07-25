<?php
/**
 * Bread Finance Payment Method
 *
 * @method Bread_BreadCheckout_Model_Payment_Api_Client getApiClient()
 * @method setApiClient($value)
 *
 * @author  Bread   copyright   2016
 * @author  Joel    @Mediotype
 */
class Bread_BreadCheckout_Model_Payment_Method_Bread extends Mage_Payment_Model_Method_Abstract
{
    const ACTION_AUTHORIZE              = "authorize";
    const ACTION_AUTHORIZE_CAPTURE      = "authorize_capture";

    /* internal action types */
    const ACTION_CAPTURE                = "capture";
    const ACTION_REFUND                 = "refund";
    const ACTION_VOID                   = "void";

    protected $_code          = 'breadcheckout';
    protected $_formBlockType = 'breadcheckout/payment_form';
    protected $_infoBlockType = 'breadcheckout/payment_info';

    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canOrder                = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canSaveCc               = false;
    protected $_canReviewPayment        = true;
    protected $_allowCurrencyCode       = array('USD');

    protected $_apiClient = null;

    /**
     * Construct Sets API Client And Sets Available For Checkout Flag
     *
     */
    public function __construct()
    {
        $this->setApiClient(Mage::getModel('breadcheckout/payment_api_client'));
        $this->_canUseCheckout      = Mage::helper('breadcheckout')->isPaymentMethodAtCheckout();
    }

    /**
     * Fetch Payment Info
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string                  $transactionId
     * @return mixed
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        $this->getApiClient()->setOrder($payment->getOrder());
        return $this->getApiClient()->getInfo($transactionId);
    }

    /**
     * Validate Payment Method before allowing next step in checkout
     *
     * @return $this|Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        $paymentInfo   = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
             $billingCountry    = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
             $billingCountry    = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }

        if (!$this->canUseForCountry($billingCountry)) {
            Mage::helper('breadcheckout')->log("ERROR IN METHOD VALIDATE, INVALID BILLING COUNTRY". $billingCountry);
            Mage::throwException(
                Mage::helper('payment')
                ->__(
                    'This financing program is available to US residents, please click the finance button 
                and complete the application in order to complete your purchase with the financing payment method.'
                )
            );
        }

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
             $token    = Mage::getSingleton('checkout/session')->getBreadTransactionId();
        } else {
             $token    = Mage::getSingleton('checkout/session')->getBreadTransactionId();
        }

        if (empty($token)) {
            Mage::helper('breadcheckout')->log("ERROR IN METHOD VALIDATE, MISSING BREAD TOKEN");
            Mage::throwException(
                Mage::helper('payment')
                ->__(
                    'This financing program is unavailable, please complete the application. 
                If the problem persists, please contact us.'
                )
            );
        }

        return $this;
    }

    /**
     * Process Cancel Payment
     *
     * @param Varien_Object $payment
     * @return $this
     */
    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }

    /**
     * Process Void Payment
     *
     * @param Varien_Object $payment
     * @return Bread_BreadCheckout_Model_Payment_Method_Bread
     * @throws Mage_Core_Exception
     */
    public function void(Varien_Object $payment)
    {
        if (!$this->canVoid($payment)) {
            Mage::throwException(Mage::helper('payment')->__('Void action is not available.'));
        }

        return $this->_place($payment, 0, self::ACTION_VOID);
    }

    /**
     * Process Authorize Payment
     *
     * @param Varien_Object $payment
     * @param float         $amount
     * @return Bread_BreadCheckout_Model_Payment_Method_Bread
     * @throws Mage_Core_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
        }

        $payment->setAmount($amount);
        $payment->setIsTransactionClosed(false);
        $payment->setTransactionId(Mage::getSingleton('checkout/session')->getBreadTransactionId());

        $this->_place($payment, $amount, self::ACTION_AUTHORIZE);

        return $this;
    }

    /**
     * Set capture transaction ID to invoice for informational purposes
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }

    /**
     * Process Capture Payment
     *
     * @param Varien_Object $payment
     * @param float         $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        $paymentInfo   = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $address            = $paymentInfo->getOrder()->getBillingAddress();
            $quote              = Mage::getModel('sales/quote')->load($paymentInfo->getOrder()->getQuoteId());
        } else {
            $address            = $paymentInfo->getQuote()->getBillingAddress();
            $quote              = $address->getQuote();
        }

        if (Mage::helper('breadcheckout')->getPaymentAction() == self::ACTION_AUTHORIZE_CAPTURE) {
            $this->getApiClient()->setOrder($payment->getOrder());
            $token  = Mage::getSingleton('checkout/session')->getBreadTransactionId();
            $result     = $this->getApiClient()
                ->authorize($token, (int)round($amount * 100), $payment->getOrder()->getIncrementId());
            $payment->setTransactionId($result->breadTransactionId);
            $amount = $result->total;
        } else {
            $token  = $payment->getAuthorizationTransaction()->getTxnId();
        }

        $payment->setTransactionId($token);
        $payment->setAmount($amount);

        $this->_place($payment, $amount, self::ACTION_CAPTURE);

        return $this;
    }

    /**
     * Set transaction ID into creditmemo for informational purposes
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        $creditmemo->setTransactionId($payment->getLastTransId());

        return $this;
    }

    /**
     * Process Refund Payment
     *
     * @param Varien_Object $payment
     * @param float         $amount
     * @return Bread_BreadCheckout_Model_Payment_Method_Bread
     * @throws Mage_Core_Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            Mage::throwException(Mage::helper('payment')->__('Refund action is not available.'));
        }

        $this->_place($payment, $amount, self::ACTION_REFUND);

        return $this;
    }

    /**
     * Process API Call Based on Request Type And Add Normalized Magento Transaction Data To Orders
     *
     * @param $payment
     * @param $amount
     * @param $requestType
     */
    protected function _place($payment, $amount, $requestType)
    {
        $this->getApiClient()->setOrder($payment->getOrder());
        switch ($requestType) {
            case self::ACTION_AUTHORIZE:
                    $result     = $this->getApiClient()->authorize(
                        $this->getValidatedTxId($payment),
                        (int)round($amount * 100), $payment->getOrder()->getIncrementId()
                    );
                    $payment->setTransactionId($result->breadTransactionId);
                    $this->addTransaction(
                        $payment,
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                        array('is_closed' => false, 'authorize_result' => json_encode($result)),
                        array(),
                        "Bread Finance Payment Authorized"
                    );
                break;
            case self::ACTION_CAPTURE:
                    $result     = $this->getApiClient()->settle($this->getValidatedTxId($payment));
                    $payment->setTransactionId($result->breadTransactionId);
                    $this->addTransaction(
                        $payment,
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                        array('is_closed' => false, 'settle_result' => json_encode($result)),
                        array(),
                        "Bread Finance Payment Captured"
                    );
                break;
            case self::ACTION_REFUND:
                    $result     = $this->getApiClient()->refund(
                        $this->getValidatedTxId($payment, true),
                        (int)round($amount * 100)
                    );
                    $payment->setTransactionId($result->breadTransactionId);
                    $this->addTransaction(
                        $payment,
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,
                        array('is_closed' => false, 'refund_result' => json_encode($result)),
                        array(),
                        "Bread Finance Payment Refunded"
                    );
                break;
            case self::ACTION_VOID:
                    $result     = $this->getApiClient()->cancel($this->getValidatedTxId($payment));
                    $payment->setTransactionId($result->breadTransactionId);
                    $this->addTransaction(
                        $payment,
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,
                        array('is_closed' => true, 'cancel_result' => json_encode($result)),
                        array(),
                        "Bread Finance Payment Canceled"
                    );
                break;
            default:

                break;
        }

        $payment->setSkipTransactionCreation(true);

        return $result;
    }

    /**
     * Add payment transaction
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string $transactionType
     * @param array $transactionAdditionalInfo
     * @param array $transactionDetails
     * @param mixed $message
     * @return Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function addTransaction(
        Mage_Sales_Model_Order_Payment $payment,
        $transactionType,
        $transactionAdditionalInfo = array(),
        $transactionDetails = array(),
        $message = false
    ) 
    {
        $this->getInfoInstance()->setAdditionalData(
            serialize(array('transaction_id' => $payment->getTransactionId()))
        );
        $payment->resetTransactionAdditionalInfo();

        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }

        $transaction = $payment->addTransaction($transactionType, null, false, $message);
        $transaction->setOrderPaymentObject($payment);

        if (array_key_exists('is_closed', $transactionAdditionalInfo)) {
            $transaction->setIsClosed((bool) $transactionAdditionalInfo["is_closed"]);
        }

        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }

        Mage::getSingleton('checkout/session')->unsBreadTransactionId()
            ->unsBreadTransactionAmount();

        $transaction->setMessage($message);
        $transaction->save();

        return $transaction;
    }

    /**
     * Is the 'breadcheckout' payment method available
     *
     * @param null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (Mage::app()->getStore()->isAdmin()) {
            if (!Mage::getSingleton('admin/session')->isAllowed('bread_checkout')) {
                return false;
            }
        }

        if (!Mage::getSingleton('checkout/session')->getBreadTransactionId()) {
            return true;
        }

        if (!parent::isAvailable($quote)) {
            return false;
        }

        return true;
    }

    /**
     * Validates and sanitizes the given transaction ID for making
     * an API request to Bread
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param bool $useParent
     * @return string
     * @throws Exception
     */
    protected function getValidatedTxId(Mage_Sales_Model_Order_Payment $payment, $useParent = false)
    {
        if ($useParent === true) {
            $rawTransId = $payment->getParentTransactionId();
        } else {
            $rawTransId = $payment->getTransactionId();
        }

        if (preg_match('/^[a-z0-9]{8}-([a-z0-9]{4}-){3}[a-z0-9]{12}/', $rawTransId, $matches)) {
            return $matches[0];
        } else {
            Mage::helper('breadcheckout')->log(
                "INVALID TRANSACTION ID PROVIDED: ".
                $rawTransId, 'bread-exception.log'
            );
            Mage::throwException(
                Mage::helper('breadcheckout')
                ->__('Unable to process request because an invalid transaction ID was provided.')
            );
        }
    }

    /**
     * Returns payment method code
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_code;
    }
}
