<?php
/**
 * Copyright (c) 2013 Hawksearch (www.hawksearch.com) - All Rights Reserved
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
class Hawksearch_Datafeed_Model_Datafeed extends Mage_Core_Model_Abstract {

	private $feedSummary;
	private $productAttributes;
    private $helper;

	/**
	 * Constructor
	 */
	function __construct() {
		/** @var $helper Hawksearch_Datafeed_Helper_Data */
		$helper = Mage::helper('hawksearch_datafeed');
        $this->helper = $helper;

        if(!file_exists($this->helper->getFeedFilePath())) {
            mkdir($this->helper->getFeedFilePath(), 0777, true);
        }

		$this->feedSummary = new stdClass();
		$this->productAttributes = array('entity_id', 'sku', 'name', 'url', 'small_image', 'msrp', 'price', 'special_price', 'special_from_date', 'special_to_date', 'short_description', 'description', 'meta_keyword', 'qty');

		parent::__construct();
	}

	/**
	 * Adds a log entry to the hawksearch proxy log. Logging must
	 * be enabled for both the module and Magneto
	 * (See System >> Configuration >> Developer >> Log Settings
	 *
	 * @param $message
	 */
	public function log($message) {
		if ($this->helper->loggingIsEnabled()) {
			Mage::log("HAWKSEARCH: $message", null, 'hawksearch.log', true);
		}
	}

    private function getPathForFile($basename) {
        $dir = sprintf('%s/%s', $this->helper->getFeedFilePath(), end($this->feedSummary->stores));
        $this->log(sprintf('checking for dir: %s', $dir));
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return sprintf('%s/%s.%s', $dir, $basename, $this->helper->getOutputFileExtension());
    }

    /**
     * Recursively sets up the category tree without introducing
     * duplicate data.
     *
     * @param $pid
     * @param $all
     * @param $tree
     */
    private function r_find($pid, &$all, &$tree) {
        foreach ($all as $item) {
            if ($item['pid'] == $pid) {
                $tree[] = $item;
                $this->r_find($item['id'], $all, $tree);
            }
        }
    }

	/**
	 * @return bool
	 * @throws Mage_Core_Exception
	 */
	private function getCategoryData(Mage_Core_Model_Store $store) {
		$this->log('starting _getCategoryData()');
		$filename = $this->getPathForFile('hierarchy');

		/** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
		/** @var Mage_Catalog_Model_Category $categoryModel */
		$categoryModel = Mage::getModel('catalog/category');
		$collection = $categoryModel->getCollection();
		$collection->addAttributeToSelect(array('name', 'is_active', 'parent_id', 'position', 'include_in_menu'));
		$collection->addAttributeToFilter('is_active', array('eq' => '1'));
		$collection->addAttributeToSort('entity_id')->addAttributeToSort('parent_id')->addAttributeToSort('position');


		$collection->setPageSize($this->helper->getBatchLimit());
		$pages = $collection->getLastPageNumber();
		$currentPage = 1;

		$this->log(sprintf('going to open feed file %s', $filename));
		$output = new CsvWriter($filename, $this->helper->getFieldDelimiter(), $this->helper->getBufferSize());
		$this->log('file open, going to append header and root');
		$output->appendRow(array('category_id', 'category_name', 'parent_category_id', 'sort_order', 'is_active', 'category_url', 'include_in_menu'));
		$output->appendRow(array('1', 'Root', '0', '0', '1', '/', '1'));
		$this->log('header and root appended');
		$base = $store->getBaseUrl();

		$cats = array();
		do {
			$this->log(sprintf('getting category page %d', $currentPage));
			$collection->setCurPage($currentPage);
			$collection->clear();
			$collection->load();
			foreach ($collection as $cat) {
				$fullUrl = Mage::helper('catalog/category')->getCategoryUrl($cat);
				$category_url = substr($fullUrl, strlen($base));
				if (substr($category_url, 0, 1) != '/') {
					$category_url = '/' . $category_url;
				}
				//$this->log(sprintf("got full category url: %s, returning relative url %s", $fullUrl, $category_url));
				$cats[] = array(
					'id' => $cat->getId(),
					'name' => $cat->getName(),
					'pid' => $cat->getParentId(),
					'pos' => $cat->getPosition(),
					'ia' => $cat->getIsActive(),
					'url' => $category_url,
					'inmenu' => $cat->getIncludeInMenu()
				);
			}
			$currentPage++;
		} while ($currentPage <= $pages);

		$rcid = $store->getRootCategoryId();
		$myCategories = array();
		foreach ($cats as $storecat) {
			if ($storecat['id'] == $rcid) {
				$myCategories[] = $storecat;
			}
		}

		$this->log("using root category id: $rcid");
		$this->r_find($rcid, $cats, $myCategories);

		foreach ($myCategories as $final) {
			$output->appendRow(array(
				$final['id'],
				$final['name'],
				$final['pid'],
				$final['pos'],
				$final['ia'],
				$final['url'],
				$final['inmenu']
			));
		}

		$this->log('done with _getCategoryData()');
		return true;
	}

    private function getAttributeData(Mage_Core_Model_Store $store) {
        $this->log('starting _getAttributeData');
        $filename = $this->getPathForFile('attributes');
        $labelFilename = $this->getPathForFile('labels');

        $this->log(sprintf('exporting attribute labels for store %s', $store->getName()));
        $start = time();
        /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $pac */
        $pac = Mage::getResourceModel('catalog/product_attribute_collection');

        // change to using the new attribute setting:
        $pac->getSelect()->where(
            'additional_table.is_hawksearch = 1 OR ' .
            $pac->getConnection()->quoteInto('main_table.attribute_code IN (?)', array('status', 'visibility'))
        );

        $pac->addStoreLabel($store->getId());
        $attributes = array();

        $labels = new CsvWriter($labelFilename, $this->helper->getFieldDelimiter(), $this->helper->getBufferSize());
        $labels->appendRow(array('key', 'store_label'));
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $att */
        foreach ($pac as $att) {
            $attributes[$att->getAttributeCode()] = $att;
            $labels->appendRow(array($att->getAttributeCode(), $att->getStoreLabel()));
        }
        $labels->closeOutput();
        $this->log(sprintf('Label export took %d seconds', time() - $start));

        /** @var Mage_Catalog_Model_Resource_Product_Collection $products */
        $products = Mage::getModel('hawksearch_datafeed/product')->getCollection();
        $feedCodes = array_diff(array_keys($attributes), $this->productAttributes);
        if(!in_array('sku', $feedCodes)) {
            array_push($feedCodes, 'sku');
        }
        $this->log(sprintf('searchable atts: %s', implode(', ', array_keys($attributes))));
        $this->log(sprintf('adding attributes to select: %s', implode(', ', $feedCodes)));
        $products->addAttributeToSelect($feedCodes);
//		$products->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $products->addStoreFilter($store);

        if (!$this->helper->includeDisabledItems()) {
            $this->log('adding status filter');
            $products->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        }

        if ( !$this->helper->includeOutOfStockItems()) {
            $this->log('adding out of stock filter');
            /** @var Mage_CatalogInventory_Model_Stock $stockfilter */
            $stockfilter = Mage::getSingleton('cataloginventory/stock');
            $stockfilter->addInStockFilterToCollection($products);
        }

        $this->log(sprintf('going to open feed file %s', $filename));
        $output = new CsvWriter($filename, $this->helper->getFieldDelimiter(), $this->helper->getBufferSize());
        $this->log('feed file open, appending header');
        $output->appendRow(array('unique_id', 'key', 'value'));

        $products->setPageSize($this->helper->getBatchLimit());
        $pages = $products->getLastPageNumber();
        $currentPage = 1;

        /** @var Mage_Review_Model_Review $review */
        $review = Mage::getSingleton('review/review');

        do{
            $this->log(sprintf('starting attribute export for page %d', $currentPage));
            $start = time();
            $products->setCurPage($currentPage);
            $products->clear();
            $review->appendSummary($products);
            $products->load();
            foreach ($products as $product) {
                foreach ($feedCodes as $attcode) {
                    if($product->getData($attcode) === null) {
                        continue;
                    }
                    $source = $attributes[$attcode]->getSource();
                    if($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Table){
//						TODO: These table based items need to be broken into separate line items
                        $output->appendRow(array(
                            $product->getSku(),
                            $attcode,
                            $product->getResource()->getAttribute($attcode)->getFrontend()->getValue($product)
                        ));
                    } elseif($source instanceof Mage_Catalog_Model_Product_Visibility
                        || $source instanceof Mage_Tax_Model_Class_Source_Product
                        || $source instanceof Mage_Catalog_Model_Product_Status	) {
                        $output->appendRow(array(
                            $product->getSku(),
                            $attcode,
                            $source->getOptionText($product->getData($attcode))
                        ));
                    } else {
                        $output->appendRow(array(
                            $product->getSku(),
                            $attcode,
                            $product->getData($attcode)
                        ));
                    }
                }
                foreach ($product->getCategoryIds() as $id) {
                    $output->appendRow(array($product->getSku(), 'category_id', $id));
                }
                if (($rs = $product->getRatingSummary()) && $rs->getReviewsCount() > 0) {
                    $output->appendRow(array($product->getSku(), 'rating_summary', $rs->getRatingSummary()));
                    $output->appendRow(array($product->getSku(), 'reviews_count', $rs->getReviewsCount()));
                }
            }

            $this->log(sprintf('page %d took %d seconds to export', $currentPage, time() - $start));
            $currentPage++;
        } while($currentPage <= $pages);
    }

	private function getProductData(Mage_Core_Model_Store $store) {
		/** @var Mage_Catalog_Model_Resource_Product_Collection $products */
		$products = Mage::getModel('hawksearch_datafeed/product')->getCollection();
		$products->addAttributeToSelect($this->productAttributes);
		//$products->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
		$products->addMinimalPrice();
		$products->addStoreFilter($store);

		if (!$this->helper->includeDisabledItems()) {
			$this->log('adding status filter');
			$products->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		}

		if (!$this->helper->includeOutOfStockItems()) {
			$this->log('adding out of stock filter');
			/** @var Mage_CatalogInventory_Model_Stock $stockfilter */
			$stockfilter = Mage::getSingleton('cataloginventory/stock');
			$stockfilter->addInStockFilterToCollection($products);
		}

		// taken from the product grid collection:
		if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
			$products->joinField('qty',
					'cataloginventory/stock_item',
					'qty',
					'product_id=entity_id',
					'{{table}}.stock_id=1',
					'left');
		}

		$filename = $this->getPathForFile('items');
		$output = new CsvWriter($filename, $this->helper->getFieldDelimiter(), $this->helper->getBufferSize());
		$output->appendRow(array(
			'product_id',
			'unique_id',
			'name',
			'url_detail',
			'image',
			'price_retail',
			'price_sale',
			'price_special',
			'price_special_from_date',
			'price_special_to_date',
			'group_id',
			'description_short',
			'description_long',
			'brand',
			'sku',
			'sort_default',
			'sort_rating',
			'is_free_shipping',
			'is_new',
			'is_on_sale',
			'keyword',
			'metric_inventory',
			'minimal_price',
            'type_id'));

		$products->setPageSize($this->helper->getBatchLimit());
		$pages = $products->getLastPageNumber();
		$currentPage = 1;

		do {
			$this->log(sprintf('Starting product page %d', $currentPage));
			$products->setCurPage($currentPage);
			$products->clear();
			$start = time();
			$products->load();
			$seconds = time() - $start;
			$this->log(sprintf('it took %d seconds to load product page %d', $seconds, $currentPage));
			$start = time();
			/** @var Mage_Catalog_Model_Product $product */
			foreach ($products as $product) {
				$output->appendRow(array(
					$product->getId(),
					$product->getSku(),
					$product->getName(),
					substr($product->getProductUrl(1), strlen($store->getBaseUrl())),
					$product->getSmallImage(),
					$product->getMsrp(),
					$product->getPrice(),
					$product->getSpecialPrice(),
					$product->getSpecialFromDate(),
					$product->getSpecialToDate(),
					$this->getGroupId($product),
					$product->getShortDescription(),
					$product->getDescription(),
					'',
					$product->getSku(),
					'',
					'',
					'',
					'',
					$product->getSpecialPrice() ? 1 : 0,
					$product->getMetaKeyword(),
					$product->getQty(),
					$product->getMinimalPrice(),
                    $product->getTypeId()
				));
			}
			$this->log(sprintf('it took %d seconds to export page %d', time() - $start, $currentPage));
			$currentPage++;
		} while ($currentPage <= $pages);

		$this->log('done with _getProductData()');
	}

	private function getGroupId(Mage_Catalog_Model_Product $product) {
		if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
			$vals =implode(",", Mage::getResourceSingleton('catalog/product_type_configurable')
				->getParentIdsByChild($product->getId()));
			if(!empty($vals)){
				return $vals;
			}
		}
		return $product->getId();
	}

    private function getContentData(Mage_Core_Model_Store $store){
		$this->log('starting getContentData()');
		$collection = Mage::getModel('cms/page')->getCollection();
		$collection->addStoreFilter($store->getId());

		$output = new CsvWriter($this->getPathForFile('content'), $this->helper->getFieldDelimiter(), $this->helper->getBufferSize());
		$output->appendRow(array('unique_id', 'name', 'url_detail', 'description_short', 'created_date'));

		foreach ($collection as $page) {
			$output->appendRow(array(
				$page->getPageId(),
				$page->getTitle(),
				sprintf('%s%s', $store->getBaseUrl(), $page->getIdentifier()),
				$page->getContentHeading(),
				$page->getCreationTime()
			));
		}
		$this->log('done with getting content data');
	}

	public function getCountryName($code) {
		/* map friendly country_of_origin names */
		if (!isset($this->countryMap)) {
			$options = Mage::getModel('directory/country')->getResourceCollection()->toOptionArray();

			$this->countryMap = array();
			foreach ($options as $option) {
				if ($option['value'] != '') {
					$this->countryMap[$option['value']] = $option['label'];
				}
			}
		}
		return isset($this->countryMap[$code]) ? $this->countryMap[$code] : $code;
	}

    public function cronGenerateDatafeed(){
        if ($this->helper->getCronEnabled()) {
            if($this->helper->isFeedLocked()){
                $message = "Hawksearch Datafeed is currently locked, not generating feed at this time.";
            } else {
                try {
                    $this->helper->createFeedLocks();
                    $this->generateFeed();
                    $message = "HawkSeach Datafeed Generated!";
                } catch (Exception $e) {
                    $message = sprintf('There has been an error: %s', $e->getMessage());
                    $this->helper->removeFeedLocks();
                }
            }
            /** @var Mage_Core_Model_Email_Template $email */
            $email = Mage::getModel('core/email_template');
            $email->loadDefault('hawksearch_datafeed_cronemail');

            $email->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
            $email->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
            $email->send(
                $this->helper->getCronEmail(),
                $this->helper->getCronEmail(),
                array('message' => $message)
            );
        }

    }

	public function generateFeed() {
		$selectedStores = $this->helper->getSelectedStores();
		/** @var Mage_Core_Model_Resource_Store_Collection $stores */
		$stores = Mage::getModel('core/store')->getCollection();
		$stores->addIdFilter($selectedStores);
		/** @var Mage_Core_Model_Store $store */
		foreach ($stores as $store) {
			try {
				Mage::reset();
				Mage::app();

				$this->log(sprintf('Starting environment for store %s', $store->getName()));
				$appEmulation = Mage::getModel('core/app_emulation');
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());

				$this->log(sprintf('Setting feed folder for store_code %s', $store->getCode()));
				$this->setFeedFolder($store);

				//exports Category Data
				$this->getCategoryData($store);

				//exports Product Data
				$this->getProductData($store);

				//exports Attribute Data
				$this->getAttributeData($store);

				//exports CMS / Content Data
				$this->getContentData($store);

				// end emulation
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

			} catch (Exception $e) {
				$this->log(sprintf("General Eception %s at generateFeed() line %d, stack:\n%s", $e->getMessage(), $e->getLine(), $e->getTraceAsString()));
                throw $e;
			}

		}
		$this->log(sprintf('going to write summary file %s', $this->helper->getSummaryFilename()));
		file_put_contents($this->helper->getSummaryFilename(), json_encode($this->feedSummary));
		$this->log('done generating data feed files, going to remove lock files.');
		$this->helper->removeFeedLocks();
		$this->log('all done, goodbye');

	}

	public function setFeedFolder(Mage_Core_Model_Store $store) {
		$this->feedSummary->stores[] = $store->getCode();
	}

    public function cronGenerateImagecache(){
        if ($this->helper->getCronEnabled()) {
            if($this->helper->isFeedLocked()){
                $message = "Hawksearch Datafeed is currently locked, not generating feed at this time.";
            } else {
                try {
                    $this->helper->createFeedLocks();
                    $this->refreshImageCache();
                    $message = "HawkSeach Imagecache Generated!";
                } catch (Exception $e) {
                    $message = sprintf('There has been an error: %s', $e->getMessage());
                    $this->helper->removeFeedLocks();
                }
            }
            /** @var Mage_Core_Model_Email_Template $email */
            $email = Mage::getModel('core/email_template');
            $email->loadDefault('hawksearch_datafeed_cronemail');

            $email->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
            $email->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
            $email->send(
                $this->helper->getCronEmail(),
                $this->helper->getCronEmail(),
                array('message' => $message)
            );
        }


    }

	public function refreshImageCache() {
		$this->log('starting refreshImageCache()');

		$selectedStores = $this->helper->getSelectedStores();
		/** @var Mage_Core_Model_Resource_Store_Collection $stores */
		$stores = Mage::getModel('core/store')->getCollection();
		$stores->addIdFilter($selectedStores);
		/** @var Mage_Core_Model_Store $store */
		foreach ($stores as $store) {
			try {
				$this->log(sprintf('Starting environment for store %s', $store->getName()));
				$appEmulation = Mage::getSingleton('core/app_emulation');
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());

				$products = Mage::getModel('hawksearch_datafeed/product')
					->getCollection()
					->addAttributeToSelect(array('small_image'))
					->addStoreFilter($store);
				$products->setPageSize($this->helper->getBatchLimit());
				$pages = $products->getLastPageNumber();

				$currentPage = 1;

				do {
					$this->log(sprintf('going to page %d of images', $currentPage));
					$products->clear();
					$products->setCurPage($currentPage);
					$products->load();

					foreach ($products as $product) {
                        $height = $this->helper->getImageHeight();
						if (empty($height)) {
							$this->log(
								sprintf('going to resize image for url: %s',
									Mage::helper('catalog/image')->init($product, 'small_image')->resize($this->helper->getImageWidth()
									)));
						} else {
							$this->log(
								sprintf('going to resize image for url: %s',
									Mage::helper('catalog/image')->init($product, 'small_image')->resize($this->helper->getImageWidth(), $this->helper->getImageHeight()
									)));
						}
					}

					$currentPage++;

				} while ($currentPage <= $pages);

				// end emulation
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

			} catch (Exception $e) {
				$this->log(sprintf("General Exception %s at generateFeed() line %d, stack:\n%s", $e->getMessage(), $e->getLine(), $e->getTraceAsString()));
			}

		}
		$this->helper->removeFeedLocks();
		$this->log('Done generating image cache for selected stores, goodbye');
	}

}

