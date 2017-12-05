<?php
class Simplesolutions_CabinetCoupons_Model_Observer
{
	public function CheckCoupon(Varien_Event_Observer $observer)
	{
		if($_SERVER["REMOTE_ADDR"] == '72.239.112.246') {
			$coupon_debug = true;
			Mage::log('Logging Enabled for Aaron only', null, 'aaa_shipping_coupons.log');
		}
		
		Mage::log('Observer - CheckCoupon() Run', null, 'aaa_shipping_coupons.log');
		
		$rule = $observer->getEvent()->getRule();
		$couponCode = $rule->getCouponCode();
		
		//if($_SERVER['REMOTE_ADDR'] == '72.239.158.50') {
			Mage::log('Coupon Added at Checkout', null, 'aaa_shipping_coupons.log');
			Mage::log($couponCode, null, 'aaa_shipping_coupons.log');
		//}
		$ruleId = $rule->getRuleId();
		Mage::log('Rule Id', null, 'aaa_shipping_coupons.log');
		Mage::log($ruleId, null, 'aaa_shipping_coupons.log');
		// Check to see if this is a cabinet coupon
		//database read adapter
		$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query_results = $conn->fetchAll("SELECT * FROM csmagcabinet_coupons WHERE name = \"".$couponCode."\";");
		if($query_results) {
			$eligible_cabinets = explode(',',$query_results[0]['cabinet_ids']);
			//if($_SERVER['REMOTE_ADDR'] == '72.239.158.50') {
				Mage::log('Coupon Query Results', null, 'aaa_shipping_coupons.log');
				Mage::log($query_results, null, 'aaa_shipping_coupons.log');
			//}
			
			// check to make sure the appropriate products are in the cart
			$cart = Mage::getModel('checkout/cart')->getQuote();
			foreach ($cart->getAllItems() as $item) {
				$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
				$cartProductCatId = $_product->getCategoryIds();
				$category = Mage::getModel('catalog/category')->load($cartProductCatId);
				$path = $category->getPath();
				$cats = explode('/', $path);
				//Mage::log($path, null, 'aaron_cab_observer.log');
				//Mage::log('Cab Ids Needed', null, 'aaron_cab_observer.log');
				//Mage::log($query_results[0]['cabinet_ids'], null, 'aaron_cab_observer.log');
			//	if($_SERVER['REMOTE_ADDR'] == '72.239.158.50') {
					Mage::log($path, null, 'aaa_shipping_coupons.log');
					Mage::log('Cab Ids Needed', null, 'aaa_shipping_coupons.log');
					Mage::log($query_results[0]['cabinet_ids'], null, 'aaa_shipping_coupons.log');
			//	}
				foreach($cats as $cat) {
					//Mage::log($results, null, 'aaron_cab_observer.log');
					if(in_array($cat, $eligible_cabinets) || $cat == $query_results[0]['cabinet_ids']) {
					//	if($_SERVER['REMOTE_ADDR'] == '72.239.158.50') {
							Mage::log($cat .' found ', null, 'aaa_shipping_coupons.log');
					//	}
						$product_check = true;
					} else {
					//	if($_SERVER['REMOTE_ADDR'] == '72.239.158.50') {
							Mage::log($cat .' NOT found ', null, 'aaa_shipping_coupons.log');
					//	}
					}
					
				}
			}
			
			
			
			$quote = Mage::getModel('checkout/session')->getQuote();
			$orderGrossTotal = 0;
			foreach ($quote->getAllItems() as $item) {
			 $orderGrossTotal += $item->getPriceInclTax()*$item->getQty();
			}
			
			
			//Mage::log(('Cart Grand Total: ', null, 'aaron_cab_observer.log');
			//Mage::log(($orderGrossTotal, null, 'aaron_cab_observer.log');

			if($product_check && ($orderGrossTotal >= 1000)) {
				//Mage::log('Coupon found setting results to 0', null, 'aaron_cab_observer.log');
				// set discount to 0
				$event = $observer->getEvent();
				$result = $event->getResult();
				$result->setBaseDiscountAmount(0)->setDiscountAmount(0);
			} else {
				$quote = $observer->getEvent()->getQuote();
				//remove coupon code
				$quote->setCouponCode('');
				$quote->collectTotals()->save();
				
			}
		}
	}	
}
?>