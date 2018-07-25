<?php
/**
 * Class Bread_BreadCheckout_Model_Payment_Api_Client
 *
 * @author  Bread   copyright   2016
 * @author  Joel    @Mediotype
 */
class Bread_BreadCheckout_Model_Payment_Api_Client
{
    const STATUS_AUTHORIZED     = 'AUTHORIZED';
    const STATUS_SETTLED        = 'SETTLED';
    const STATUS_PENDING        = 'PENDING';
    const STATUS_CANCELED       = 'CANCELED';

    protected $_order    = null;

    /**
     * @param Mage_Sales_Model_Order $payment
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
    }
    
    
    /**
     * Call API Cancel Method
     *
     * @param $breadTransactionId
     * @param int $amount
     * @param array $lineItems
     * @throws Exception
     */
    public function cancel($breadTransactionId, $amount = 0, $lineItems = array())
    {
        $data = array('type'   => 'cancel');

        if (!$amount == 0) {
            $data['amount'] = $amount;
        }

        if (!empty($lineItems)) {
            $data['lineItems'] = $lineItems;
        }

        $result = $this->call($this->getUpdateTransactionUrl($breadTransactionId), $data);

        if ($result->status != self::STATUS_CANCELED) {
            Mage::helper('breadcheckout')
                ->log(array("ERROR"=>"Transaction cancel failed", "RESULT"=>$result), 'bread-exception.log');
            Mage::throwException(
                Mage::helper('breadcheckout')
                ->__('Transaction cancel failed (current transaction status :' . $result->status . ')')
            );
        }

        return $result;
    }

    /**
     * Call API Authorize Method
     *
     * @param $breadTransactionId
     * @param $amount
     * @param null $merchantOrderId
     * @return mixed
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function authorize($breadTransactionId, $amount, $merchantOrderId = null)
    {
        $dataArray = array('type' => 'authorize');
        if ($merchantOrderId != null) {
            $dataArray['merchantOrderId'] = $merchantOrderId;
        }

        $result = $this->call(
            $this->getUpdateTransactionUrl($breadTransactionId),
            $dataArray
		);

        if ($result->status != self::STATUS_AUTHORIZED && !$this->isSplitPaymentCCFail($result->description))
        {
            Mage::helper('breadcheckout')
                ->log(array("ERROR"=>"AUTHORIZATION FAILED", "RESULT"=>$result), 'bread-exception.log');
            Mage::throwException(
                Mage::helper('breadcheckout')
                ->__('Transaction authorize failed (current transaction status :' . $result->status . ')')
            );
        }

        $breadAmount = $result->total;
        if (( (int)$breadAmount != (int)$amount ) && ( abs( (int)$breadAmount - (int)$amount ) >= 2 ) && !$this->isSplitPaymentCCFail($result->description)) {
            Mage::helper('breadcheckout')->log(
                array("ERROR"=>"BREAD AMOUNT AND QUOTE AMOUNT MIS-MATCH",
                "BREAD AMOUNT"=>(int)$breadAmount ,"QUOTE AMOUNT"=>(int)$amount ,
                "RESULT"=>$result), 'bread-exception.log'
            );
            Mage::throwException(
                Mage::helper('breadcheckout')
                ->__('Bread authorized amount ' . $breadAmount . ' but transaction expected ' . $amount)
            );
        }

        return $result;
    }

    /**
     * Call API Update Order Id
     *
     * @param $breadTransactionId
     * @param $merchantOrderId
     * @return mixed
     * @throws Exception
     */
    public function updateOrderId($breadTransactionId, $merchantOrderId)
    {
        $result = $this->call(
            $this->getTransactionInfoUrl($breadTransactionId),
            array('merchantOrderId' => $merchantOrderId),
            Zend_Http_Client::PUT
        );

        return $result;
    }

    /**
     * Call API Update Order Id Capture Authorized Transaction Method
     *
     * @param $breadTransactionId
     * @return mixed
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function settle($breadTransactionId)
    {
        $result = $this->call(
            $this->getUpdateTransactionUrl($breadTransactionId),
            array('type' => 'settle')
        );

        if ($result->status != self::STATUS_SETTLED) {
            Mage::throwException(
                Mage::helper('breadcheckout')
                ->__('Transaction settle failed (current transaction status :' . $result->status . ')')
            );
        }

        return $result;
    }

    /**
     * Call API Refund Method
     *
     * @param $breadTransactionId
     * @param int $amount
     * @param array $lineItems
     * @return mixed
     * @throws Exception
     */
    public function refund($breadTransactionId, $amount = 0, $lineItems = array())
    {
        $data = array('type'   => 'refund');

        if (!$amount == 0) {
            $data['amount'] = $amount;
        }

        if (!empty($lineItems)) {
            $data['lineItems'] = $lineItems;
        }

        return $this->call($this->getUpdateTransactionUrl($breadTransactionId), $data);
    }

    /**
     * Call API Get Info Method
     *
     * @param $breadTransactionId
     * @return mixed
     * @throws Exception
     */
    public function getInfo($breadTransactionId)
    {
        return $this->call(
            $this->getTransactionInfoUrl($breadTransactionId),
            array(),
            Zend_Http_Client::GET
        );
    }

    /**
     * Submit cart data
     *
     * @param $data
     * @return string
     * @throws Exception
     */
    public function submitCartData($data)
    {
        return $this->call(
            Mage::helper("breadcheckout")->getCartCreateApiUrl(),
            $data,
            Zend_Http_Client::POST
        );
    }

