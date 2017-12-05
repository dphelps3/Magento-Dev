<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 03/11/14
 * Time   : 11:00 AM
 * File   : Api.php
 * Module : sage-payment-solutions
 */

class Ebizmarts_SagePaymentsPro_Model_Api
{
    public function info($orderId)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $collection = Mage::getModel('ebizmarts_sagepaymentspro/transaction')
            ->getCollection()->addFieldToFilter('order_id', array('eq'=>$order->getId()));
        if (!$collection->getSize()) {
            return array('error' => 'No order '.$orderId.' exist');
        }
        $allItems = array();
        foreach ($collection as $item) {
            $oneItem = array();
            $oneItem['response_status'] = $item->getResponseStatus();
            $oneItem['post_code_result'] = $item->getPostCodeResult();
            $oneItem['cvv'] = $item->getCvvIndicator();
            $oneItem['avs'] = $item->getAvs();
            $oneItem['risk'] = $item->getRiskIndicator();
            $oneItem['reference'] = $item->getReference();
            $oneItem['transaction_id'] = $item->getTransactionId();
            $oneItem['amount'] = $item->getAmount();
            $oneItem['type'] = $item->getType();
            $oneItem['date'] = $item->getTransactionDate();
            $oneItem['token'] = $item->getToken();
            $allItems[] = $oneItem;
        }
        return $allItems;
    }
}