<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_GoogleTrustedStore
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

class Mage_GoogleTrustedStore_Model_Feeder
{
    /**
     * Generates feeds for shipment and cancellation
     * 
     * @param mixed $store
     * @param bool $manual
     */
    public function generateFeeds($stores = null, $manual = null)
    {
        try {
            if (!$stores) {
                $stores = Mage::app()->getStores();
            }
            foreach ($stores as $store) {
                $feedFiles = array();
                if ($this->_getConfig()->isEnabled($store)) {
                    $this->_generateShipmentFeed($store);
                    $this->_generateCancellationFeed($store);
                }
                $this->_getConfig()->setLastTimeGenerated(null, $store);
            }
        } catch (RuntimeException $e) {
            $message = 'GoogleTrustedStore: ' . $e->getMessage();
            Mage::log($message);
            if ($manual) {
                throw new Exception($message);
            }
        }
    }

    /**
     * Generates feed for shipment and save it in temporary directory
     * 
     * @param mixed $store 
     */
    protected function _generateShipmentFeed($store)
    {
        $fromDate = $this->_getConfig()->getLastTimeGenerated($store);

        $shipments = Mage::getResourceModel('sales/order_shipment_collection')
            ->addFieldToFilter('created_at', array('from' => $fromDate->toString(Zend_Date::ISO_8601)));
        if ($store) {
            $shipments->addFieldToFilter('store_id', array('eq' => $store->getId()));
        }
        $this->_saveFeedToFile(
            Mage::getModel('googletrustedstore/feed_shipment', $shipments),
            $this->_getConfig()->getFtpShipmentFileName($store)
        );
    }