    /**
     * Interact with the API
     *
     * @param $url
     * @param array $data
     * @param string $method
     * @return mixed
     * @throws Exception
     */
    protected function call($url, array $data, $method = Zend_Http_Client::POST)
    {
        $storeId = $this->getStoreId();
        $username   = Mage::helper('breadcheckout')->getApiPublicKey($storeId);
        $password   = Mage::helper('breadcheckout')->getApiSecretKey($storeId);

        try {
            $clientUrl = $url;
            $jsonData = Mage::helper('core')->jsonEncode($data);
            $curl = new Varien_Http_Adapter_Curl();
            $config = array(
                'timeout'   => 30,
                'userpwd'   => $username . ":" . $password,
                'header'    => 0,
                'verifypeer'=> false,
                'verifyhost'=> false
            );
            $options = array();
            $curl->setConfig($config);

            $headers= array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen(Mage::helper('core')->jsonEncode($data))
            );
            if ($method == Zend_Http_Client::POST) {
                $options[CURLOPT_POST] = 1;
                $options[CURLOPT_POSTFIELDS] = $jsonData;
                $curl->setOptions($options);
                $curl->write(Zend_Http_Client::POST, $clientUrl, '1.1', $headers, $jsonData);
            } elseif ($method == Zend_Http_Client::PUT) {
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $options[CURLOPT_POSTFIELDS] = $jsonData;
                $curl->setOptions($options);
                $curl->write(Zend_Http_Client::POST, $clientUrl, '1.1', $headers, $jsonData);
            } else {
                $curl->write(Zend_Http_Client::GET, $clientUrl, '1.1', array(), '');
            }

            $result = $curl->read();
            $status = $curl->getInfo(CURLINFO_HTTP_CODE);

            if ($status != 200 && !$this->isSplitPaymentCCFail($result)) {
                Mage::throwException(Mage::helper('breadcheckout')->__('Call to Bread API failed.  Error: '. $result));
            }
        } catch (Exception $e){
            Mage::helper('breadcheckout')->log(
                array(
                    "USER"      => $username,
                    "PASSWORD"  => $password,
                    "URL"       => $url,
                    "DATA"      => $data,
                    "RESULT"    => $result,
                ), 'bread-exception.log'
            );

            $curl->close();
            throw $e;
        }

        $curl->close();

        Mage::helper('breadcheckout')->log(
            array(
                "USER"      => $username,
                "PASSWORD"  => $password,
                "URL"       => $url,
                "DATA"      => $data,
                "RESULT"    => $result,
            )
        );

        if (!$this->isJson($result)) {
            Mage::throwException(
                Mage::helper('breadcheckout')
                ->__('API Response Is Not Valid JSON.  Result: ' . $result)
            );
        }

        if($this->isSplitPaymentCCFail($result)){
            $res = json_decode($result,true);
            $res['breadTransactionId'] = Mage::getSingleton('checkout/session')
                ->getBreadTransactionId();
            $result = json_encode($res);
            Mage::getSingleton('checkout/session')
                ->setBreadSplitCCFailed(true);
        }

        return json_decode($result);
    }

    /**
     * Form transaction info URI string
     *
     * @param $transactionId
     * @return string
     */
    protected function getTransactionInfoUrl($transactionId)
    {
        $baseUrl = Mage::helper('breadcheckout')->getTransactionApiUrl($this->getStoreId());
        return join('/', array(trim($baseUrl, '/'), 'transactions', trim($transactionId, '/')));
    }

    /**
     * Send cart sms
     *
     * @param $transactionId
     * @param $phone
     * @return string
     */
    public function sendSms($transactionId, $phone)
    {
        $baseUrl = Mage::helper('breadcheckout')->getTransactionApiUrl($this->getStoreId());
        $sendSmsUrl = join('/', array(trim($baseUrl, '/'), 'carts', trim($transactionId, '/'), 'text'));
        $data = array('phone' => $phone);
        return $this->call(
            $sendSmsUrl,
            $data,
            Zend_Http_Client::POST
        );
    }

    /**
     * Send cart email
     *
     * @param $transactionId
     * @param $email
     * @param $name
     * @return string
     */
    public function sendEmail($transactionId, $email, $name)
    {
        $baseUrl = Mage::helper('breadcheckout')->getTransactionApiUrl($this->getStoreId());
        $sendEmailUrl = join('/', array(trim($baseUrl, '/'), 'carts', trim($transactionId, '/'), 'email'));
        $data = array('email' => $email, 'name' => $name);
        return $this->call(
            $sendEmailUrl,
            $data,
            Zend_Http_Client::POST
        );
    }

    /**
     * Form update transaction URI string
     * @param $transactionId
     * @return string
     */
    protected function getUpdateTransactionUrl($transactionId)
    {
        $baseUrl = Mage::helper('breadcheckout')->getTransactionApiUrl($this->getStoreId());
        return join('/', array(trim($baseUrl, '/'), 'transactions/actions', trim($transactionId, '/')));
    }

    /**
     * Check a string to verify JSON format is valid
     *
     * @param $string
     * @return bool
     */
    protected function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        if (Mage::app()->getStore()->isAdmin() == false) {
            return Mage::app()->getStore()->getId();
        }

        //if we don't have a payment model, return default store
        if (!isset($this->_order)) {
            return 0;
        }

        return $this->_order->getData('store_id');
    }

    /**
     * Check if result we get is a split payment cc fail
     *
     * @param $string
     * @return bool
     */
    protected function isSplitPaymentCCFail($string)
    {
        return (strpos($string, "There's an issue with authorizing the credit card portion of this transaction due to this reason: \"Charge was declined - This card could not be charged.\" Please have the customer reach out to the bank. Once the transaction is approved by the bank, try reauthorizing in 1-3 business days.") !== false);
    }
}