/**
 * CsvWriter
 *
 * The purpose of this class is to allow uniform escaping of CSV data via the fputcsv()
 * along with handling the boilerplate of file operations.
 */
class CsvWriter {
	private $finalDestinationPath;
	private $outputFile;
	private $outputOpen = false;
	private $delimiter;
	private $bufferSize;

	public function __construct($destFile, $delim, $buffSize = null) {
		$this->finalDestinationPath = $destFile;
		if (file_exists($this->finalDestinationPath)) {
			if (false === unlink($this->finalDestinationPath)) {
				throw new Exception("CsvWriteBuffer: unable to remove old file '$this->finalDestinationPath'");
			}
		}
		$this->delimiter = $delim;
		$this->bufferSize = $buffSize;
	}

	public function __destruct() {
		$this->closeOutput();
	}

	public function appendRow(array $fields) {
		if (!$this->outputOpen) {
			$this->openOutput();
		}
		foreach ($fields as $k => $f) {
			$fields[$k] = strtr($f, array('\"' => '"'));
		}
		if (false === fputcsv($this->outputFile, $fields, $this->delimiter)) {
			throw new Exception("CsvWriter: failed to write row.");
		}
	}

	public function openOutput() {
		if (false === ($this->outputFile = fopen($this->finalDestinationPath, 'a'))) {
			throw new Exception("CsvWriter: Failed to open destination file '$this->finalDestinationPath'.");
		}
		if (!is_null($this->bufferSize)) {
			stream_set_write_buffer($this->outputFile, $this->bufferSize);
		}
		$this->outputOpen = true;
	}

	public function closeOutput() {
		if ($this->outputOpen) {
			if (false === fflush($this->outputFile)) {
				throw new Exception(sprintf("CsvWriter: Failed to flush feed file: %s", $this->finalDestinationPath));
			}
			if (false === fclose($this->outputFile)) {
				throw new Exception(sprintf("CsvWriter: Failed to close feed file ", $this->finalDestinationPath));
			}
			$this->outputOpen = false;
		}
	}
}
