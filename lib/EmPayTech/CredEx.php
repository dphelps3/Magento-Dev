<?php
/**
 * EmPayTech CredEx module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   EmPayTech
 * @package    EmPayTech_CredEx
 * @copyright  Copyright (c) 2012 EmPayTech
 * @author     Thomas Vander Stichele / EmPayTech <support@empaytech.net>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class CredEx
{
    /**
     * @param string $url      url to post requests to (ends in transact.cfm)
     * @param string $merch_id merchant id
     * @param string $username
     * @param string $password
     */
    public function __construct($url, $merch_id, $username, $password) {
        $this->url = $url;
        $this->merch_id = $merch_id;
        $this->_username = $username;
        $this->_password = $password;
    }

    public function getDataspace() {
        if (strpos($this->url, 'api-test') !== false) {
            return 'staging';
        } else {
            return 'production';
        }
    }

    public function log($msg) {
    }

    function request($fields) {
        $fields = array_merge($fields, array(
            'dg_username'=> $this->_username,
            'dg_password'=> $this->_password,
            'merch_id'=> $this->merch_id));

        $query = http_build_query($fields);
        $this->log("request: $query");
		
        $ch = curl_init($this->url);
		//var_dump($this->url);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_REFERER, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
        // FIXME: we have to set response_format
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //    "Accept: application/xml","Content-Type: text/xml"
        //));
		//curl_setopt($ch, CURLOPT_HEADER, 0); // DO NOT RETURN HTTP HEADERS
		//curl_setopt($ch, CURLINFO_HEADER_OUT, true); //Track handle's request string
        /* return contents of the call as a variable */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		
		//curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		//var_dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
		
        $result = curl_exec($ch);
		
		//print_r(curl_getinfo($ch)); 
        curl_close($ch);
		
        $response = $this->parseResponse($result);

        $this->log('response: ' . $response->asXML());
        return $response;
    }

    public function parseResponse($xmlString)
    {
        /* SimpleXMLElement returns an element proxying for the root <response>
           tag */
        $response = new SimpleXMLElement($xmlString);

        return $response;
    }

}

class CredEx_Magento extends CredEx
{
    public function __construct($paymentMethod) {

        $errors = FALSE;

        $keys = array("platform", "merch_id", "username", "password");

        foreach ($keys as $key) {
            $$key = $paymentMethod->getConfigData($key);
            if (!$$key) {
                $this->log("specify $key in the configuration");
                $errors = TRUE;
            }
        }

        $platforms = array('staging', 'platform');
        if (!in_array($platform, $platforms)) {
            $this->log("platform is set to unsupported value $platform");
            $errors = TRUE;
        }

        if ($errors) $this->misconfigured();

        $key = "gateway_url_$platform";
        $url = $paymentMethod->getConfigData($key);
        if (!$url) {
            $this->log("specify $key in the configuration");
            $errors = TRUE;
        }

        if ($errors) $this->misconfigured();

        parent::__construct($url, $merch_id, $username, $password);
    }

    /* parent class implementations */
    public function log($msg) {
        Mage::log("Credex: $msg");
    }

    private function misconfigured()
    {
        //Mage::throwException(Mage::helper('paygate')->__('The web store has misconfigured the Credex payment module.'));
    }

    /*
     * Mage_Sales_Model_Quote
     */
    public function request($quote)
    {
        $errors = FALSE;

        $billingaddress = $quote->getBillingAddress();
        $shippingaddress = $quote->getShippingAddress();
        $totals = number_format($quote->getGrandTotal(), 2, '.', '');

        $email = $quote->getCustomerEmail();
        $cust_id_ext = $quote->reserveOrderId()->getReservedOrderId();

        $this->log("request for total amount $totals and cust_id_ext $cust_id_ext");

        $fields = array(
            'cust_fname'=> $billingaddress->getData('firstname'),
            'cust_lname'=> $billingaddress->getData('lastname'),
            'cust_email'=> $email,

            'bill_addr_address'=> $billingaddress->getData('street'),
            'bill_addr_city'=> $billingaddress->getData('city'),
            'bill_addr_state'=> $billingaddress->getData('region'),
            'bill_addr_zip'=> $billingaddress->getData('postcode'),

            'ship_addr_address'=> $shippingaddress->getData('street'),
            'ship_addr_city'=> $shippingaddress->getData('city'),
            'ship_addr_state'=> $shippingaddress->getData('region'),
            'ship_addr_zip'=> $shippingaddress->getData('postcode'),

            'version' => '0.3',
            'response_format' => 'XML',
            // FIXME
            'cust_id_ext'=> $cust_id_ext,
            'inv_action' => 'AUTH_ONLY',
            'inv_value' => $totals,
            /* FIXME: description */
            'udf02' => 'Sample purchase'
        );
		
        $response = parent::request($fields);

        if ($response->status_code == "GW_NOT_AUTH") {
            $this->log("response status: " . $response->status_string);
            $this->log("Please verify your authentication details.");
            $this->misconfigured();
        }

        if ($response->status_code == "APPROVED") {
            /* store application url in session so our phtml can use them */
            $this->getSession()->setCredexApplicationURL((string) $response->udf02);
            $this->getSession()->setCredexCustId((string) $response->cust_id);
            $this->getSession()->setCredexInvId((string) $response->inv_id);
            /* store response values on quote too */
            // FIXME: these don't actually persist at all when saved
            //        get them from postback instead
            /*
            $quote->setCredexApplicationURL((string) $response->udf02);
            $quote->setCredexCustId((string) $response->cust_id);
            $quote->setCredexInvId((string) $response->inv_id);
            $quote->save();
            */
        } else {
            /* throwing an exception makes us stay on this page so we can repeat */
			print_r($response->status_string);
            Mage::throwException(Mage::helper('paygate')->__('Payment capturing error.'));
        }

        return $response;
    }

     /**
     * Get credex session namespace
     *
     * @return Mage_Credex_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }


}
