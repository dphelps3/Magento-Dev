<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2012 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


/**
 * Sales Order items qty column renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Webshopapps_Dropship_Block_Adminhtml_Sales_Items_Warehouse extends Mage_Adminhtml_Block_Sales_Order_View_Items
{
		
	public function getSimpleWarehouseText() {
  
        $items = $this->getItemsCollection();
        $itemWarehouses = array();
        foreach ($items as $item) {
        	if ($item->getParentItem() || $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                continue;  
        	}
            $itemWarehouses[] = Mage::helper('dropship')->getWarehouseTitle($item);
        }    
		return Mage::helper('core')->jsonEncode($itemWarehouses);
	}
	
    public function getBundleWarehouseText() {
        $items = $this->getItemsCollection();
        $itemWarehouses = array();
        foreach ($items as $item) {
            if (!$item->getParentItem() || $item->getParentItem()->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                continue;   
            } 
            // child of bundle
            $itemWarehouses[] = Mage::helper('dropship')->getWarehouseTitle($item);
        }    
        return Mage::helper('core')->jsonEncode($itemWarehouses);
    }
  
 
}
