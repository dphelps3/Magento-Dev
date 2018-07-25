<?php
/**
 * Class Bread_BreadCheckout_AdminBreadOrderController
 *
 * @author  Bread   copyright 2016
 * @author  Joel    @Mediotype
 */
class Bread_BreadCheckout_Adminhtml_BreadController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Post cart to backend
     *
     */
    public function sendMailAction()
    {
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();

        $url = $this->getRequest()->getParam("url");

        $ret = array(
            "error" => false,
            "successRows" => array(),
            "errorRows" => array(),
        );
        try{
            Mage::helper('breadcheckout/Customer')
                ->sendCartActivationEmailToCustomer($quote->getCustomer(), $url, $quote->getAllItems());
            $ret["successRows"][] = $this->__("A Bread Cart email was successfully sent to the customer.");
        }catch (Exception $e){
            $ret["error"] = true;
            $ret["errorRows"][] = $this->__("An error occurred while sending email:");
            $ret["errorRows"][] = $e->getMessage();
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ret));
    }

    /**
     * Send cart activation sms
     *
     */
    public function sendSmsAction()
    {
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();

        $id = $this->getRequest()->getParam("id");

        $ret = array(
            "error" => false,
            "successRows" => array(),
            "errorRows" => array(),
        );
        try{
            Mage::helper('breadcheckout/Customer')->sendCartActivationSmsToCustomer($quote, $id);
            $ret["successRows"][] = $this->__("A Bread Cart SMS was successfully sent to the customer.");
        }catch (Exception $e){
            $ret["error"] = true;
            $ret["errorRows"][] = $this->__("An error occurred while sending SMS:");
            $ret["errorRows"][] = $e->getMessage();
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ret));
    }

    /**
     * Send cart activation email
     *
     */
    public function sendBreadMailAction()
    {
        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();

        $id = $this->getRequest()->getParam("id");

        $ret = array(
            "error" => false,
            "successRows" => array(),
            "errorRows" => array(),
        );
        try{
            Mage::helper('breadcheckout/Customer')->sendCartActivationBreadEmailToCustomer($quote, $id);
            $ret["successRows"][] = $this->__("A Bread Cart email was successfully sent to the customer.");
        }catch (Exception $e){
            $ret["error"] = true;
            $ret["errorRows"][] = $this->__("An error occurred while sending email:");
            $ret["errorRows"][] = $e->getMessage();
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ret));
    }

    /**
     * Post cart to backend
     *
     */
    public function createCartAction()
    {
        try {
            $ret = array(
                     "error"       => false,
                     "successRows" => array(),
                     "errorRows"   => array(),
                     "cartUrl"     => ""
            );

            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();

            if (!$quote || ($quote && $quote->getItemsQty() == 0)) {
                Mage::throwException("Cart is empty");
            }

            if ($quote->getPayment()->getMethodInstance()->getCode()
                != Mage::getModel("breadcheckout/payment_method_bread")->getMethodCode()) {
                Mage::throwException("In order to checkout with bread you must choose bread as payment option.");
            }

            $arr = array();

            $arr["expiration"] = Mage::getModel('core/date')
                ->date('Y-m-d', strtotime("+" . Mage::getStoreConfig('checkout/cart/delete_quote_after') . "days"));
            $arr["options"] = array();
            $arr["options"]["orderRef"] = $quote->getId();
            $arr["options"]["completeUrl"] = Mage::helper("breadcheckout")->getLandingPageURL();
            $arr["options"]["errorUrl"] = Mage::helper("breadcheckout")->getLandingPageURL(true);

            $arr["options"]["shippingOptions"] = array();

            $method = $quote->getShippingAddress()->getShippingMethod();

            if (!$method) {
                Mage::throwException("Please specify a shipping method.");
            }

            foreach ($quote->getShippingAddress()->collectShippingRates()->getGroupedAllShippingRates() as $rate) {
                foreach ($rate as $r) {
                    if ($r["code"] == $method) {
                        array_push(
                            $arr["options"]["shippingOptions"],
                            array(
                                "type"   => $r["carrier_title"] . " - " . $r["method_title"],
                                "typeId" => $method,
                                "cost"   => Mage::helper('breadcheckout')->getCentsFromPrice($r["price"])
                            )
                        );
                    }
                }
            }

            $arr["options"]["shippingContact"] = $this->parseAddress($quote->getShippingAddress());
            $arr["options"]["billingContact"]  = $this->parseAddress($quote->getBillingAddress());

            $arr["options"]["items"] = array();

            foreach ($quote->getAllItems() as $item) {
                array_push($arr["options"]["items"], $this->parseItem($item));
            }

            $arr["options"]["discounts"] = array();
            $totals = $quote->getTotals();

            if ($totals && isset($totals["discount"])) {
                array_push(
                    $arr["options"]["discounts"],
                    array(
                        "amount"      => Mage::helper('breadcheckout')
                            ->getCentsFromPrice(abs($quote->getTotals()["discount"]->getValue())),
                        "description" => $this->__('Cart Discount')
                    )
                );
            }

            $tax = Mage::helper('breadcheckout')
                ->getCentsFromPrice($quote->getShippingAddress()->getData('tax_amount'));

            if ($tax) {
                $arr["options"]["tax"] = $tax;
            }

            $result = Mage::getModel('breadcheckout/payment_api_client')->submitCartData($arr);

            $ret["successRows"] = array(
                $this->__(
                    "A Bread Cart was created successfully. Send the following link to your 
                    customer or trigger an email or sms to the customer."
                ),
                sprintf('<a href="%1$s">%1$s</a>', $result->url)
            );

            $ret["cartUrl"] = $result->url;
            $ret["cartId"] = $result->id;
        }catch (Exception $e){
            $ret["error"] = true;
            $ret["errorRows"][] = $this->__("There was an error in cart creation:");
            $ret["errorRows"][] = $e->getMessage();
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($ret));
    }

    /**
     * Parse quote item to array
     * @param item
     * @return array
     */
    protected function parseItem($item)
    {
        $sku = $item->getProduct()["sku"];
        if ($item->getBuyRequest()->getOptions()) {
            foreach ($item->getBuyRequest()->getOptions() as $code => $option) {
                foreach ($item->getProduct()->getOptions() as $_option) {
                    if ($code == $_option->getOptionId()) {
                        $opt = $_option;
                    }
                }

                switch ($opt->getType()) {
                    case "area":
                    case "field":
                        $title = $opt->getSku();
                        $value = $option;
                        break;
                    case "multiple":
                    case "checkbox":
                        foreach ($option as $o) {
                            foreach ($opt->getValues() as $k => $v) {
                                if ($k == $o) {
                                    $value = $v->getSku();
                                }
                            }

                            $sku .= "***" . $value;
                        }

                        $value = null;
                        break;
                    case "drop_down":
                    case "radio":
                        $title = null;
                        foreach ($opt->getValues() as $k => $v) {
                            if ($k == $option) {
                                $value = $v->getSku();
                            }
                        }
                        break;
                    case "date_time":
                    case "time":
                    case "date":
                        $title = $opt->getSku();
                        $value = $option["date_internal"];
                        break;
                    case "file":
                        $title = $opt->getSku();
                        $value = serialize($option);
                        break;
                }

                if ($title && $value) {
                    $sku .= "***" . $title . "===" . $value;
                } else if ($value) {
                    $sku .= "***" . $value;
                }

                $title = null;
                $value = null;
            }
        }

        $image = $item->getProduct()->getSmallImage();
        if(!$image)
            $image = '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
        return array("imageUrl"=>Mage::helper('breadcheckout')->getMediaPath($image),
            "detailUrl"=>$item->getProduct()->getProductUrl(),
            "name"=>$item->getProduct()->getName(),
            "price"=>Mage::helper('breadcheckout')->getCentsFromPrice($item->getPrice()),
            "quantity"=> $item["qty"],
            "sku"=> $sku
        );
    }

    /**
     * Parse address to array
     * @param quote_address
     * @return array
     */

    protected function parseAddress($address)
    {
        $contactMap = array("firstName"=>"firstname", "lastName"=>"lastname", "email"=>"email", "address"=>"street",
            "address2"=> "", "city"=>"city", "state"=>"region", "zip"=>"postcode", "phone"=>"telephone");
        $arr = array();
        foreach ($contactMap as $k=>$v) {
            $arr[$k] = $address[$v];
        }

        $arr["address2"]="";
        $states = Mage::getModel('directory/country')->load($address["country_id"])->getRegions();
        foreach ($states as $state) {
            if ($state["default_name"] == $arr["state"]) {
                $arr["state"] = $state["code"];
            }
        }

        return $arr;
    }

    /**
     * Validate Payment Method In Admin
     *
     */
    public function validatePaymentMethodAction()
    {
        $result     = false;

        try {
            $token      = $this->getRequest()->getParam('token');
            if ($token) {
                $data   = Mage::getModel('breadcheckout/payment_api_client')->getInfo($token);
                if (isset($data->breadTransactionId)) {
                    if (Mage::app()->getStore()->isAdmin()) {
                        $quote      = Mage::getSingleton('adminhtml/session_quote')->getQuote();
                    } else {
                        $quote      = Mage::getSingleton('checkout/cart')->getQuote();
                    }

                    Mage::getSingleton('checkout/session')->setBreadTransactionId($data->breadTransactionId);
                    $result     = true;
                }
            }
        } catch (Exception $e) {
            Mage::helper('breadcheckout')
                ->log(
                    array('EXCEPTION IN VALIDATE PAYMENT IN ADMIN CONTROLLER'=>$e->getMessage()),
                    'bread-exception.log'
                );
            Mage::logException($e);
            Mage::throwException($e);
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(
                array(
                'result' => $result,
                )
            )
        );
    }

    protected function _isAllowed()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('bread_checkout')) {
            return false;
        }

        return true;
    }
}
