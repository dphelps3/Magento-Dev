<?php
/**
 * Helps Integration With Session Quote
 *
 * @author      Bread   copyright   2016
 * @author      Joel    @Mediotype
 */
class Bread_BreadCheckout_Helper_Quote extends Bread_BreadCheckout_Helper_Data
{

    /**
     * Get Grand Total From Quote
     *
     * @return mixed
     */
    public function getGrandTotal()
    {
        $quote      = $this->getSessionQuote();
        $quote->collectTotals();

        $grandTotal = $quote->getGrandTotal();

        return round($grandTotal * 100);
    }

    /**
     * get Tax Value from Quote
     *
     * @return float
     */
    public function getTaxValue()
    {
        $quote      = $this->getSessionQuote();
        $quote->collectTotals();

        $quoteAddress       = $quote->getShippingAddress();
        $taxAmount          = $quoteAddress->getTaxAmount();

        return round($taxAmount * 100);
    }

    /**
     * get Discount Data From Quote
     *
     * @return array
     */
    public function getDiscountData()
    {
        $quote      = $this->getSessionQuote();
        $totals     = $quote->getTotals();
        $discountData     = array();
        if (isset($totals['discount']) && $totals['discount']->getValue()) {
            $couponCode = $quote->getCouponCode();
            $discount   = array(
                'amount'        => $totals['discount']->getValue() * -100.0,
                'description'   => "Discount ($couponCode)"
            );
            $discountData[]   = $discount;
        }

        return $discountData;
    }

    /**
     * Get Quote Irems Data in JSON Format for cart overview
     *
     * @return array
     */
    public function getCartOverviewItemsData()
    {
        $quote      = $this->getSessionQuote();

        $itemsData     = array();
        foreach ($quote->getAllVisibleItems() as $item) {
            $baseProduct            = $item->getProduct();
            $simpleProductItem      = $item->getOptionByCode('simple_product');
            $thisProduct            = null;
            if ($simpleProductItem == null) {
                $thisProduct    = $baseProduct;
                $baseProduct    = null;
            } else {
                $thisProduct    = $item->getOptionByCode('simple_product')->getProduct();
            }

            if (isset($totals['discount']) && $totals['discount']->getValue()) {
                $discount   = round($totals['discount']->getValue());
            } else {
                $discount   = null;
            }

            $itemsData[]   = Mage::helper('breadcheckout/Catalog')
                ->getProductDataArray($thisProduct, $baseProduct, $item->getQty(), $discount);
        }

        return $itemsData;
    }

    /**
     * Get Quote Items Data in JSON Format for checkout form
     *
     * @return string JSON formatted String
     */
    public function getQuoteItemsData()
    {
        $quote      = $this->getSessionQuote();

        if ($quote->hasItems() == false) {
            return array();
        }

        $itemsData     = array();
        foreach ($quote->getAllVisibleItems() as $item) {
            $price                  = $item->getPrice();
            $baseProduct            = $item->getProduct();
            $simpleProductItem      = $item->getOptionByCode('simple_product');
            $thisProduct            = null;
            if ($simpleProductItem == null) {
                $thisProduct            = $baseProduct;
                $baseProduct            = null;
            } else {
                $thisProduct            = $item->getOptionByCode('simple_product')->getProduct();
            }

            $itemsData[]       = Mage::helper('breadcheckout/Catalog')
                ->getProductDataArray($thisProduct, $baseProduct, $item->getQty(), $price);
        }

        return $itemsData;
    }

    /**
     * Get Bread Formatted Billing Address Data From Address Model
     *
     * @param Mage_Sales_Model_Quote_Address $billingAddress
     * @return array
     */
    public function getFormattedBillingAddressData(Mage_Sales_Model_Quote_Address $billingAddress)
    {
        $data     = array(
            'address'       => $billingAddress->getStreet1() .
                ($billingAddress->getStreet2() == '' ? '' : (' ' . $billingAddress->getStreet2())),
            'address2'      => $billingAddress->getStreet3() .
                ($billingAddress->getStreet4() == '' ? '' : (' ' . $billingAddress->getStreet4())),
            'city'          => $billingAddress->getCity(),
            'state'         => $billingAddress->getRegionCode(),
            'zip'           => $billingAddress->getPostcode(),
            'phone'         => substr(preg_replace('/[^0-9]+/', '', $billingAddress->getTelephone()), -10),
            'email'         => $billingAddress->getEmail(),
            'firstName'     => $billingAddress->getFirstname(),
            'lastName'      => $billingAddress->getLastname(),
        );

        return $data;
    }

    /**
     * Get Bread Formatted Shipping Address Data From Address Model
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return array
     */
    public function getFormattedShippingAddressData(Mage_Sales_Model_Quote_Address $shippingAddress)
    {
        $data     = array(
            'fullName'      => $shippingAddress->getName(),
            'address'       => $shippingAddress->getStreet1() .
                ($shippingAddress->getStreet2() == '' ? '' : (' ' . $shippingAddress->getStreet2())),
            'address2'      => $shippingAddress->getStreet3() .
                ($shippingAddress->getStreet4() == '' ? '' : (' ' . $shippingAddress->getStreet4())),
            'city'          => $shippingAddress->getCity(),
            'state'         => $shippingAddress->getRegionCode(),
            'zip'           => $shippingAddress->getPostcode(),
            'phone'         => substr(preg_replace('/[^0-9]+/', '', $shippingAddress->getTelephone()), -10)
        );

        return $data;
    }

