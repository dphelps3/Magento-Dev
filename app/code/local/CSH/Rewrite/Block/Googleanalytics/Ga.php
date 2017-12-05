<?php
/**
 * Sitesquad - Refactoring modifications to core Magento files
 *
 * =============================================================================
 * NOTE: See README.txt for more information about this extension
 * =============================================================================
 *
 * @category   CSH
 * @package    CSH_Rewrite
 * @copyright  Copyright (c) 2015 Sitesquad. (http://www.sitesquad.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Phil Mobley <phil.mobley@sitesquad.net>
 */

/**
 * Modifies the core functionality by:
 *
 * 1) Adding "_setSiteSpeedSampleRate" token value to tracking code
 * 2) Modifying the Order Tracking Code to include product category info
 */
class CSH_Rewrite_Block_Googleanalytics_Ga
    extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Caches results from categories associated with products (to avoid loading
     * the same category multiple times)
     *
     * KEY: category_id
     * VAL: category->getName()
     *
     * @var array
     */
    protected $_category = array();
    
    
    /**
     * Render regular page tracking javascript code
     * The custom "page name" may be set from layout or somewhere else. It must start from slash.
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._trackPageview
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gaq.html
     * @param string $accountId
     * @return string
     */
    protected function _getPageTrackingCode($accountId)
    {
        $pageName   = trim($this->getPageName());
        $optPageURL = '';
        if ($pageName && preg_match('/^\/.*/i', $pageName)) {
            $optPageURL = ", '{$this->jsQuoteEscape($pageName)}'";
        }
        return "
_gaq.push(['_setAccount', '{$this->jsQuoteEscape($accountId)}']);
_gaq.push(['_setSiteSpeedSampleRate', 50]);
_gaq.push(['_trackPageview'{$optPageURL}]);
";
    }

    
    /**
     * Render information about specified orders and their items
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
     * @return string
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $order->getBaseGrandTotal(),
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount(),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCity())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getRegion())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCountry()))
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                    $this->_getOrderItemCategories($item), // NEW: loads products for the categories
                    $item->getBasePrice(), $item->getQtyOrdered()
                );
            }
            $result[] = "_gaq.push(['_trackTrans']);";
        }
        return implode("\n", $result);
    }
    
    
    /**
     * Gets the category names from the order item (in order to submit to GA)
     *
     * @param  Mage_Sales_Model_Order_Item $item
     * @return string|NULL
     */
    protected function _getOrderItemCategories($item)
    {
        $categoryList = null;
        
        try {
            $model = Mage::getModel('catalog/product');
            
            if ($item->getProductId()) {
                $pid = $item->getProductId();
            } else {
                $pid = $model->getIdBySku($item->getSku());
            }
            
            if ($pid) {
                $model->load($pid);
                
                $categoryList   = '';
                $cats           = $model->getCategoryCollection()->exportToArray();
                
                // refactored this code to use cache -- avoid loading same
                // category multiple times
                foreach ($cats as $cat) {
                    $catId = @$cat['entity_id'];
                    
                    if ($catId) {
                        $name = null;
                        
                        if (array_key_exists($catId, $this->_category)) {
                            $name = $this->_category[$catId];

                        } else {
                            $category = Mage::getModel('catalog/category')->load($catId);
                            
                            if ($category->getId()) {
                                $name = $category->getName();
                                $this->_category[$catId] = $name;
                            } else {
                                $this->_category[$catId] = false;
                            }
                        }
                        
                        if (!empty($name)) {
                            $categoryList .= $name . '|';
                        }
                    }
                }
                
                $categoryList = str_replace('||', '|', $categoryList);
                $categoryList = trim($categoryList," |");
            }
            
        } catch (Exception $e) {
            $categoryList = null;
        }
        
        if (empty($categoryList)) {
            $categoryList = null;
        }
        
        return $categoryList;
    }
}
