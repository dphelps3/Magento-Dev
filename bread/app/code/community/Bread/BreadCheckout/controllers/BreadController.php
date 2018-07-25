<?php
/**
 * Handles Checking Out From The Product Page
 *
 * @author  Bread   copyright 2016
 * @author  Joel    @Mediotype
 */
class Bread_BreadCheckout_BreadController extends Mage_Core_Controller_Front_Action
{
    /*
     * Landing page to which user gets redirected to after completing payment or error happens
     *
     */

    public function landingPageAction()
    {
        $transactionId = $this->getRequest()->getParam("transactionId");
        $orderRef = $this->getRequest()->getParam("orderRef");

        if ($transactionId && $orderRef && !$this->getRequest()->getParam("error")) {
            $this->validateBackendOrder($transactionId, $orderRef);
        } else {
            Mage::getSingleton('core/session')->addError($this->__('There was an error with your financing program'));
            $this->_redirect("/");
        }
    }

    /**
     * Add Token and total To Session Once Approved
     *
     */
    public function validatePaymentMethodAction()
    {
        try {
            $token  = $this->getRequest()->getParam('token');
            if ($token) {
                $data   = Mage::getModel('breadcheckout/payment_api_client')->getInfo($token);
                if ($data->breadTransactionId) {
                    Mage::getSingleton('checkout/session')
                        ->setBreadTransactionId($token);
                }

                if ($data->adjustedTotal) {
                    Mage::helper('breadcheckout/checkout')->setBreadTransactionAmount($data->adjustedTotal);
                }
            }

            $result = true;
        } catch (Exception $e) {
            $result = false;
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(
                array(
                'result' => $result
                )
            )
        );
    }

    /**
     * Create Magento Order From Bread Pop Up Order
     *
     */
    public function validateOrderAction()
    {
        try {
            $token      = $this->getRequest()->getParam('token');
            if ($token) {
                Mage::helper('breadcheckout')->log(
                    array(
                        "VALIDATE ORDER TOKEN"      => $token,
                    )
                );
                $data       = Mage::getModel('breadcheckout/payment_api_client')->getInfo($token);
                $this->processOrder($data);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError(
                "Checkout With Financing On Product Page Error, Please 
                Contact Store Owner. You may checkout by adding to cart and providing a payment in the checkout 
                process."
            );
            $this->_redirectReferer();
        }
    }

    /**
     * Create Magento Order From Bread Pop Up Order
     *
     */
    public function validateBackendOrder($token, $quoteId)
    {
        try {
            if ($token) {
                Mage::helper('breadcheckout')->log(
                    array(
                        "VALIDATE ORDER TOKEN"      => $token,
                    )
                );
                $data       = Mage::getModel('breadcheckout/payment_api_client')->getInfo($token);

                $store = Mage::getModel('sales/quote')->load($quoteId)->getStoreId();
                $website = Mage::getModel('core/store')->load($store)->getWebsiteId();

                $customer = Mage::getModel('customer/customer')->setWebsiteId($website);
                $customer->loadByEmail($data->billingContact->email);

                Mage::getSingleton('customer/session')->loginById($customer->getId());

                Mage::getSingleton('checkout/session')->setBreadTransactionId($token);
                $this->processBackendOrder($quoteId, $data);

                $this->_redirect('checkout/onepage/success');
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('breadcheckout/Customer')->sendCustomerErrorReportToMerchant(
                $e,
                "",
                $quoteId,
                $token
            );
            Mage::getSingleton('core/session')->addError(
                $this->__('There was an error with your financing program. Notification was sent to merchant.')
            );
            $this->_redirect("/");
        }
    }

    /**
     * Create Magento Order From Bread Pop Up Order
     *
     */
    public function processBackendOrder($quoteId, $data)
    {
        $quote = Mage::getModel("sales/quote")->load($quoteId);
        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();

        $quote->getPayment()->importData(array('method' => 'breadcheckout'));
        $quote->getPayment()->setTransactionId($data->breadTransactionId);
        $quote->getPayment()->setAdditionalData(json_encode($data));
        $service    = Mage::getModel('sales/service_quote', $quote);

        try {
            $service->submitAll();
        } catch (Exception $e) {
            Mage::helper('breadcheckout')->log(
                array("ERROR SUBMITTING QUOTE IN PROCESS ORDER"=>$e->getMessage()),
                'bread-exception.log'
            );
            Mage::logException($e);
            throw $e;
        }

        $order      = $service->getOrder();
        Mage::getSingleton('breadcheckout/session')->setLastRealOrderId($order->getId());
        $session    = Mage::getSingleton('checkout/session');
        $session->setLastSuccessQuoteId($quote->getId());
        $session->setLastQuoteId($quote->getId());
        $session->setLastOrderId($order->getId());

        try {
            $order->sendNewOrderEmail();
        } catch (Exception $ex) {
            Mage::logException($ex);
        }

        $cart   = Mage::getModel('checkout/cart');
        $cart->truncate()->save();
        $cart->init();

        $cart       = Mage::getModel('checkout/cart');
        $cartItems  = $cart->getItems();
        foreach ($cartItems as $item) {
            $quote->removeItem($item->getId());
        }

        $quote->save();
    }


    /**
     * Get Shipping Method Prices
     *
     */
    public function shippingEstimationAction()
    {
        try {
            $address    = $this->getShippingAddressForQuote($this->getRequest()->getParams());

            $data       = $address->getGroupedAllShippingRates();
            $methods    = array();
            $code       = array();
            foreach ($data as $method) {
                foreach ($method as $rate) {
                    if (array_key_exists($rate->getCode(), $code)) {
                        continue;
                    }

                    $code[$rate->getCode()] = true;
                    $methods[] = array(
                        'type'   => $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle(),
                        'typeId' => $rate->getCode(),
                        'cost'   => round($rate->getPrice() * 100),
                    );
                }
            }

            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(
                    array(
                    'result' => $methods
                    )
                )
            );
        } catch (Exception $e) {
            Mage::helper('breadcheckout')->log(
                array("ERROR"=>"Exception in shipping estimate action", "PARAMS"=>$this->getRequest()->getParams()),
                'bread-exception.log'
            );
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError(
                "Internal Error, Please Contact Store Owner. 
                You may checkout by adding to cart and providing a payment in the checkout process."
            );
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(
                    array('result' => array('error' => 1, 'text'  => 'Internal error'))
                )
            );
        }
    }