    public function getFormattedShippingOptionsData(Mage_Sales_Model_Quote_Address $shippingAddress)
    {
        $data         = array();
        $data[]       = array(
            'type'   => $shippingAddress->getShippingDescription(),
            'typeId' => $shippingAddress->getShippingMethod(),
            'cost'   => round($shippingAddress->getShippingAmount() * 100),
        );

        return $data;
    }

    /**
     * Get Session Quote object for admin or frontend
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getSessionQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/cart')->getQuote();
    }

    /**
     * Check quote before submitting
     *
     * @param Mage_Sales_Model_Quote $quote
     * @throws Exception
     */

    protected function checkQuote($quote)
    {
        if (!$quote || ($quote && $quote->getItemsQty() == 0)) {
            Mage::throwException("Cart is empty");
        }

        if ($quote->getPayment()->getMethodInstance()->getCode()
            != Mage::getModel("breadcheckout/payment_method_bread")->getMethodCode()) {
            Mage::throwException("In order to checkout with bread you must choose bread as payment option.");
        }

        $method = $quote->getShippingAddress()->getShippingMethod();

        if (!$method) {
            Mage::throwException("Please specify a shipping method.");
        }

    }

    /**
     * Submit quote to API
     *
     * @param Mage_Sales_Model_Quote $quote
     * @throws Exception
     */

    public function submitQuote($quote, $skipCheck = false)
    {
        if (!$skipCheck) {
            $this->checkQuote($quote);
        }

        $arr = array();

        $arr["expiration"] = Mage::getSingleton('core/date')->date(
            'Y-m-d',
            strtotime("+" . Mage::getStoreConfig('checkout/cart/delete_quote_after') . "days")
        );
        $arr["options"] = array();
        $arr["options"]["orderRef"] = $quote->getId();
        $arr["options"]["completeUrl"] = Mage::helper("breadcheckout")->getLandingPageURL();
        $arr["options"]["errorUrl"] = Mage::helper("breadcheckout")->getLandingPageURL(true);

        $arr["options"]["shippingOptions"] = array();

        $method = $quote->getShippingAddress()->getShippingMethod();

        foreach ($quote->getShippingAddress()->collectShippingRates()->getGroupedAllShippingRates() as $rate) {
            foreach ($rate as $r) {
                if ($r["code"] == $method) {
                    array_push(
                        $arr["options"]["shippingOptions"],
                        array(
                            "type"   => $r["method_title"],
                            "typeId" => $method,
                            "cost"   => Mage::helper('breadcheckout')->getCentsFromPrice($r["price"])
                        )
                    );
                }
            }
        }

        $arr["options"]["discounts"] = array();
        $totals = $quote->getTotals();

        if (!Mage::helper('breadcheckout')->isHealthcare()) {
            $arr["options"]["shippingContact"] = $this->getFormattedShippingAddressData($quote->getShippingAddress());
            $arr["options"]["billingContact"] = $this->getFormattedBillingAddressData($quote->getBillingAddress());

            $arr["options"]["items"] = array();

            foreach ($quote->getAllVisibleItems() as $item) {
                array_push($arr["options"]["items"], $this->parseItem($item));
            }
        } else {
            if ($totals && isset($totals["grand_total"])) {
                $grandTotal = Mage::helper('breadcheckout')->getCentsFromPrice($totals['grand_total']->getValue());
                $arr["options"]["customTotal"] = $grandTotal;
            }
        }

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

        $tax = Mage::helper('breadcheckout')->getCentsFromPrice($quote->getShippingAddress()->getData('tax_amount'));

        if ($tax) {
            $arr["options"]["tax"] = $tax;
        }

        return Mage::getModel('breadcheckout/payment_api_client')->submitCartData($arr);
    }

    /**
     *  Get quote estimated monthly payment
     *
     * @param Mage_Sales_Model_Quote $quote
     * @throws Exception
     */
    public function getQuoteEstimate($quote = null)
    {
        if (!$quote) {
            $quote=$this->getSessionQuote();
        }

        try{
            $result = $this->submitQuote($quote, true);
            return $result->asLowAs->amount;
        }catch (Exception $e){
            Mage::helper('breadcheckout')->log($e->getMessage(), 'bread-exception.log');
        }
    }

    /**
     * Parse address to array
     * @param quote_address
     * @return array
     */

    protected function parseAddress($address)
    {
        $contactMap = array("firstName"=>"firstname", "lastName"=>"lastname",
                            "email"=>"email", "address"=>"street", "address2"=> "",
                            "city"=>"city", "state"=>"region", "zip"=>"postcode", "phone"=>"telephone");
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

}
