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
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webshopapps_Dropship_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{	

    private static $_debug;
    
    
    
    function _construct() {
      self::$_debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Dropship');
    	
      parent::_construct();
       
   }
	
    private function getDropshipShippingRates()
    {
    	if (!Mage::getStoreConfig('carriers/dropship/active')) {
    		return parent::getShippingRates();
    	}
    	
   	 	if (empty($this->_rates)) {

   	 		$this->getAddress()->collectShippingRates()->save();

            $groups = $this->getAddress()->getGroupedAllShippingRates();
            
            return $this->_rates = $groups;	 		
    	}

	    return $this->_rates;
    }
    

    public function getWarehouseRates() {
    	
        if (self::$_debug) {
            Mage::helper('wsacommon/log')->postNotice('dropship','Geting Multi Warehouse Rates','');
        }

    	$shippingRateGroups = $this->getDropshipShippingRates(); 
     	if (count($shippingRateGroups)==0) {
    		return;
    	}

    	$warehouseRates=array();

    	$address = $this->getAddress();
    	
    	$warehouseItems = Mage::helper('dropship/shipcalculate')->getWarehouseDetails(
    		$address->getCountryId(),
    		$address->getRegionCode(),
    		$address->getPostcode(),
    		$address->getAllItems());
    		
    	if (count($warehouseItems)<2) {
    	   if (self::$_debug) {
              Mage::helper('wsalogger/log')->postDebug('dropship','Multi Warehouse','Found less than 2 warehouses.Shouldnt be in here');
           }
    	    Mage::log('Shouldnt be in here');
    		// shouldnt hit here
    		return;
    	}

    	foreach ($shippingRateGroups as $code=>$rates) {
    		$tempWareRates=array();
    		foreach ($rates as $rate) {
	    		$warehouse=$rate->getWarehouse();
	    		if (array_key_exists($warehouse,$tempWareRates)) {
	    			$tempWareRates[$warehouse][]=$rate;
	    		} else {
	    			$tempWareRates[$warehouse]= array($rate);
	    		}
    		}
    		foreach ($tempWareRates as $warehouse=>$rates) {
    			if (array_key_exists($warehouse,$warehouseRates)) {
	    			$warehouseRates[$warehouse]['shipping'][$code] = $rates;
	    		} else {
	    			$warehouseRates[$warehouse] = array (
	    				'item_list'	=> '',
	    				'shipping'	=> array(
	    					$code => $rates
	    					)
	    			);
	    		}
    		}
    	}
    	
    	foreach ($warehouseRates as $warehouse=>$value) {
    		if (array_key_exists($warehouse,$warehouseItems)) {
    			$warehouseRates[$warehouse]['item_list']= $this->getFormattedItemList(
    				$warehouseItems[$warehouse]);
    		} else {
    			return;
    		}
    	}

    	return $warehouseRates;
    }
    
    /*
     * Retrieves the items details ready for printing to checkout when < 2 warehouses are in checkout
     */
    public function getSingleWhItemDetails(){
    	
    	$address = $this->getAddress();
        $formattedWarehouseItems = array();
    	
        $warehouseItems = Mage::helper('dropship/shipcalculate')->getWarehouseDetails(
            $address->getCountryId(),
            $address->getRegionCode(),
            $address->getPostcode(),
            $address->getAllItems());
            
    	if (count($warehouseItems)>1) {
    	   if (self::$_debug) {
              Mage::helper('wsalogger/log')->postDebug('dropship','Single Warehouse','Found more than 1 warehouse.Shouldnt be in here');
           }
            Mage::log('Found more than 1 warehouse.Shouldnt be in here'); // log this so can see even if loggin switched off
            // shouldnt hit here
            return array();
        }
        
        // now format these
        foreach ($warehouseItems as $warehouse=>$items) {
        	$formattedWarehouseItems[] = array (
        	   'warehouse' => $warehouse,
        	   'item_list'  => $this->getFormattedItemList($items));
        }
        
        if (self::$_debug) {
           Mage::helper('wsalogger/log')->postDebug('dropship','Formatted Single Warehouse Item Details',$formattedWarehouseItems);
        }
        
        return $formattedWarehouseItems;
        
    }
    

    protected function getFormattedItemList($items) {
    	$useParent = Mage::getStoreConfigFlag('carriers/dropship/use_parent');
        $showAllItems = Mage::getStoreConfigFlag('carriers/dropship/show_all_items');
		$formattedItemList=array();
        $weightUnit = Mage::getStoreConfig('carriers/dropship/weight_unit_to_display');
		
		foreach ($items as $item) {
			if ($item->getParentItem() && ( ($item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $useParent && !$showAllItems)
				|| $item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE  )) {
				continue;  
			}
			
			if (!$useParent && $item->getHasChildren() && $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
				continue; // TODO: Correct? Think it should still display bundle parent maybe?
			}
			
			if ($item->getHasChildren() && $item->isShipSeparately() && !$useParent) {
				foreach ($item->getChildren() as $child) {
					$formattedItemList[]= self::getStyledHtmlItem(
					   'bundle_child',$child->getQty(),$child->getProduct()->getName(),$child->getWeight(),$weightUnit);					
				}
			} else {
                $formattedItemList[] = self::getFormattedItem($item ,$weightUnit);
			}
		}
		
		
		//TODO Weight not being returned on parent when useParent set to Yes.
		
        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postDebug('dropship','Formatted Item List',$formattedItemList);
        }
		
		return $formattedItemList;
	}
	

    protected static function getFormattedItem($item, $weightUnit) {
    	    	    	
        $class = 'non_bundle';
        
    	if ($item->getHasChildren()) {
            $class = 'bundle_parent';
        } elseif ($item->getParentItemId()) {
            $class = 'bundle_child';
        }
        
        $styleHtmlItem = self::getStyledHtmlItem(
                    $class,$item->getQty(),$item->getName(),$item->getWeight(), $weightUnit);
        
        return $styleHtmlItem;
    }
    
    protected static function getStyledHtmlItem($itemType, $qty, $name,$weight,$weightUnit) {

    	$weightHtmlDesc = $weightUnit == 'none' ? '' : '
           <span class="'.$itemType.'_weight">'.round($weight).$weightUnit.'</span>';
    	
    	$qtyHtmlDesc =  '<span class="'.$itemType.'_qty">'.$qty.' x '.'</span>';
    	
    	$nameHtmlDesc =  '<span class="'.$itemType.'_name">'.$name.'</span>';
    	
    	return $qtyHtmlDesc.$nameHtmlDesc.$weightHtmlDesc;
    	
    }
  
 	/**
     * Returns warehouse description
     * @param $warehouse
     * @return unknown_type
     */
 	public function getDescriptionText($warehouseId) {
    	return Mage::helper('dropship')->getDescription($warehouseId);
    }
    
 	public function getExplanationText() {
    	if ($explanationText = Mage::getStoreConfig('carriers/dropship/help_text')) {
    		if ($explanationUrl = Mage::getStoreConfig('carriers/dropship/help_url')) {
    			return '<a href="'.$this->getUrl($explanationUrl).'" target="_blank">'.$explanationText.'</a>';
    		} else {
    		 	return $explanationText;
    		}
    	}
    }

    
 	public function getWarehouseAddressShippingMethod($warehouse)
    {
    	    	
    	$shippingDetails = $this->getQuote()->getShippingAddress()->getWarehouseShippingDetails();
    	if (empty($shippingDetails) || $shippingDetails=='') {
    		return;
    	}
    	$_whss = Mage::helper('dropship')->decodeShippingDetails($shippingDetails);
    	foreach ($_whss as $_whs) {
    		if ($_whs['warehouse']==$warehouse) {
    			return $_whs['code'];
    		}
    	}
    	
    }
    
    
    public function showItemDescription() {
    	if (Mage::getStoreConfig('carriers/dropship/verbose_split') && 
    		!Mage::getStoreConfig('carriers/dropship/merged_checkout') && Mage::helper('dropship')->isActive()) {
    			return true;
    		}
    	return false;   	
    }
        	
// copied from ProductMatrix w/ Tooltips version
public function showToolTips($desc)
    { 
    	if($desc=='' or $desc=='*') {
    		return false;
    	}  	
    	// is tooltip present and not *
    	$options = explode(',',Mage::getStoreConfig("carriers/productmatrix/ship_options"));
    	if (in_array('show_tooltips',$options)) {
    		return true;
    	}
    	
        return false;
    }
    
	
	public function toolTipHtmlStart() {
		
		$html = $this->getLayout()
		->createBlock('core/text', 'example-block')
		->setText('<b class="helpcursor" title="')
		->_toHtml();
		return $html;
		$html;
	
	}
	
	public function toolTipHtmlImg() {
	
		$html = $this->getLayout()
		->createBlock('core/text', 'example-block')
		->setText('"><img src="')
		->_toHtml();
		return $html;
		$html;
	
	}
	
	public function toolTipHtmlEnd() {
	
		$html = $this->getLayout()
		->createBlock('core/text', 'example-block')
		->setText('"></b>')					
		->_toHtml();
		return $html;
	
	}
 
}