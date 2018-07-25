<?php
/**
 *
 * @author  Bread   copyright   2017
 */
class Bread_BreadCheckout_Model_Observer
{

    /**
     * Insert Bread button on category page
     *
     */
    public function insertBreadButton($observer)
    {
        if (!Mage::helper('breadcheckout')->isEnabledOnCAT()) {
            return;
        }

        $category = Mage::registry('current_category');
        $product  = Mage::registry('product');
        if ($category && $category->getId() && empty($product)) {
            if (Mage::helper('breadcheckout/catalog')->isBreadOnCategory($category)) {
                $_block = $observer->getBlock();
                $_type = $_block->getType();
                if ($_type == 'catalog/product_price') {
                    $_child = clone $_block;
                    $_child->setType('price/block');
                    $_block->setChild('child', $_child);
                    $_block->setTemplate('breadcheckout/list_product.phtml');
                }
            }
        }
    }
	
	 public function updateOrderStatus($observer)
	 {
        if (Mage::getSingleton('checkout/session')->getBreadSplitCCFailed()) {
            $payment = $observer->getEvent()->getPayment();
            $order = $payment->getOrder();
            $order->setStatus('bread_split_auth_failed');
            $order->save();
            Mage::getSingleton('core/session')
				->addError('Your credit card split amount authorization failed. Please contact merchant.');
            Mage::getSingleton('checkout/session')->unsBreadSplitCCFailed();
        }
    }
}
