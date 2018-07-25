<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 11/13/14
 * Time: 9:29 AM
 */
class Hawksearch_Proxy_Model_Observer extends Mage_Core_Model_Abstract {

	public function categorySave(Varien_Event_Observer $observer) {
		if (Mage::getStoreConfigFlag('hawksearch_proxy/general/enabled')) {
			try {
				/** @var Mage_Catalog_Model_Category $cat */
				$cat = $observer->getEvent()->getCategory();

				/** @var Hawksearch_Proxy_Helper_Data $helper */
				$helper = Mage::helper('hawksearch_proxy');
				if ($cat->getIsActive() == true && ($helper->getManageAll() == true || $cat->getHawkLandingPage() == true)) {
					$helper->addLandingPage($cat->getId());
				} else {
					$helper->removeLandingPage($cat->getId());
				}

			} catch (Exception $e) {
				Mage::logException($e);
			}
		}
	}

	/* called for event 'controller_action_layout_load_before' */
	public function layoutUpdates(Varien_Event_Observer $observer) {
		if (!Mage::getStoreConfig('hawksearch_proxy/general/enabled')) {
			return $this;
		}
		$rrp = $observer->getAction()->getRequest()->getAlias('rewrite_request_path');
		$helper = Mage::helper('hawksearch_proxy');

		/** @var Mage_Core_Model_Layout $layout */
		$layout = $observer->getLayout();
		/** @var Mage_Core_Model_Layout_Update $update */
		$update = $layout->getUpdate();
		if (in_array('catalog_category_view', $update->getHandles()) && $helper->getIsHawkManaged($rrp)) {
			$update->addHandle('hawksearch_facet_handle');
		} elseif (in_array('catalogsearch_result_index', $update->getHandles())) {
			$update->addHandle('hawksearch_search_handle');
		} elseif (in_array('hawksearch_proxy_custom_view', $update->getHandles())){
		    $update->addHandle('hawkproxy_custom_handle');
        }

		return $this;
	}

	public function syncCategories(Mage_Cron_Model_Schedule $observer) {
		$helper = Mage::helper('hawksearch_proxy');
		if(! Mage::getStoreConfigFlag('hawksearch_proxy/sync/enabled')){
			$helper->log('cron sync not enabled, exiting');
			return $this;
		}
		$helper->log('going to start cron sync');
		try {
			if ($helper->createSyncLocks()) {
				$helper->synchronizeHawkLandingPages();
				$helper->log('done synchronizing landing pages, removing locks');
				$helper->removeSyncLocks();
				$msg = 'Category synchronization completed.';
			} else {
				$msg = 'Category synchronization is locked, cannot proceed.';
			}

		} catch (Exception $e) {
			$msg = 'Hawksearch category sync encountered an error: ' . $e->getMessage();
		}
		$email = Mage::getStoreConfig("hawksearch_proxy/sync/email");
		if (!empty($email)) {
			$subject = 'Hawksearch cron category sync';
			$body = sprintf("Message: %s\r\n\r\nPlease check Hawksearch log files if an error was reported.\r\n\r\nSincerely,\r\nHawkSearch\r\n\r\n", $msg);
			$f_name = Mage::getStoreConfig('trans_email/ident_general/name');
			$f_email = Mage::getStoreConfig('trans_email/ident_general/email');

			mail($email, $subject, $body, "From: {$f_name} <{$f_email}>");
		}
	}

	public function orderPlaced(Varien_Event_Observer $observer) {
		if (!Mage::getStoreConfig('hawksearch_proxy/general/enabled')) {
			return $this;
		}

		/*
		 * Ok, so what we will do is register the pixel data here
		 * and in the layoutUpdates function, we can add the pixel
		 * to the bottom of the page
		 * Nope, magento registry won't persist, there is a bounce to
		 * the success page. Lets see if we can use the session...
		 */
		/** @var Mage_Core_Model_Session $sess */
		$sess = Mage::getSingleton('core/session');
		$sessionId  = $sess->getSessionId();

		/** @var Hawksearch_Proxy_Helper_Data $helper */
		$helper = Mage::helper('hawksearch_proxy');
		/** @var Mage_Sales_Model_Order $order */
		$order = $observer->getOrder();
		$order->getShippingAmount();

		$sess->setData('hawk_tracking_data', $helper->getTrackingPixelUrl(array(
			'd' => $helper->getOrderTackingKey(),
			'hawksessionid' => $sessionId,
			'orderno' => $order->getIncrementId(),
			'total' => $order->getGrandTotal() // or getSubtotal()?
		)));
	}

}