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
class Hawksearch_Datafeed_SearchController extends Mage_Core_Controller_Front_Action {

    public function getCacheKeyAction() {
		$response = array("error" => false);
		try {
			/** @var Mage_Catalog_Model_Resource_Product_Collection $coll */
			$coll = Mage::getModel('catalog/product')->getCollection();
			$coll->addAttributeToSelect('small_image');
			$coll->addAttributeToFilter('small_image', array(
				'notnull' => true
			));
			$coll->getSelect()->limit(100);
			$item = $coll->getLastItem();
			$path = (string)Mage::helper('catalog/image')->init($item, 'small_image');
			$imageArray = explode("/", $path);
			$cache_key = "";
			foreach ($imageArray as $part) {
				if (preg_match('/[0-9a-fA-F]{32}/', $part)) {
					$cache_key = $part;
				}
			}

			$response['cache_key'] = $cache_key;
			$response['date_time'] = date('Y-m-d H:i:s');
		} catch (Exception $e) {
			$response['error'] = $e->getMessage();
		}
		$this->getResponse()
			->setHeader("Content-Type", "application/json")
			->setBody(json_encode($response));
	}

}