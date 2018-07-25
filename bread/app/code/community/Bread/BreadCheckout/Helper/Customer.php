<?php
/**
 * Helps Integration With Customer
 *
 * @copyright   Bread   copyright   2016
 * @author      Joel    @Mediotype
 */
class Bread_BreadCheckout_Helper_Customer extends Bread_BreadCheckout_Helper_Data
{

    const CART_CONFIRMATION_EMAIL_TEMPLATE = "confirmation_email_template";
    const ERROR_REPORT_EMAIL_TEMPLATE = "error_report_email_template";
     /**
     * Pass Back Bread Formatted Default Customer Address If It Exists
     *
     * @return array
     */
    public function getFormattedDefaultShippingAddress()
    {
        $session                    = $this->getCustomerSession();
        $customer                   = $session->getCustomer();

        if (empty($customer)) {
            return array();
        }

        $defaultShippingAddress     = $customer->getPrimaryShippingAddress();

        if (empty($defaultShippingAddress)) {
            return array();
        }

        $primaryData        = array(
            'fullName'      => $defaultShippingAddress->getName(),
            'address'       => $defaultShippingAddress->getStreet1() .
                ($defaultShippingAddress->getStreet2() == '' ? '' : (' ' . $defaultShippingAddress->getStreet2())),
            'address2'      => $defaultShippingAddress->getStreet3() .
                ($defaultShippingAddress->getStreet4() == '' ? '' : (' ' . $defaultShippingAddress->getStreet4())),
            'city'          => $defaultShippingAddress->getCity(),
            'state'         => $defaultShippingAddress->getRegionCode(),
            'zip'           => $defaultShippingAddress->getPostcode(),
            'email'         => $customer->getEmail(),
            'phone'         => substr(preg_replace('/[^0-9]+/', '', $defaultShippingAddress->getTelephone()), -10)
        );

        return $primaryData;
    }

    /**
     * Pass Back Bread Formatted Default Customer Address If It Exists
     *
     * @return array
     */
    public function getFormattedDefaultBillingAddress()
    {
        $session                    = $this->getCustomerSession();
        $customer                   = $session->getCustomer();

        if (empty($customer)) {
            return array();
        }

        $defaultBillingAddress     = $customer->getPrimaryBillingAddress();

        if (empty($defaultBillingAddress)) {
            return array();
        }

        $primaryData        = array(
            'fullName'      => $defaultBillingAddress->getName(),
            'address'       => $defaultBillingAddress->getStreet1() .
                ($defaultBillingAddress->getStreet2() == '' ? '' : (' ' . $defaultBillingAddress->getStreet2())),
            'address2'      => $defaultBillingAddress->getStreet3() .
                ($defaultBillingAddress->getStreet4() == '' ? '' : (' ' . $defaultBillingAddress->getStreet4())),
            'city'          => $defaultBillingAddress->getCity(),
            'state'         => $defaultBillingAddress->getRegionCode(),
            'zip'           => $defaultBillingAddress->getPostcode(),
            'email'         => $customer->getEmail(),
            'phone'         => substr(preg_replace('/[^0-9]+/', '', $defaultBillingAddress->getTelephone()), -10)
        );

        return $primaryData;
    }

    /**
     * Create Customer Called From Order Place Process
     *
     * @param $quote
     * @param $billingContact
     * @param $shippingContact
     * @return Mage_Customer_Model_Customer|void
     * @throws Mage_Core_Exception
     */
    public function createCustomer($quote, $billingContact, $shippingContact)
    {
        $session    = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            return $session->getCustomer();
        }

        $quote->setCustomerLastname($billingContact['lastname']);
        $quote->setCustomerFirstname($billingContact['firstname']);

        if ($this->isAutoCreateCustomerAccountEnabled() == false) {
            return;
        }

        $customer   = Mage::getModel('customer/customer');
        $email      = $quote->getCustomerEmail();

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

