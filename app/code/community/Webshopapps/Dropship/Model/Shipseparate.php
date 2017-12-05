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
 * @package    Mage_Shipping
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * WebShopApps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial
 * that is bundled with this package in the file LICENSE.txt.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Webshopapps
 * @package    Webshopapps_Shipseparate
 * @copyright  Copyright (c) 2009 Raw Components Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt
 */

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Shipseparate  extends Mage_Core_Model_Abstract {

	private $_maxWeight;
	private $_splitRequests;
	private $_useParent;


    public function getSeparateRequests($items) {
    	$this->_splitRequests=array();
    	$pkgQty=0;
    	$pkgWeight=0;
    	$baseSubtotal=0;
    	$allSeparate = Mage::getStoreConfig('carriers/dropship/all_separate');
		$this->_maxWeight=1000;
		$splitStandard=false;
		$this->_useParent = Mage::getStoreConfig('carriers/dropship/use_parent');;
		
    	foreach ($items as $item) {
			unset($splitRequest);

			$weight=0;
			$qty=0;
			$price=0;
			$itemGroup=array();

			if (!$this->getItemTotals($item, $weight,$qty,$price,$itemGroup)) {
				continue;
			}

			$price = $price/$qty;
			$weight = $weight/$qty;

			if ($item->getParentItem()!=null &&
				$this->_useParent ) {
	        	$productColl = Mage::getModel('catalog/product')->getResourceCollection()
	            ->addAttributeToSelect('separate_shipping')
	            ->addAttributeToSelect('separate_qty')
	            ->addAttributeToSelect('oversized_shipping')
	            ->addAttributeToFilter('entity_id',$item->getParentItem()->getProductId());
			} else if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && !$this->_useParent ) {
				if ($item->getHasChildren()) {
                	foreach ($item->getChildren() as $child) {
                		$productColl = Mage::getModel('catalog/product')->getResourceCollection()
						  	->addAttributeToSelect('separate_shipping')
	            			->addAttributeToSelect('separate_qty')
	            			->addAttributeToSelect('oversized_shipping')
						    ->addAttributeToFilter('entity_id',$child->getProductId());
						    break;
                	}
				}
			} else {
					$productColl = Mage::getModel('catalog/product')->getResourceCollection()
					    ->addAttributeToSelect('separate_shipping')
	            		->addAttributeToSelect('separate_qty')
	            		->addAttributeToSelect('oversized_shipping')
					    ->addAttributeToFilter('entity_id',$item->getProductId());
			}

       		foreach($productColl as $product) {
       			continue;
       		}
       		       		
			if ($allSeparate  || $product->getData('separate_shipping') || $product->getData('oversized_shipping') ) {
				$splitQty=$product->getData('separate_qty');
				if (empty($splitQty) || !is_numeric($splitQty) || $splitQty<1) {
					$splitQty=1;
				}
				for($remainingQty=$qty;$remainingQty>0;) {
					if ($remainingQty>=$splitQty) {
						$qtyToAdd=$splitQty;
						$remainingQty-=$splitQty;
					} else {
						$qtyToAdd=$remainingQty;
						$remainingQty=0;
					}
					$weightToAdd=($item->getWeight())*$qtyToAdd;
					if (!empty($this->_maxWeight) || is_numeric($this->_maxWeight) && $splitQty == 1 && $item->getWeight()>$this->_maxWeight) {
						$this->createSplitWeight($weightToAdd,$price*$qtyToAdd,$qtyToAdd,array($item),$product->getData('oversized_shipping'));

					} else {
						$splitRequest = array (
								'price' 	=> $price*$qtyToAdd,
								'weight'	=> $weightToAdd,
								'qty'		=> $qtyToAdd,
								'items'		=> array($item),
								'oversized'	=> false,
						);


		        		if ($product->getData('oversized_shipping')) {
                			$splitRequest['oversized']=true;
		        		} // else use default

		        		$this->_splitRequests[]=$splitRequest;
					}

				}
			} else {
					$items[]=$item;
	       			$pkgQty +=$qty;
	       			$pkgWeight += $item->getWeight()*$qty;
            		$baseSubtotal+= $price*$qty;
			}

        }

    	if ($splitStandard && $pkgWeight>$this->_maxWeight) {
    		$this->createSplitWeight($pkgWeight,$baseSubtotal,$pkgQty,$items);
			$pkgQty=0;
	    	$pkgWeight=0;
	    	$baseSubtotal=0;
		}

        if (count($this->_splitRequests)>0  && $pkgWeight>0) {
        	$this->_splitRequests[] = array (
								'price' 	=> $baseSubtotal,
								'weight'	=> $pkgWeight,
								'qty'		=> $pkgQty,
								'items'		=> $items,
								'oversized'	=> false,
						);
        }


		usort($this->_splitRequests, array(&$this, 'cmp'));

        return $this->_splitRequests;
    }


 	private function getItemTotals($item, &$weight, &$qty, &$price,&$itemGroup) {

			$addressWeight=0;
			$addressQty=0;
			$freeMethodWeight=0;

			$itemGroup[]=$item;

			/**
             * Skip if this item is virtual
             */

            if ($item->getProduct()->isVirtual()) {
                return false;
            }

            /**
             * Children weight we calculate for parent
             */
            if ($item->getParentItem() && ( ($item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $this->_useParent)
            	|| $item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE  )) {
            		return false;
            }

            if (!$this->_useParent && $item->getHasChildren() && $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
            	return false;
            }

			if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
					$itemGroup[]=$item;

                    if ($child->getProduct()->isVirtual()) {
                        continue;
                    }
                    $addressQty += $item->getQty()*$child->getQty();

                    if (!$item->getProduct()->getWeightType()) {
                        $itemWeight = $child->getWeight();
                        $itemQty    = $item->getQty()*$child->getQty();
                        $rowWeight  = $itemWeight*$itemQty;
                        if ($this->_freeShipping || $child->getFreeShipping()===true) {
                            $rowWeight = 0;
                        } elseif (is_numeric($child->getFreeShipping())) {
                            $freeQty = $child->getFreeShipping();
                            if ($itemQty>$freeQty) {
                                $rowWeight = $itemWeight*($itemQty-$freeQty);
                            }
                            else {
                                $rowWeight = 0;
                            }
                        }
                        $freeMethodWeight += $rowWeight;
                    }
                }
                if ($item->getProduct()->getWeightType()) {
                    $itemWeight = $item->getWeight();
                    $rowWeight  = $itemWeight*$item->getQty();
                    $addressWeight+= $rowWeight;
                    if ($this->_freeShipping || $item->getFreeShipping()===true) {
                        $rowWeight = 0;
                    } elseif (is_numeric($item->getFreeShipping())) {
                        $freeQty = $item->getFreeShipping();
                        if ($item->getQty()>$freeQty) {
                            $rowWeight = $itemWeight*($item->getQty()-$freeQty);
                        }
                        else {
                            $rowWeight = 0;
                        }
                    }
                    $freeMethodWeight+= $rowWeight;
                }
            }
            else {
                if (!$item->getProduct()->isVirtual()) {
                    $addressQty += $item->getQty();
                }
                $itemWeight = $item->getWeight();
                $rowWeight  = $itemWeight*$item->getQty();
                $addressWeight+= $rowWeight;
                if ($this->_freeShipping || $item->getFreeShipping()===true) {
                    $rowWeight = 0;
                } elseif (is_numeric($item->getFreeShipping())) {
                    $freeQty = $item->getFreeShipping();
                    if ($item->getQty()>$freeQty) {
                        $rowWeight = $itemWeight*($item->getQty()-$freeQty);
                    }
                    else {
                        $rowWeight = 0;
                    }
                }
                $freeMethodWeight+= $rowWeight;
            }

		if (!$this->_useParent && $item->getParentItem() && $item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
        	$weight=$addressWeight*$item->getParentItem()->getQty();
			$qty=$addressQty*$item->getParentItem()->getQty();
        }   else {
	        $weight=$addressWeight;
			$qty=$addressQty;
        }
		$price=$item->getRowTotal();

		return true;
	}





    private function cmp($a, $b)
	{
	    if ($a['weight'] == $b['weight']) {
	        return 0;
	    }
	    return ($a['weight'] > $b['weight']) ? -1 : 1;
	}



    private function createSplitWeight($totalWeight, $price, $qty, $items, $oversized=false) {
    	for($remainingWeight=$totalWeight;$remainingWeight>0;) {
			if ($remainingWeight>=$this->_maxWeight) {
				$remainingWeight-=$this->_maxWeight;
				$weightToAdd=$this->_maxWeight;
			} else {
				$weightToAdd=$remainingWeight;
				$remainingWeight=0;
			}
			$this->_splitRequests[] = array (
				'price' 	=> $price,
				'weight'	=> $weightToAdd,
				'qty'		=> $qty,
				'items'		=> $items,
				'oversized'	=> $oversized,
				);

		}
    }
}