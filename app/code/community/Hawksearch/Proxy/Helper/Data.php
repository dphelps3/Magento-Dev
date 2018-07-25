<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 7/3/14
 * Time: 9:34 AM
 */
class Hawksearch_Proxy_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LP_CACHE_KEY = 'hawk_landing_pages';
    const LOCK_FILE_NAME = 'hawkcategorysync.lock';

    private $mode = null;
    private $hawkData;
    private $rawResponse;
    private $store = null;
    private $landingPages;
    private $uri;
    private $clientIP;
    private $clientUA;
    private $_feedFilePath;
    //private $rewriteRequestPath;
    private $isManaged;
    private $productHrefs;

    public function isValidSearchRoute($route)
    {
		// currently only checking for presence of slash
        $valid = strpos($route, '/') === false;
		$valid = $valid && $route != 'hawksearch';
		return $valid;
    }
	public function setStore($s){
		$this->store = $s;
		return $this;
	}


    public function setClientIp($ip)
    {
        $this->clientIP = $ip;
    }

    public function setClientUa($ua)
    {
        $this->clientUA = $ua;
    }

    public function setUri($args)
    {
        unset($args['ajax']);
        unset($args['json']);
        $args['output'] = 'custom';
        $args['hawkitemlist'] = 'json';
        if (isset($args['q'])) {
            unset($args['lpurl']);
        }
        $args['hawksessionid'] = Mage::getSingleton('core/session')->getSessionId();

        $this->uri = $this->getTrackingUrl() . '/?' . http_build_query($args);
    }

    private function fetchResponse()
    {
        if (empty($this->uri)) {
            $this->log("fetchResponse called, but no URI set, setting data to null");
            $this->hawkData = null;
            return;
        }
        $client = new Zend_Http_Client();
        $client->setConfig(array('useragent' => $this->clientUA));
        $client->setUri($this->uri);
        $client->setHeaders('HTTP-TRUE-CLIENT-IP', $this->clientIP);
        $response = $client->request();
        $this->log(sprintf('requesting url %s', $client->getUri()));
        $this->rawResponse = $response->getBody();
        $this->hawkData = json_decode($this->rawResponse);
    }

    public function getResultData()
    {
        if (empty($this->hawkData)) {
            $this->fetchResponse();
        }
        return $this->hawkData;
    }

    public function getProxyBaseUrl()
    {
        return Mage::getUrl(
            'hawkproxy',
            array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()));
    }

    public function getApiUrl()
    {
        if ($this->getMode() == '1') {
            $apiUrl = Mage::getStoreConfig('hawksearch_proxy/proxy/tracking_url_live');
        } else {
            $apiUrl = Mage::getStoreConfig('hawksearch_proxy/proxy/tracking_url_staging');
        }
        $apiUrl = preg_replace('|^http://|', 'https://', trim($apiUrl, "/"));

        return sprintf("%s/api/v%s/", $apiUrl, $this->getApiVersionNumber());
    }

    public function clearHawkData()
    {
        unset($this->hawkData);
    }

    public function getLocation()
    {
        if (empty($this->hawkData)) {
            $this->fetchResponse();
        }
        return $this->hawkData->Location;
    }

    public function getFacets()
    {
        if (empty($this->hawkData)) {
            $this->fetchResponse();
        }
        return $this->hawkData->Data->Facets;
    }

    public function getTrackingUrl()
    {
        if ($this->getMode() == '1') {
            $trackingUrl = Mage::getStoreConfig('hawksearch_proxy/proxy/tracking_url_live');
        } else {
            $trackingUrl = Mage::getStoreConfig('hawksearch_proxy/proxy/tracking_url_staging');
        }
        if ('/' == substr($trackingUrl, -1)) {
            return $trackingUrl . 'sites/' . $this->getEngineName();
        }
        return $trackingUrl . '/sites/' . $this->getEngineName();
    }

    public function getTrackingPixelUrl($args)
    {
        if ($this->getMode() == '1') {
            $trackingUrl = Mage::getStoreConfig('hawksearch_proxy/proxy/tracking_url_live');
        } else {
            $trackingUrl = Mage::getStoreConfig('hawksearch_proxy/proxy/tracking_url_staging');
        }
        if ('/' == substr($trackingUrl, -1)) {
            return $trackingUrl . 'sites/_hawk/hawkconversion.aspx?' . http_build_query($args);
        }
        return $trackingUrl . '/sites/_hawk/hawkconversion.aspx?' . http_build_query($args);
    }

    public function getOrderTackingKey()
    {
        return Mage::getStoreConfig('hawksearch_proxy/proxy/order_tracking_key');
    }

    public function getEngineName()
    {
        return Mage::getStoreConfig('hawksearch_proxy/proxy/engine_name');
    }

    public function getMode()
    {
        if (empty($this->mode)) {
            $this->mode = Mage::getStoreConfig('hawksearch_proxy/proxy/mode');
        }
        return $this->mode;
    }

    public function getApiKey()
    {
        return Mage::getStoreConfig('hawksearch_proxy/proxy/hawksearch_api_key');
    }

    public function getProductCollection()
    {
        if (empty($this->hawkData)) {
            $this->fetchResponse();
        }
        $skus = array();
        $map = array();
        $i = 0;
        $results = json_decode($this->hawkData->Data->Results);
        if (count((array)$results) == 0) {
            return null;
        }
        foreach ($results->Items as $item) {
            $skus[] = $item->Id;
            $map[$item->Id] = $i;
            $i++;
        }
        $this->log(sprintf("recieved %s skus" , implode(",", $skus)));
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addAttributeToFilter('sku', array('in' => $skus))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            // ->addStoreFilter()
            ->addUrlRewrite();

        //Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        //Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        //file_put_contents('/tmp/dbselwstore.sql', $collection->getSelect()->__toString());
        $sorted = array();
        if ($collection->count() > 0) {
            $this->productHrefs = array();
            $it = $collection->getIterator();
            while ($it->valid()) {
                $prod = $it->current();
                $sorted[$map[trim($prod->getSku())]] = $prod;
                $it->next();
            }
            ksort($sorted);
            foreach ($sorted as $p) {
                $collection->removeItemByKey($p->getId());
                $collection->addItem($p);
                $this->productHrefs[] = array('sku' => $p->getSku(), 'url' => $p->getProductUrl());
            }
        }
        $this->log(sprintf("recieved %d products" , $collection->count()));
        return $collection;
    }

    public function getProductHrefList() {
        return array(
            'tracking' => array(
                'products' => $this->productHrefs,
                'id' => $this->getResultData()->TrackingId
            )
        );
    }
    public function getProductTrackingJson() {

        return json_encode($this->getProductHrefList());
    }
    public function getHawkResponse($method, $url, $data = null)
    {
        $client = new Zend_Http_Client();

        $client->setUri($this->getApiUrl() . $url);
        $client->setMethod($method);
        if (isset($data)) {
            $client->setRawData($data, 'application/json');
        }
        $client->setHeaders('X-HawkSearch-ApiKey', $this->getApiKey());
        $client->setHeaders('Accept', 'application/json');
        $this->log(sprintf('fetching request. URL: %s, Method: %s', $client->getUri(), $method));
        $response = $client->request();
        return $response->getBody();
    }

	public function getLPCacheKey() {
		return self::LP_CACHE_KEY . Mage::app()->getStore()->getId();
	}
    public function getLandingPages($force = false)
    {
        /** @var Varien_Cache_Core $cache */
        $cache = Mage::app()->getCache();
        $lpstr = $cache->load($this->getLPCacheKey());
        if (empty($lpstr) or $force) {
            $this->landingPages = json_decode($this->getHawkResponse(Zend_Http_Client::GET, 'LandingPage/Urls'));
            sort($this->landingPages, SORT_STRING);
            $cache->save(serialize($this->landingPages), $this->getLPCacheKey(), array(), Mage::getStoreConfig('landingpage_cache_life'));
        } else {
            $this->landingPages = unserialize($lpstr);
        }
        return $this->landingPages;
    }

    public function setIsHawkManaged($im)
    {
        $this->isManaged = $im;
    }

    public function getIsHawkManaged($path = null)
    {
        if (empty($path)) {
            return $this->isManaged;
        }
        //$this->rewriteRequestPath = $path;
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }
        $hs = $this->getLandingPages();
        $low = 0;
        $high = count($hs) - 1;
        while ($low <= $high) {
            $p = (int)floor(($high + $low) / 2);
            $sc = strcmp($hs[$p], $path);
            if ($sc == 0) {
                $this->isManaged = true;
                return true;
            } elseif ($sc < 0) {
                $low = $p + 1;
            } else {
                $high = $p - 1;
            }
        }
        $this->isManaged = false;
        return $this->isManaged;
    }

    public function getCategoryStoreId()
    {
        $code = Mage::getStoreConfig('hawksearch_proxy/proxy/store_code');

        /** @var Mage_Core_Model_Resource_Store_Collection $store */
        $store = Mage::getModel('core/store')->getCollection();
        return $store->addFieldToFilter('code', $code)->getFirstItem()->getId();

    }

    public function addLandingPage($cid)
    {
        $sid = $this->getCategoryStoreId();
        $cat = Mage::getModel('catalog/category')->setStoreId($sid)->load($cid);
        $lpObject = $this->getLandingPageObject(
            $cat->getName(),
            $this->getHawkCategoryUrl($cat),
            $this->getHawkNarrowXml($cat->getId()),
			$cid
        );

        $this->log(sprintf("going to add landing page for landing page %s with id %d", $lpObject['CustomUrl'], $cat->getId()));
        $resp = $this->getHawkResponse(Zend_Http_Client::GET, 'LandingPage/Url/' . $lpObject['CustomUrl']);
        if (empty($resp)) {
            $this->log('getHawkResponse did not return any value for last request');
            throw new Exception('No response from hawk, unable to proceed');
        }

        $po = json_decode($resp);
        if (isset($po->PageId)) {
            $this->log(sprintf('pageid: %d, raw resp: %s', $po->PageId, $resp));
            $lpObject['PageId'] = $po->PageId;
            $resp = $this->getHawkResponse(Zend_Http_Client::PUT, 'LandingPage/' . $po->PageId, json_encode($lpObject));
        } else {
            $resp = $this->getHawkResponse(Zend_Http_Client::POST, 'LandingPage/', json_encode($lpObject));
        }
        $this->log(sprintf('posted: %s', json_encode($lpObject)));
        $this->log(sprintf('response: %s', $resp));
    }

    private function getLandingPageObject($hawk, $mage) {
        $obj = array();
        if($hawk) {
            foreach ($hawk as $key => $value) {
                $obj[$key] = $value;
            }
            $obj['Name'] = $mage['Name'];
            $obj['NarrowXml'] = $this->getHawkNarrowXml($mage['catid']);
            return $obj;
        }
        return array(
            'PageId' => 0,
            'Name' => $mage['Name'],
            'CustomUrl' => $mage['CustomUrl'],
            'IsFacetOverride' => false,
            'SortFieldId' => 0,
            'SortDirection' => 'Asc',
            'SelectedFacets' => array(),
            'NarrowXml' => $this->getHawkNarrowXml($mage['catid']),
            'Custom' => "__mage_catid_{$mage['catid']}__"
        );
    }


    public function removeLandingPage($cid)
    {
        $sid = $this->getCategoryStoreId();
        $cat = Mage::getModel('catalog/category')->setStoreId($sid)->load($cid);

        $urlpath = $this->getHawkCategoryUrl($cat);
        $this->log("going to remove landing page for catid: {$cat->getId()} and url {$urlpath}");
        $res = $this->getHawkResponse(Zend_Http_Client::DELETE, 'LandingPage/Url/' . $urlpath);
        $this->log('remove got result: ' . $res);
    }

    private function getHawkNarrowXml($id)
    {
        $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><Rule xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" RuleType="Group" Operator="All" />');
        $rules = $xml->addChild('Rules');
        $rule = $rules->addChild('Rule');
        $rule->addAttribute('RuleType', 'Eval');
        $rule->addAttribute('Operator', 'None');
        $rule->addChild('Field', 'facet:category_id');
        $rule->addChild('Condition', 'is');
        $rule->addChild('Value', $id);
        $xml->addChild('Field');
        $xml->addChild('Condition');
        $xml->addChild('Value');
        return $xml->asXML();
    }

    public function getHawkCategoryUrl(Mage_Catalog_Model_Category $cat)
    {
        $fullUrl = Mage::helper('catalog/category')->getCategoryUrl($cat);
        $base = Mage::app()->getStore()->getBaseUrl();
        $url = substr($fullUrl, strlen($base) - 1);
        $this->log(sprintf('full %s', $fullUrl));
        if (substr($url, 0, 1) != '/') {
            $url = '/' . $url;
        }
        return $url;
    }

    private function syncHawkLandingByStore(Mage_Core_Model_Store $store)
    {
        Mage::reset();
        Mage::app();

        $this->log(sprintf('Starting environment for store %s', $store->getName()));
        /** @var Mage_Core_Model_App_Emulation $appEmulation */
        $appEmulation = Mage::getModel('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());
        $this->log('starting synchronizeHawkLandingPages()');

        $hawkList = $this->getHawkLandingPages();

        $this->log(sprintf('got %d hawk managed landing pages', count($hawkList)));

        $mageList = $this->getMagentoLandingPages();

        usort($hawkList, function ($a, $b) {
            return strcmp($a->CustomUrl, $b->CustomUrl);
        });
        usort($mageList, function ($a, $b) {
            return strcmp($a['CustomUrl'], $b['CustomUrl']);
        });


        $left = 0; //hawk on the left
        $right = 0; //magento on the right
        while ($left < count($hawkList) || $right < count($mageList)) {
            if ($left >= count($hawkList)) {
                //only right left to process
                $sc = 1;
            } elseif ($right >= count($mageList)) {
                // only left left to process
                $sc = -1;
            } else {
                $sc = strcmp($hawkList[$left]->CustomUrl, $mageList[$right]['CustomUrl']);
            }
            if ($sc < 0) {
                //Hawk has page Magento doesn't want managed, delete, increment left
				if(substr($hawkList[$left]->Custom, 0, strlen('__mage_catid_')) == '__mage_catid_') {
					$resp = $this->getHawkResponse(Zend_Http_Client::DELETE, 'LandingPage/' . $hawkList[$left]->PageId);
					$this->log(sprintf('attempt to remove page %s resulted in: %s', $hawkList[$left]->CustomUrl, $resp));
				} else {
					$this->log(sprintf('Customer custom landing page "%s", skipping', $hawkList[$left]->CustomUrl));
				}
                $left++;
            } elseif ($sc > 0) {
                //Mage wants it managed, but hawk doesn't know, POST and increment right
                $lpObject = $this->getLandingPageObject(null, $mageList[$right]);
                $resp = $this->getHawkResponse(Zend_Http_Client::POST, 'LandingPage/', json_encode($lpObject));
                $this->log(sprintf('attempt to add page %s resulted in: %s', $mageList[$right]['CustomUrl'], $resp));
                $right++;
            } else {
                //they are the same, PUT value to cover name changes, etc. increment both sides
                $lpObject = $this->getLandingPageObject($hawkList[$left], $mageList[$right]);
                $resp = $this->getHawkResponse(Zend_Http_Client::PUT, 'LandingPage/' . $hawkList[$left]->PageId, json_encode($lpObject));
                $this->log(sprintf('attempt to update page %s resulted in %s', $hawkList[$left]->CustomUrl, $resp));
                $left++;
                $right++;
            }
        }

        // end emulation
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
    }

    public function synchronizeHawkLandingPages()
    {
        try {
            /** @var Mage_Core_Model_Resource_Store_Collection $stores */
            $stores = Mage::getModel('core/store')->getCollection();

            /** @var Mage_Core_Model_Store $store */
            foreach ($stores as $store) {
                if ($store->getConfig('hawksearch_proxy/general/enabled')) {
                    $this->syncHawkLandingByStore($store);
                }
            }

        } catch (Exception $e) {
            $this->log(sprintf('there has been an error: %s', $e->getMessage()));
        }
    }

    public function getHawkLandingPages()
    {
        $hawkPages = array();
        $pages = json_decode($this->getHawkResponse(Zend_Http_Client::GET, 'LandingPage'));
        foreach ($pages as $page) {
            if (empty($page->Custom))
                continue;
            $hawkPages[] = $page;
        }

        return $hawkPages;
    }

    public function getMagentoLandingPages()
    {
        $this->log('getting magento landing pages...');

        /** @var Mage_Catalog_Helper_Category $helper */
        $helper = Mage::helper('catalog/category');

        /** @var Mage_Catalog_Model_Resource_Category_Collection $categories */
        $categories = $helper->getStoreCategories(false, true, false);

        $categories->addAttributeToSelect('hawk_landing_page');
        if (!Mage::getStoreConfigFlag('hawksearch_proxy/proxy/manage_all')) {
            $categories->addAttributeToFilter('hawk_landing_page', array('eq' => '1'));
        }
        $categories->addAttributeToSort('entity_id')
            ->addAttributeToSort('parent_id')
            ->addAttributeToSort('position');

        $cats = array();
        $categories->setPageSize(1000);
        $pages = $categories->getLastPageNumber();
        $currentPage = 1;

        do {
            $categories->clear();
            $categories->setCurPage($currentPage);
            $categories->load();
            /** @var Mage_Catalog_Model_Category $cat */
            foreach ($categories as $cat) {
                $suff = trim($helper->getCategoryUrlSuffix(), ".");
                $path = $cat->getRequestPath();
                if(!empty($suff)) {
                    $path = implode(".", [$path, $suff]);
                }
                $cats[] = array(
                    'CustomUrl' => sprintf("/%s", $path),
                    'Name' => $cat->getName(),
                    'catid' => $cat->getId(),
                    'pid' => $cat->getParentId()
                );
            }
            $currentPage++;
        } while ($currentPage <= $pages);

        return $cats;
    }

    public function log($message)
    {
        if ($this->isLoggingEnabled()) {
            Mage::log("HAWKSEARCH: $message", null, 'hawkproxy.log');
        }
    }

    public function getManageAll()
    {
        return Mage::getStoreConfigFlag('hawksearch_proxy/proxy/manage_all');
    }

    public function isLoggingEnabled()
    {
		return Mage::getStoreConfigFlag('hawksearch_proxy/general/logging_enabled', $this->store);
    }

    public function getAjaxNotice($force = true)
    {
        /** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToFilter('parent_id', array('neq' => '0'));
        $collection->addAttributeToFilter('hawk_landing_page', array('eq' => '1'));
        $collection->addAttributeToFilter('is_active', array('neq' => '0'));
        $collection->addAttributeToFilter('display_mode', array('neq' => Mage_Catalog_Model_Category::DM_PAGE));
        $count = $collection->count();

        $fs = '';
        if ($force) {
            $fs = " Check 'force' to remove lock and restart.";
        }
        return sprintf('<span style=\"color:red;\">Currently synchronizing %d categories.%s</span>', $count, $fs);
    }

    public function isSyncLocked()
    {
        $this->log('checking for sync lock');
        $path = $this->getSyncFilePath();
        $filename = implode(DS, array($path, self::LOCK_FILE_NAME));
        if (is_file($filename)) {
            $this->log('category sync lock file found, returning true');
            return true;
        }
        return false;
    }

    public function launchSyncProcess()
    {
        $this->log('launching new sync process');
        $tmppath = sys_get_temp_dir();
        $tmpfile = tempnam($tmppath, 'hawkproxy_');

        $parts = explode(DIRECTORY_SEPARATOR, __FILE__);
        array_pop($parts);
        $parts[] = 'runsync.php';
        $runfile = implode(DIRECTORY_SEPARATOR, $parts);
        $root = getcwd();

        $this->log("going to open new shell script: $tmpfile");
        $f = fopen($tmpfile, 'w');
        fwrite($f, '#!/bin/sh' . "\n");
        $phpbin = PHP_BINDIR . DIRECTORY_SEPARATOR . "php";

        $this->log("writing script: $phpbin $runfile -r $root -t $tmpfile");
        fwrite($f, "$phpbin $runfile -r $root -t $tmpfile\n");

        $this->log('going to execute script');
        shell_exec("/bin/sh $tmpfile > /tmp/hawklog.txt 2>&1 &");
        $this->log('sync script launched');
    }

    public function getSyncFilePath()
    {
        $this->log('getting feed file path');
        if ($this->_feedFilePath === null) {
            $this->log('path is null, checking/creating');
            $this->_feedFilePath = $this->makeVarPath(array('hawksearch', 'proxy'));
        }
        $this->log("returning feed file path: {$this->_feedFilePath}");
        return $this->_feedFilePath;
    }

    /**
     * Create path within var folder if necessary given an array of directory names
     *
     * @param array $directories
     * @return string
     */
    public function makeVarPath($directories)
    {
        $path = Mage::getBaseDir('var');
        foreach ($directories as $dir) {
            $path .= DS . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }
        }
        return $path;
    }

    public function createSyncLocks()
    {
        $this->log('going to create proxy lock file');
        $path = $this->getSyncFilePath();
        $filename = implode(DS, array($path, self::LOCK_FILE_NAME));
        $content = date("Y-m-d H:i:s");

        if (file_put_contents($filename, $content) === false) {
            $this->log("Unable to write lock file, returning false!");
            return false;
        }
        return true;

    }

    public function removeSyncLocks()
    {
        $path = $this->getSyncFilePath();
        $filename = implode(DS, array($path, self::LOCK_FILE_NAME));

        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }

    public function getSearchBoxes()
    {
        $sbids = explode(',', Mage::getStoreConfig('hawksearch_proxy/proxy/search_box_ids'));
        foreach ($sbids as $id) {
            $id = trim($id);
        }
        return $sbids;
    }

    public function getAutoSuggestParams()
    {
        return Mage::getStoreConfig('hawksearch_proxy/proxy/autosuggest_params');
    }

    public function getHiddenDivName()
    {
        return Mage::getStoreConfig('hawksearch_proxy/proxy/hidden_div');
    }

    private function getApiVersionNumber()
    {
        return Mage::getStoreConfig('hawksearch_proxy/proxy/api_version_number');
    }

    public function decorateCategory($category)
    {
        if (empty($this->hawkData)) {
            $this->fetchResponse();
        }
        if(isset($this->hawkData->PageHeading)){
            $category->setName($this->hawkData->PageHeading);
        }
        if(isset($this->hawkData->CustomHtml)) {
            $category->setDescription($this->hawkData->CustomHtml);
        }
        if(isset($this->hawkData->MetaDescription) ){
            $category->setMetaDescription(preg_replace('|<meta name="description" content="(.*)"/>|', '$1', $this->hawkData->MetaDescription));
        }
        if(isset($this->hawkData->MetaKeywords)) {
            $category->setMetaKeywords(preg_replace('|<meta name="keywords" content="(.*)"/>|','$1', $this->hawkData->MetaKeywords));
        }
        if($this->hawkData->HeaderTitle) {
            $category->setMetaTitle(strip_tags($this->hawkData->HeaderTitle));
        }
        if($this->hawkData->RelCanonical) {
            $category->setUrl(preg_replace('|<link rel="canonical" href="(.*?)" />.*|', '$1', $this->hawkData->RelCanonical));
        }
    }
}