        // Don't create a new account if one already exists for this email
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return;
        }

        $billingAddress     = Mage::getModel('customer/address');
        $billingAddress->setData($billingContact);
        $shippingAddress    = Mage::getModel('customer/address');
        $shippingAddress->setData($shippingContact);

        $customer->setEmail($email)
            ->setPassword($customer->generatePassword(7));
        $billingAddress->setIsDefaultBilling('1')->setSaveInAddressBook('1');
        $shippingAddress->setIsDefaultShipping('1')->setSaveInAddressBook('1');
        $customer->setPrimaryBillingAddress($billingAddress)
            ->setPrimaryShippingAddress($shippingAddress)
            ->setLastname($quote->getCustomerLastname())
            ->setFirstname($quote->getCustomerFirstname());

        try {
            $customer->save();
            $customer->setConfirmation(null);

            $quote->setCustomerId($customer->getId())
                ->setCustomer($customer);

            $billingAddress->setCustomerId($customer->getId())->setCustomer($customer);
            $customer->addAddress($billingAddress);
            $billingAddress->save();

            $shippingAddress->setCustomerId($customer->getId())->setCustomer($customer);
            $customer->addAddress($shippingAddress);
            $shippingAddress->save();

            $customer->save()->sendNewAccountEmail();
        } catch (Exception $ex) {
            Mage::helper('breadcheckout')->log('Exception Creating New Customer Account', 'bread-exception.log');
            Mage::logException($ex);
        }

        return $customer;
    }

     /**
     * Get Default Customer Shipping Address If It Exists
     *
     * @return string
     */
    public function getShippingAddressData()
    {
        if ($this->isUserLoggedIn() == false) {
            return 'false';
        }

        if ($this->hasBillingAddress() == false) {
            return 'false';
        }

        $primaryAddressData     = $this->getFormattedDefaultShippingAddress();

        return Mage::helper('core')->jsonEncode($primaryAddressData);
    }

    /**
     * Get Billing Address Default Data
     *
     * @return string
     */
    public function getBillingAddressData()
    {
        if ($this->isUserLoggedIn() == false) {
            return 'false';
        }

        if ($this->hasBillingAddress() == false) {
            return 'false';
        }

        $primaryAddressData     = $this->getFormattedDefaultBillingAddress();

        return Mage::helper('core')->jsonEncode($primaryAddressData);
    }


    /**
     * Check if Customer has associated addresses
     *
     * @return bool
     */
    public function hasBillingAddress()
    {
        if ($this->getCustomerSession()->getCustomer()->getPrimaryBillingAddress() == false) {
            return false;
        }

        return true;
    }

     /**
     * Check if current visitor is logged in
     *
     * @return bool
     */
     public function isUserLoggedIn()
     {
         return (bool) $this->getCustomerSession()->isLoggedIn();
     }

    /**
     * Login Customer After Pop Up Checkout If Enabled
     *
     * @param $customer
     */
    public function loginCustomer($customer)
    {
        $session    = Mage::getSingleton('customer/session');

        if ($session->isLoggedIn()) {
            return;
        }

        if (!is_object($customer)) {
            return;
        }

        if (!$customer->getId()) {
            return;
        }

        Mage::getSingleton('customer/session')->loginById($customer->getId());
    }


    /**
     * Get Current Customer Session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Send activation link to customer
     * @param customer
     * @param url
     */
    public function sendCartActivationEmailToCustomer($customer, $url, $items)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        $template = self::CART_CONFIRMATION_EMAIL_TEMPLATE;


        $sender = array(
            'name' => Mage::helper('core')->escapeHtml(Mage::getStoreConfig('trans_email/ident_general/name')),
            'email' => Mage::helper('core')->escapeHtml(Mage::getStoreConfig('trans_email/ident_general/email'))
        );

        $emailData = array(
            'subject' => Mage::helper('core')->__(
                "Complete your checkout at ".
                Mage::app()->getStore()->getFrontendName()." with financing"
            ),
            'url' => Mage::helper('core')->escapeHtml($url),
            'email' => Mage::helper('core')->escapeHtml($customer->getEmail()),
            'firstName' => Mage::helper('core')->escapeHtml($customer->getFirstname()),
            'lastName' => Mage::helper('core')->escapeHtml($customer->getLastname()),
            'items' => $items
        );

        $mailTemplate->addBcc($sender['email']);

        $recipient = Mage::helper('core')->escapeHtml($customer->getEmail());

        $replyTo = explode(",", Mage::getStoreConfig('sales_email/order/copy_to'))[0];
        if($replyTo)
        $mailTemplate->setReplyTo($replyTo);
        $mailTemplate->sendTransactional(
            $template, $sender, trim($recipient),
            trim($customer->getFullname()), $emailData
        );

        $translate->setTranslateInline(true);
    }

    /**
     * Send activation link to customer in sms
     * @param customer
     * @param url
     */
    public function sendCartActivationSmsToCustomer($quote, $id)
    {
        $phone = $quote->getBillingAddress()->getTelephone();
        $result = Mage::getModel('breadcheckout/payment_api_client')->sendSms($id, $phone);
        return $result;
    }

    /**
     * Send activation link to customer in email
     * @param customer
     * @param url
     */
    public function sendCartActivationBreadEmailToCustomer($quote, $id)
    {
        $email = $quote->getCustomerEmail();
        $name = $quote->getBillingAddress()->getName();
        $result = Mage::getModel('breadcheckout/payment_api_client')->sendEmail($id, $email, $name);
        return $result;
    }

    /**
     * Send error report to merchant
     * @param customer
     * @param url
     */
    public function sendCustomerErrorReportToMerchant($exception, $response="", $quoteId="", $transactionId=null)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        $template = self::ERROR_REPORT_EMAIL_TEMPLATE;


        $sender = array(
            'name' => Mage::helper('core')->escapeHtml(Mage::getStoreConfig('trans_email/ident_general/name')),
            'email' => Mage::helper('core')->escapeHtml(Mage::getStoreConfig('trans_email/ident_general/email'))
        );

        $emailData = array(
            'exception_message' => $exception->getMessage(),
            'quote' => $quoteId,
            'token' => $transactionId,
            'response' => $response
        );


        $subject = $this->__("Error report");

        $emailData['subject'] = $subject;

        $recipients = explode(",", Mage::getStoreConfig('sales_email/order/copy_to'));
        foreach($recipients as $recipient)
            $mailTemplate->sendTransactional($template, $sender, trim($recipient), trim($recipient), $emailData);

        $translate->setTranslateInline(true);
    }
}