    /**
     * Get Tax Estimate
     *
     */
    public function taxEstimationAction()
    {
        Mage::helper('breadcheckout')->log(array("TAX ESTIMATE ACTION GET PARAMS"=>$this->getRequest()->getParams()));
        $params = $this->getRequest()->getParams();
        $data       = json_decode($params['shippingInfo'], true);
        try {
            $shippingAddress    = $this->getShippingAddressForQuote($data);
            $result             = $shippingAddress->getTaxAmount();

            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(array('result' => round($result  * 100)))
            );
        } catch (Exception $e) {
            Mage::helper('breadcheckout')->log("EXCEPTION IN TAX ESTIMATE ACTION", 'bread-exception.log');
            Mage::logException($e);
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(array('result' => array('error' => 1, 'text'  => 'Internal error')))
            );
        }
    }

    /**
     * Collect Totals Tax and Shipping Estimate Actions
     *
     * @param array $data
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function getShippingAddressForQuote(array $data)
    {
        $helper     = Mage::helper('breadcheckout');
        try {
            $quote  = $this->getQuote($data);

            $address    = $quote->getShippingAddress();

            $country = $helper->getDefaultCountry();
            $address->setCountryId($country)
                ->setCity($data['city'])
                ->setPostcode($data['zip'])
                ->setRegionId(Mage::getModel('directory/region')->loadByCode($data['state'], $country)->getId())
                ->setRegion($data['state'])
                ->setRegionCode($data['state'])
                ->setCollectShippingRates(true);

            if (isset($data['selectedShippingOption']) && isset($data['selectedShippingOption']['typeId'])) {
                $address->setShippingMethod($data['selectedShippingOption']['typeId']);
            }
            
            $this->setCustomerGroupId($quote);
            $address->setTotalsCollectedFlag(false)->getQuote()->collectTotals();

            return $address;
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(array('result' => array('error' => 1, 'text'  => 'Internal error')))
            );
        }
    }

    /**
     * Set customer group ID to ensure that correct
     * tax rate is applied to order total
     *
     * @param $quote Mage_Sales_Model_Quote
     * @return void
     */
    protected function setCustomerGroupId($quote)
    {
        $session = Mage::getSingleton('customer/session');

        if ($session->isLoggedIn()) {
            $groupId = $session->getCustomer()->getGroupId();
        } else {
            $groupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        }

        $quote->setCustomerGroupId($groupId);
    }

    /**
     *
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote($data)
    {
        $requestCode    = $data['block_key'];

        switch ($requestCode) {
            case Bread_BreadCheckout_Helper_Data::BLOCK_CODE_CHECKOUT_OVERVIEW :
                $session    = Mage::getSingleton('checkout/cart');
                $quote      = $session->getQuote();
                break;

            case Bread_BreadCheckout_Helper_Data::BLOCK_CODE_PRODUCT_VIEW :
                $quote                  = Mage::getModel('sales/quote');
                $selectedProductId      = $data['selected_simple_product_id'];
                $mainProductId          = $data['main_product_id'];
                $customOptionPieces     = explode('***', $data['selected_sku']);
                $mainProduct            = Mage::getModel('catalog/product')->load($mainProductId);
                $simpleProduct          = Mage::getModel('catalog/product')->load($selectedProductId);
                $this->addItemToQuote(
                    $quote,
                    $simpleProduct,
                    $mainProduct,
                    $customOptionPieces,
                    isset($item['quantity']) ? $item['quantity'] : 1
                );
                $address = $quote->getShippingAddress();
                $storeId = Mage::app()->getStore()->getId();
                $quote->setStoreId($storeId);
                $address->setStoreId($storeId);
                $quote->save();
                $address->save();
                break;
        }

        return $quote;
    }

    /**
     * Process Order Placed From Bread Pop Up
     *
     * @param $data
     * @throws Exception
     */
    protected function processOrder($data)
    {
        Mage::helper('breadcheckout')->log(array("PROCESS ORDER DATA"=>$data));

        $quote  = Mage::getModel('sales/quote'); /** @var $quote Mage_Sales_Model_Quote */

        $storeId    = Mage::app()->getStore()->getId();
        $quote->setStoreId($storeId);

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer   = Mage::getSingleton('customer/session')->getCustomer();
            $quote->assignCustomer($customer);
        } else {
            $quote->setCustomerEmail($data->billingContact->email);
        }

        Mage::getSingleton('checkout/session')->setBreadTransactionId($data->breadTransactionId);
        $this->processOrderItems($quote, $data->lineItems);

        $this->itemWeightFix($quote);

        if (isset($data->discounts) && !empty($data->discounts)) {
            $discountDescription = $data->discounts[0]->description;
            $quote->setCouponCode(substr($discountDescription, 10, strlen($discountDescription) - 11));
        }

        $billingContact     = $this->processAddress($data->billingContact);
        $shippingContact    = $this->processAddress($data->shippingContact);

        if ($billingContact['city'] == null) {
            $billingContact['city']         = $shippingContact['city'];
            $billingContact['region_id']    = $shippingContact['region_id'];
        }

        Mage::helper('breadcheckout')->log(
            array("SHIPPING CONTACT"=>$shippingContact, "BILLING CONTACT"=>$billingContact)
        );

        $billingAddress     = $quote->getBillingAddress()->addData($billingContact);
        $shippingAddress    = $quote->getShippingAddress()->addData($shippingContact)->setCollectShippingRates(true);

        if (!isset($data->shippingMethodCode)) {
            Mage::helper('breadcheckout')->log("Shipping Method Code Is Not Set On The Response");
        }

        $shippingAddress->setShippingMethod($data->shippingMethodCode);

        $shippingAddress = $quote->getShippingAddress();

        $storeId = Mage::app()->getStore()->getId();
        $quote->setStoreId($storeId);
        $shippingAddress->setStoreId($storeId);

        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod('breadcheckout');
        } else {
            $quote->getShippingAddress()->setPaymentMethod('breadcheckout');
        }

        $customer   = Mage::helper('breadcheckout/Customer')->createCustomer($quote, $billingContact, $shippingContact);
        $quote->getPayment()->importData(array('method' => 'breadcheckout'));
        $quote->getPayment()->setTransactionId($data->breadTransactionId);
        $quote->getPayment()->setAdditionalData(json_encode($data));

        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
        $service    = Mage::getModel('sales/service_quote', $quote);

        try {
            $service->submitAll();
        } catch (Exception $e) {
            Mage::helper('breadcheckout')->log(
                array("ERROR SUBMITTING QUOTE IN PROCESS ORDER"=>$e->getMessage()),
                'bread-exception.log'
            );
            Mage::logException($e);
            throw $e;
        }

        $order      = $service->getOrder();
        Mage::getSingleton('breadcheckout/session')->setLastRealOrderId($order->getId());
        $session    = Mage::getSingleton('checkout/session');
        $session->setLastSuccessQuoteId($quote->getId());
        $session->setLastQuoteId($quote->getId());
        $session->setLastOrderId($order->getId());

        try {
            $order->sendNewOrderEmail();
        } catch (Exception $ex) {
            Mage::logException($ex);
        }

        $cart   = Mage::getModel('checkout/cart');
        $cart->truncate()->save();
        $cart->init();

        $cart       = Mage::getModel('checkout/cart');
        $cartItems  = $cart->getItems();
        foreach ($cartItems as $item) {
            $quote->removeItem($item->getId());
        }

        $quote->save();

        if ($customer) {
            Mage::helper('breadcheckout/Customer')->loginCustomer($customer);
        }

        $this->_redirect('checkout/onepage/success');
    }

    /**
     * Format Address Data
     *
     * @param array $contactData
     * @return array
     */
    protected function processAddress($contactData)
    {
        $regionId   = null;
        if (isset($contactData->state)) {
            $region     = Mage::getModel('directory/region');       /** @var $region Mage_Directory_Model_Region */
            $region->loadByCode($contactData->state, Mage::helper('breadcheckout')->getDefaultCountry());
            if ($region->getId()) {
                $regionId   = $region->getId();
            }
        }

        $fullName       = isset($contactData->fullName) ? explode(' ', $contactData->fullName) : '';
        $addressData    = array(
            'firstname'     => isset($contactData->firstName) ? $contactData->firstName : $fullName[0],
            'lastname'      => isset($contactData->lastName) ?
                                $contactData->lastName : (isset($fullName[1]) ? $fullName[1] : ''),
            'street'        => $contactData->address . (isset($contactData->address2) ?
                                (' ' .  $contactData->address2) : ''),
            'city'          => $contactData->city,
            'postcode'      => $contactData->zip,
            'telephone'     => $contactData->phone,
            'country_id'    => Mage::helper('breadcheckout')->getDefaultCountry()
        );

        if (null !== $regionId) {
            $addressData['region']      = $contactData->state;
            $addressData['region_id']   = $regionId;
        }

        if (isset($contactData->email)) {
            $addressData['email']   = $contactData->email;
        }

        return $addressData;
    }

    /**
     * Process Order Items
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param                        $data
     */
    protected function processOrderItems(Mage_Sales_Model_Quote $quote, $data)
    {
        foreach ($data as $item) {
            if ($item->product->sku) {
                $pieces         = explode('///', $item->product->sku);
                $productCount   = 0;
                $baseProduct    = null;
                if (isset($pieces[1])) {
                    $baseProduct    = Mage::getModel('catalog/product');
                    $baseProduct->load($baseProduct->getIdBySku($pieces[0]));
                    $productCount++;
                }

                $product                = Mage::getModel('catalog/product');
                $customOptionPieces     = explode('***', $pieces[$productCount]);
                $product->load($product->getIdBySku($customOptionPieces[0]));

                if ($baseProduct == null) {
                    $baseProduct    = $product;
                }

                if ($product->getId()) {
                    $this->addItemToQuote(
                        $quote,
                        $product,
                        $baseProduct,
                        $customOptionPieces,
                        isset($item->quantity) ? $item->quantity : 1
                    );
                }
            }
        }
    }

    /**
     * Add Item To Quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param                        $product
     * @param                        $baseProduct
     * @param                        $customOptionPieces
     * @param                        $quantity
     */
    protected function addItemToQuote(
        Mage_Sales_Model_Quote $quote,
        $product,
        $baseProduct,
        $customOptionPieces,
        $quantity
    ) 
    {
        $productId          = $product->getId();
        $baseProductId      = $baseProduct->getId();

        $buyInfo            = array(
            'qty' =>  $quantity,
            'product'=>$baseProductId,
        );

        if ($baseProductId != $productId) {
            $catalogResource            = Mage::getResourceModel('catalog/product');
            $options                    = array();
            $productAttributeOptions    = $baseProduct->getTypeInstance(true)
                ->getConfigurableAttributesAsArray($baseProduct);
            foreach ($productAttributeOptions as $option) {
                $options[$option['attribute_id']]   =
                    $catalogResource->getAttributeRawValue($productId, $option['attribute_id'], null);
            }

            $buyInfo['super_attribute']     = $options;
        }

        $counter    = 0;
        if (count($customOptionPieces) > 1) {
            $customOptionConfig     = array();
            foreach ($customOptionPieces as $customOption) {
                $counter++;
                if ($counter == 1) {
                    continue;
                }

                $optionKeyValue     = explode('===', $customOption);
                $found              = false;

                foreach ($baseProduct->getOptions() as $o) {
                    if ($found) {
                        break;
                    }

                    $values     = $o->getValues();
                    if (!empty($values)) {
                        foreach ($values as $v) {
                            if ($this->compareOptions($v, $optionKeyValue[0])) {
                                if ($optionKeyValue[1]) {
                                    if ($v->getOptionTypeId() == $optionKeyValue[1]) {
                                        if (array_key_exists($v->getOptionId(), $customOptionConfig)) {
                                            $customOptionConfig[$v->getOptionId()]  =
                                                $customOptionConfig[$v->getOptionId()].','.$v->getOptionTypeId();
                                        } else {
                                            $customOptionConfig[$v->getOptionId()]  = $v->getOptionTypeId();
                                        }

                                        $found      = true;
                                    }
                                } else {
                                    if (array_key_exists($v->getOptionId(), $customOptionConfig)) {
                                        $customOptionConfig[$v->getOptionId()]  =
                                            $customOptionConfig[$v->getOptionId()].','.$v->getOptionTypeId();
                                    } else {
                                        $customOptionConfig[$v->getOptionId()]  = $v->getOptionTypeId();
                                    }

                                    $found      = true;
                                }
                            }
                        }
                    } else {
                        if ($this->compareOptions($o, $optionKeyValue[0])) {
                            $customOptionConfig[$o->getOptionId()]  = $optionKeyValue[1];
                            $found      = true;
                        }
                    }
                }
            }

            $buyInfo['options']     = $customOptionConfig;
        }

        $quote->addProduct($baseProduct, new Varien_Object($buyInfo));

    }

    /**
     * Fix configurable items weight
     *
     * @param $quote
     */
    protected function itemWeightFix($quote)
    {
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getProduct()->getTypeId() == 'configurable') {
                $childItem = $item->getChildren()[0];
                $childProduct = $childItem->getProduct();
                $productReloaded = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($childProduct->getId());
                $weight = $productReloaded->getWeight();
                $childProduct->setWeight($weight);
                $childItem->setWeight($weight);
                $item->getProduct()->setWeight($weight);
                $item->setWeight($weight);
            }
        }
    }

    /**
     * Compare selected option ID or SKU to current option
     * in loop
     *
     * @param $optionData
     * @param $optionValue
     * @return bool
     */
    protected function compareOptions($optionData, $optionValue)
    {
        if (preg_match('/^id~(.+)$/', $optionValue, $matches)) {
            return (bool) ($optionData->getOptionId() == $matches[1]);
        } else {
            return (bool) ($optionData['sku'] == $optionValue);
        }
    }

    /**
     * Validate Transaction total with Quote total
     */
    public function validateTotalsAction()
    {
        $params = $this->getRequest()->getParams();
        $responseArray = array('valid'=>false);
        if (isset($params['bread_transaction_id'])) {
            $responseArray['valid'] = Mage::helper('breadcheckout/checkout')
                ->validateTransactionAmount($params['bread_transaction_id']);
        }
        
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($responseArray));
    }

}
