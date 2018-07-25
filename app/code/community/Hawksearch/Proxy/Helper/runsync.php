<?php
/**
 * Copyright (c) 2013 Hawksearch (www.hawksearch.com) - All Rights Reserved
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 */

$opts = getopt('r:t:i:');
unlink($opts['t']);

chdir($opts['r']);
require 'app/Mage.php';


Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
/** @var Mage $app */
$app = Mage::app();

/** @var Hawksearch_Proxy_Helper_Data $helper */
$helper = Mage::helper('hawksearch_proxy');

if ($helper->isSyncLocked()) {
	Mage::throwException("One or more feeds are being generated. Generation temporarily locked.");
}
if ($helper->createSyncLocks()) {
	$helper->synchronizeHawkLandingPages();
	$helper->log('done synchronizing landing pages, removing locks');
	$helper->removeSyncLocks();
}

