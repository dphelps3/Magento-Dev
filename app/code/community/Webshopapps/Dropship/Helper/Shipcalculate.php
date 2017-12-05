<?php

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Helper_Shipcalculate extends Mage_Core_Model_Abstract
{
    static $_useParent;

    public function _construct() {
        self::$_useParent = Mage::getStoreConfig('carriers/dropship/use_parent');
        parent::_construct();
    }

    public function getWarehouse($item) {
    	if ($item->getParentItem()!=null &&
			self::$_useParent ) {
    			$product = $item->getParentItem()->getProduct();
    	}  else {
    		$product = Mage::getModel('catalog/product')->load($item->getProductId());
    	}
    	if(is_object($product)){
    		return $product->getData('warehouse');
    	}
    }


 	public function getShippingMethods($items) {
    	$carriersArr=array();
    	if ($items==null) {
    		$items=$this->getAllVisibleItems();
    	}
   		if (empty($this->_warehouses) && !empty($items)) {
    		foreach($items as $item) {
   				$wareAttr=$this->getWarehouse($item);
    			$warehouses=explode(',',$wareAttr);
   			    foreach ($warehouses as $warehouse) {
	   			    $carriersAllowed = Mage::getModel('dropship/dropship')->load($warehouse);
	   			    foreach ($carriersAllowed->getShippingMethods() as $shipmethod) {
	   			    	$carriersArr[]=$shipmethod;
	   			    }
   			    }
    		}
   		}
    	return $carriersArr;
    }

    /**
     * Gets the list of dropship warehouses for the items in the cart
     *
     * @param $country
     * @param $region
     * @param $postcode
     * @param null $items
     * @return array
     */
    public function getWarehouseDetails($country,$region,$postcode,$items=null)
    {
    	$warehouseArr=array();
    	$debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Dropship');
        $wareArr = $this->warehouseItemRowTotals($items);

    	foreach($items as $item) {

    		//TODO Work out why this isnt in isMultipleWarehouse logic
    		if (($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
    				$item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) && !self::$_useParent ) {continue;}

    		if ($item->getProduct()->isVirtual()) {
    		    continue;
    		}
            $warehouseChanged = false;
    		$warehouse = $this->determineWhichWarehouse($item,$country,$region,$postcode,$warehouseChanged,$wareArr);

    		if (!array_key_exists($warehouse,$warehouseArr)) {
    			$warehouseArr[$warehouse]=array($item);
	    		if ($debug) {
	                Mage::helper('wsalogger/log')->postDebug('dropship','Warehouse Details, Warehouse being added:',$warehouse);
	            }
    		} else {
    			$warehouseArr[$warehouse][]=$item;
    		}

    	}

		return $warehouseArr;
    }


    public function isMultipleWarehouses($country,$region,$postcode,$items=null)
    {
    	$oldWarehouse=-1;
        $wareArr = $this->warehouseItemRowTotals($items);

    	if (!empty($items)) {
    		foreach($items as $item) {
    		    if ($item->getProduct()->isVirtual()) {
    		        continue;
    		    }
    			if ($item->getHasChildren() && ($item->isShipSeparately()|| !self::$_useParent)) {
    				foreach ($item->getChildren() as $child) {
                        $warehouseChanged = false;
    					$warehouse = $this->determineWhichWarehouse($child,$country,$region,$postcode,$warehouseChanged,$wareArr);
    					if ($oldWarehouse==-1) {
    						$oldWarehouse=$warehouse;
    					} else if ($warehouse!=$oldWarehouse) {
    						return true;
    					}
    				}
    			} else {
                    $warehouseChanged = false;
    				$warehouse = $this->determineWhichWarehouse($item,$country,$region,$postcode,$warehouseChanged,$wareArr);
	    			if ($oldWarehouse==-1) {
	    				$oldWarehouse=$warehouse;
	    			} else if ($warehouse!=$oldWarehouse) {
	    				return true;
	    			}
		    	}
    		}
    	}
		return false;
    }


    public function determineWhichWarehouse($item,$country,$region,$postcode,&$warehouseChanged=false,$wareArr=null)
    {
        $wareAttr=$this->getWarehouse($item);

    	if (!empty($wareAttr)) {
    	    $warehouses=explode(',',$wareAttr);
    	} else {
    	    $warehouses=array();
    	}

    	$defaultWarehouse = Mage::getStoreConfig('carriers/dropship/default_warehouse');

		if (Mage::getStoreConfigFlag('carriers/dropship/instock_default') &&
           $item->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_NO && $defaultWarehouse) {
		    // out of stock, use default
		    $warehouses = array();
        } elseif (Mage::getStoreConfigFlag('carriers/dropship/price_limit') && $defaultWarehouse) {
            if ($wareArr[$wareAttr] > Mage::getStoreConfig('carriers/dropship/price_limit')) {
                // less than price limit, use default
                $warehouses = array();
            }
        }

    	switch (count($warehouses)) {
    	    case 0:
        		$warehouse = $defaultWarehouse;
        		$warehouseChanged = true;
        		break;
    	    case 1:
        		$warehouse=$warehouses[0];
        		break;
    	    default:
        		$warehouse = Mage::helper('dropship')->getNearestWarehouse($country,$region,$postcode,$warehouses);
        		$warehouseChanged = true;
        		break;
    	}

    	return $warehouse;
    }

    /**
     * Sets up an array with total price for items in the cart by warehouse.
     *
     * @param $items
     * @return array
     */
    public function warehouseItemRowTotals($items)
    {
        $wareArr = array();
        if (!Mage::getStoreConfigFlag('carriers/dropship/price_limit')) {
            return $wareArr;
        }
        foreach($items as $item) {
            if (!array_key_exists($this->getWarehouse($item), $wareArr)) {
                $wareArr[$this->getWarehouse($item)]=$item->getRowTotal();
            } else {
                $wareArr[$this->getWarehouse($item)]+=$item->getRowTotal();
            }
        }
        return $wareArr;
    }
}