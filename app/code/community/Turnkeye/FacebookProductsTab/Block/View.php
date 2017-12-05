<?php
/**
 * TurnkeyE Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send
 * an email to info@turnkeye.com so we can send you a copy immediately.
 *
 * @category   Turnkeye
 * @package    Turnkeye_FacebookProductsTab
 * @copyright  Copyright (c) 2010-2012 TurnkeyE Co. (http://turnkeye.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tab view block
 *
 * @method Mage_Catalog_Model_Resource_Product_Collection getProducts()
 *
 * @category   Turnkeye
 * @package    Turnkeye_FacebookProductsTab
 * @author     Viacheslav Fedorenko <v.fedorenko@turnkeye.com>
 */

class Turnkeye_FacebookProductsTab_Block_View extends Mage_Catalog_Block_Product_Abstract
{

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return Turnkeye_FacebookProductsTab_Block_View
     */
    protected function _beforeToHtml()
    {
        $this->_prepareCollection();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare product collection object
     *
     * @return Turnkeye_FacebookProductsTab_Block_View
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addAttributeToFilter('is_facebook', 1)
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('small_image')
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
//			->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
        ;
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        $collection->load();

        Mage::getModel('review/review')->appendSummary($collection);
        $this->setProducts($collection);
        return $this;
    }

}
?>