    /**
     * Generates feed for canceled orders and saves it in temporary directory
     * 
     * @param mixed $store
     */
    protected function _generateCancellationFeed($store)
    {
        $fromDate = $this->_getConfig()->getLastTimeGenerated($store);

        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_CANCELED))
            ->addFieldToFilter('updated_at', array('from' => $fromDate->toString(Zend_Date::ISO_8601)));
        if ($store) {
            $orders->addFieldToFilter('store_id', array('eq' => $store->getId()));
        }
        $this->_saveFeedToFile(
            Mage::getModel('googletrustedstore/feed_cancellation', $orders),
            $this->_getConfig()->getFtpCancellationFileName($store)
        );
    }

    /**
     * Saves feed to file
     *
     * @param Mage_GoogleTrustedStore_Model_Feed_Abstract $feed
     * @param string $fileName
     * @throws RuntimeException If error on file writing
     */
    protected function _saveFeedToFile(Mage_GoogleTrustedStore_Model_Feed_Abstract $feed, $fileName)
    {
        if (!file_exists($fileName)) {
            touch($fileName);
            chmod($fileName,0766);
        }
        $append = file_exists($fileName) && filesize($fileName);
        if (false === @file_put_contents($fileName, $feed->toString(!$append), $append ? FILE_APPEND : 0)) {
            throw new RuntimeException(Mage::helper('googletrustedstore')->__("Unable to write feed to file '%s'.", $fileName));
        }
    }

    /**
     * Upload feeds (shipment/cancelled orders) to Google
     * 
     * @param mixed $stores
     * @param bool $manual
     */
    public function uploadFeeds($stores = null, $manual = null)
    {
        try {
        	if (!$stores) {
                $stores = Mage::app()->getStores();
            }
            $uploadedFilesNum = 0;
            foreach ($stores as $store) {
                $feedFiles = array();
                if ($this->_getConfig()->isEnabled($store)) {
                    $feedFiles[$this->_getConfig()->getFtpShipmentFileName($store)] =
                        $this->_getConfig()->getFtpShipmentTargetFileName($store);
                    $feedFiles[$this->_getConfig()->getFtpCancellationFileName($store)] =
                        $this->_getConfig()->getFtpCancellationTargetFileName($store);
                    $uploadedFilesNum += count($this->_uploadFiles($feedFiles,$store));
                }
            }
            if (!$uploadedFilesNum) {
                throw new Exception(Mage::helper('googletrustedstore')
                    ->__('Feed is not sent. Please generate the feed prior to sending'));
            }
        } catch (Varien_Io_Exception $e) {
            $message = Mage::helper('googletrustedstore')->__('GoogleTrustedStore FTP upload error:') .
                ' ' . $e->getMessage();
            Mage::log($message);
            if ($manual) {
                throw new Exception($message);
            }
        }
    }

    /**
     * Verify general settings for feed generation/upload
     * 
     * @param mixed $stores
     */
    public function verifySettings($stores = null)
    {
        if (!$stores) {
            $stores = Mage::app()->getStores();
        }
        //ftp
        try {
            foreach ($stores as $store) {
                $ftp = new Varien_Io_Ftp;
                $ftp->open(array(
                    'host' => $host = $this->_getConfig()->getFtpHostName(),
                    'user' => $this->_getConfig()->getFtpUserName($store),
                    'password' => $this->_getConfig()->getFtpPassword($store),
                    'passive' => $this->_getConfig()->getFtpMode($store),
                ));
                $ftp->close();
            }
        } catch (Varien_Io_Exception $e) {
            throw new Varien_Io_Exception(
                Mage::helper('googletrustedstore')->__('FTP error: ') . $e->getMessage());
        }
        //directory permissions
        try {
            Mage::getBaseDir('tmp');
        } catch (Mage_Core_Exception $e) {
            throw new Mage_Core_Exception(
                Mage::helper('googletrustedstore')->__('Filesystem error: ') . $e->getMessage());
        }
        // file permissions
        foreach ($stores as $store) {
            $shipmentFile = $this->_getConfig()->getFtpShipmentFileName($store);
            $this->_checkFileWritable($shipmentFile);
            $cancelFile = $this->_getConfig()->getFtpCancellationFileName($store);
            $this->_checkFileWritable($cancelFile);
        }
        //cron
        $cronScheduleItems = Mage::getResourceModel('cron/schedule_collection');
        if (!$cronScheduleItems->count()) {
            throw new Exception(
                Mage::helper('googletrustedstore')->__('Cron warning: Possibly cron job is not installed'));
        }
    }
    
    /**
     * Check file permissions for writing
     *
     * @param string $filename absolute path to file
     * @throws Exception if file is not writable
     */
    protected function _checkFileWritable($filename)
    {
        if (file_exists($filename) && !is_writable($filename)) {
            throw new Exception(Mage::helper('googletrustedstore')->__("Filesystem error: feed file '%s' is not writable", $filename));
        }
    }
    
    /**
     * Upload file by file map to Google FTP server and delete it from local file system
     *
     * @param array $fileNameMap   array('local file name' => 'remote file name', ...)
     * @param mixed $store
     *
     * @throws Varien_Io_Exception If FTP related error occurred
     */
    protected function _uploadFiles(array $fileNameMap, $store)
    {
        $ftp = new Varien_Io_Ftp;
        $fs = new Varien_Io_File;
        $result = $ftp->open(array(
            'host' => $host = $this->_getConfig()->getFtpHostName(),
            'user' => $this->_getConfig()->getFtpUserName($store),
            'password' => $this->_getConfig()->getFtpPassword($store),
            'passive' => $this->_getConfig()->getFtpMode($store),
        ));
	$uploadedFiles = array();
        foreach ($fileNameMap as $localFileName => $targetFileName) {
            if ($fs->fileExists($localFileName)) {
                $uploadedFiles[] = $localFileName;
                $result = $ftp->write($targetFileName, $localFileName);
                if (false === $result) {
                    throw new Varien_Io_Exception(Mage::helper('googletrustedstore')->__("Unable to upload '%s' to '%s' on server %s", $localFileName, $targetFileName, $host));
                }
                $fs->rm($localFileName);
            }
        }

        $ftp->close();
        return $uploadedFiles;
    }

    /**
     * Returns config
     *
     * @return Mage_GoogleTrustedStore_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('googletrustedstore/config');
    }
}