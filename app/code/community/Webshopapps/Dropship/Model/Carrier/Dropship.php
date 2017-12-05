<?php


/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
/**
 * Webshopapps Shipping Module
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
 * Conditional Free Shipping Module - where if attribute exclude_free_shipping is set
 * will result in free shipping being disabled for checkout
 *
 * @category   Webshopapps
 * @package    Webshopapps_Dropship
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
 * @version    1.5
*/
class Webshopapps_Dropship_Model_Carrier_Dropship
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'dropship';
    private static $_debug;
    
    private $_shippingResults;
    private $_shippingWarehouse;
    private $_request;
    private $_checkedFreightItems;
    private $_handlingCount;
    private $_repeatRequest;
    private $_origUSPSOversized;
    private $_sepItemsResults;
    private $_handlingType;
    private $_handlingAction;
    private $_customError = "";

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::helper('dropship')->calculateDropshipRates()) {
            return false;
        }
        $this->_shippingResults=array();
        $this->_shippingWarehouse=array();
        $this->_request = $request;
        $this->_origUSPSOversized = $request->getUspsSize();

        $cartPackageValue =  $request->getPackageValue();
        $cartPackageValueWithDiscount =	$request->getPackageValueWithDiscount();
        $cartPackagePhysicalValue = $request->getPackagePhysicalValue();
        $cartFreeShipping = $request->getFreeShipping();

        // get all items
    	$items = $this->_request->getAllItems();
    	$useCartPrice = Mage::getStoreConfig('carriers/dropship/use_cart_price');
    	$this->_repeatRequest = Mage::getStoreConfig('carriers/dropship/repeat_req');
    	self::$_debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Dropship');
    	$this->_handlingType = Mage::getStoreConfig('carriers/dropship/handling_type');
    	$this->_handlingAction = Mage::getStoreConfig('carriers/dropship/handling_action');

    	// get warehouses
    	$warehouseDetails = Mage::helper('dropship/shipcalculate')->getWarehouseDetails(
    		$this->_request->getDestCountryId(),
    		$this->_request->getDestRegionCode(),
    		$this->_request->getDestPostcode(),
    		$items
    	);

    	if ($this->getConfigFlag('handling_action')=='W') {
    		$this->_handlingCount = count($warehouseDetails);
    	} else {
    		$this->_handlingCount = 1;
    	}

    	if (self::$_debug) {
    		Mage::helper('wsalogger/log')->postDebug('dropship','Entered Dropship Carrier Logic','');
    	}
 	    if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvZHJvcHNoaXAvc2hpcF9vbmNl',
        	'aG9sZGluZ3Vw','Y2FycmllcnMvZHJvcHNoaXAvc2VyaWFs')) { return false;}

    	if (count($warehouseDetails)<2) {
    		reset($warehouseDetails);
    		$warehouse = key($warehouseDetails);
            $results = $this->processSingleWarehouse($warehouse);

            $result = $this->applyFreeText($results);

    		return $result;
    	}
    	$sepModel = Mage::getModel('dropship/shipseparate');
        $useParent=Mage::getStoreConfigFlag('carriers/dropship/use_parent');


    	foreach ($warehouseDetails as $warehouse=>$warehouseItems) {

     		$splitRequests = $sepModel->getSeparateRequests($warehouseItems);
     		if (count($splitRequests)>1) {
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					Mage::log('count($splitRequests)>1 == true', null, 'aaron_dropship_log.log');
					Mage::log('Run collectSeparateSplitRates', null, 'aaron_dropship_log.log');
				}
     			$this->collectSeparateSplitRates($splitRequests,$warehouse);
     		} else {
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					Mage::log('count($splitRequests)>1 != true', null, 'aaron_dropship_log.log');
					Mage::log('multiple warehouses - split out items', null, 'aaron_dropship_log.log');
				}
	    		// multiple warehouses - split out items
	    		$subTotal=0;
	    		$qtyTotal=0;
	    		$freeTotalWeight=0;
	    		$weightTotal=0;
                $subTotalInclTax=0;
	    		foreach ($warehouseItems as $item) {
		    		if ($item->getProduct()->isVirtual()) {
		                continue;
		            }
		            $weight=0;
		    		$qty=0;
		    		$price=0;
                    $priceInclTax=0;
		    		$freeMethodWeight=0;
                    $temp = array();
                    if (!Mage::helper('wsacommon/shipping')->getItemInclFreeTotals($item, $weight,$qty,$price,$freeMethodWeight,
                        $useParent,true,$temp,false, false, false, false,$priceInclTax)) {
                        continue;
                    }
	    			$subTotal+=$price;
	    			$qtyTotal+=$qty;
                    $subTotalInclTax+=$priceInclTax;
	    			$freeTotalWeight+=$freeMethodWeight;
	    			$weightTotal+=$weight;
	    		}
                $request->setFreeShipping($cartFreeShipping); // always reset as Free Shipping Carrier doesnt!
	    		if ($useCartPrice) {
	    			$this->_request->setPackagePhysicalValue($cartPackagePhysicalValue);
	    			$this->_request->setPackageValue($cartPackageValue);
	        		$this->_request->setPackageValueWithDiscount($cartPackageValueWithDiscount);
	    		} else {
		    		$this->_request->setPackagePhysicalValue($subTotal);
		    		$this->_request->setPackageValue($subTotal);
		        	$this->_request->setPackageValueWithDiscount($subTotal);
                    $this->_request->setBaseSubtotalInclTax($subTotalInclTax);
                }
	        	$this->_request->setAllItems($warehouseItems);
                $this->_request->setPackageWeight($this->getCalculatedWeight($weightTotal));
	        	$this->_request->setPackageQty($qtyTotal);
	        	$this->_request->setFreeMethodWeight($freeTotalWeight);
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					//Mage::log('Weight Total: '.$weightTotal, null, 'aaron_dropship_log.log');
					//Mage::log('Price Total: '.$subTotal, null, 'aaron_dropship_log.log');
					//Mage::log('Qty Total: '.$qtyTotal, null, 'aaron_dropship_log.log');
					//Mage::log('SubtotalIncl Total: '.$subTotalInclTax, null, 'aaron_dropship_log.log');
				}
                 if (self::$_debug) {
                     Mage::helper('wsalogger/log')->postDebug('dropship','Weight Total',$weightTotal);
                     Mage::helper('wsalogger/log')->postDebug('dropship','Price Total',$subTotal);
                     Mage::helper('wsalogger/log')->postDebug('dropship','Qty Total',$qtyTotal);
                     Mage::helper('wsalogger/log')->postDebug('dropship','SubtotalIncl Total',$subTotalInclTax);
                 }

	        	if (!$this->setReqWarehouseDetails($warehouse)) {
    				if (self::$_debug) {
	        			Mage::helper('wsalogger/log')->postDebug('dropship','Cant get any carriers to call','');
	        		}
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
						//Mage::log('Cant get any carriers to call', null, 'aaron_dropship_log.log');
					}
	        		return $this->getErrorResult();
	        	}

		        // get rates for warehouse
		        $this->collectWarehouseRates($warehouse);
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					Mage::log('Running collectWarehouseRates', null, 'aaron_dropship_log.log');
				}
     		}
    	}
        $this->_request->setDropshipCollecting(false);
    	if ($request->getDropshipSplitRates()) {
    		$rates=$this->getSplitRates();
    		// doesnt currently return insurance. Not raised by customers so assumed they are using merged rates.
    		return $this->collectStdResult($rates);
    	}

        if (empty($this->_shippingResults)) {
            $result=$this->getErrorResult();
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				Mage::log('empty($this->_shippingResults) == true', null, 'aaron_dropship_log.log');
				Mage::log('Returning error', null, 'aaron_dropship_log.log');
			}
        }  else {
        	// multiple rates returned
        	// lets merge
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				Mage::log('Shipping Results', null, 'aaron_dropship_log.log');
				Mage::log($this->_shippingResults, null, 'aaron_dropship_log.log');
				Mage::log('empty($this->_shippingResults) != true, merging rates', null, 'aaron_dropship_log.log');
				Mage::log('Running collectMergedRates', null, 'aaron_dropship_log.log');
			}
        	$result=$this->collectMergedRates();
        }
        $result = $this->applyFreeText($result);
		
        return $result;
    }

    private function applyFreeText($result)
    {
        $rates = $result->getAllRates();

        if (empty($rates)) {
            return $result;
        }

        $newResult = Mage::getModel('shipping/rate_result');

        foreach ($rates as $rate) {
            if ($rate->getPrice() == 0 && $this->getConfigData('free_shipping_text') != '') {
                $rate->setMethodTitle($this->getConfigData('free_shipping_text'));
                $newResult->append($rate);
            } else {
                $newResult->append($rate);
            }
        }

        return $newResult;
    }

    /*
     * Ship Separately called - deprecated
     */
    private function collectSeparateSplitRates($splitRequests,$warehouse) {
        $oldWeight=0;
        $saveRates=array();
        foreach ($splitRequests as $splitRequest) {
        	if (!$this->_repeatRequest && $oldWeight==$splitRequest['weight'] && !empty($saveRates)) {
        		// same weight
        		$this->_shippingResults[]=$saveRates;
        	} else {
        		$oldWeight=$splitRequest['weight'];
	        	$this->_request->setPackageValue($splitRequest['price']);
	        	$this->_request->setAllItems($splitRequest['items']);
	      		$this->_request->setPackageValueWithDiscount($splitRequest['price']);
	        	$this->_request->setPackageWeight($this->getCalculatedWeight($splitRequest['weight']));
	        	$this->_request->setFreeMethodWeight($this->getCalculatedWeight($splitRequest['weight']));
	        	$this->_request->setPackageQty($splitRequest['qty']);
        		if (!$this->setReqWarehouseDetails($warehouse)) {
        			if (self::$_debug) {
	        			Mage::helper('wsalogger/log')->postDebug('dropship','Cant get any carriers to call','');
	        		}
        			return $this->getErrorResult();
        		}
	        	if ($splitRequest['oversized']) {
	        		$this->_request->setUspsSize('OVERSIZE');
	        	} else {
	        		$this->_request->setUspsSize($this->_origUSPSOversized);
	        	}
	        	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shippingoverride2','shipping/shippingoverride2/active')) {
	        		$saveRates = $this->calloutCollectRates($this->_request);
	        	} else {
		        	$saveRates = Mage::getModel('dropship/shipping_shipping')
	            		->collectRates($this->_request)
	                	->getResult();
	        	}

        	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingmatrix','shipping/handlingmatrix/active')) {
    			$handlingMatrixModel = Mage::getModel('handlingmatrix/handlingmatrix');
    			$handlingMatrixModel->addHandlingCosts($this->_request,$saveRates);
    		}
    		if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingproduct','shipping/handlingproduct/active') &&
    		        !Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa','shipping/shipusa/active')) {
    		    $handlingProductModel = Mage::getModel('handlingproduct/handlingproduct');
    		    $handlingProductModel->addHandlingCosts($this->_request,$saveRates);
    		}
     		if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Insurance','shipping/insurance/active','shipping/insurance/active')) {
    			$result =  Mage::helper('insurance')->getInsuranceResults($this->_request,$saveRates);
    		}
        		$this->_shippingWarehouse[]=$warehouse;
				$this->_shippingResults[] = $saveRates;
        	}
         }

     	if (self::$_debug) {
         	Mage::helper('wsalogger/log')->postDebug('dropship','Seperate Item Results',$this->_sepItemsResults);
		}

      	$this->_shippingWarehouse[]=$warehouse;
    }

    /**
     * collect rates for each shipping carrier for particular warehouse
     * @param unknown_type $request
     * @param unknown_type $warehouse
     */
	private function collectWarehouseRates($warehouse) {
		if (self::$_debug) {
			Mage::helper('wsalogger/log')->postDebug('dropship','Collecting Rates for warehouse:',$warehouse);
		}
		if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shippingoverride2','shipping/shippingoverride2/active')) {
        	$result = $this->calloutCollectRates($this->_request);
        } else {
        	$result = Mage::getModel('dropship/shipping_shipping')
            	->collectRates($this->_request)
                ->getResult();
        }

        if ($result) {
	        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingmatrix','shipping/handlingmatrix/active')) {
	    		$handlingMatrixModel = Mage::getModel('handlingmatrix/handlingmatrix');
	    		$handlingMatrixModel->addHandlingCosts($this->_request,$result);
	    	}
            if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingproduct','shipping/handlingproduct/active') &&
                    !Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa','shipping/shipusa/active')) {
    		    $handlingProductModel = Mage::getModel('handlingproduct/handlingproduct');
    		    $handlingProductModel->addHandlingCosts($this->_request,$result);
    		}
	     	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Insurance','shipping/insurance/active')) {
	    		$result =  Mage::helper('insurance')->getInsuranceResults($this->_request,$result);
	    	}
         	if (self::$_debug) {
         		Mage::helper('wsalogger/log')->postDebug('dropship','Results:',$result);
         	}
            $this->_shippingResults[] = $result;
            $this->_shippingWarehouse[]=$warehouse;

        } else {
        	return false;
        }
        return true;

    }


    /**
     * Called when we want to get split rates out
     * @param unknown_type $request
     * @return string
     */
    private function getSplitRates() {

    	if (self::$_debug) {
    		Mage::helper('wsalogger/log')->postDebug('dropship','Getting Split Rates','');

    	}
    	$bigRates=array();

    	foreach ($this->_shippingResults as $key=>$result) {
    		$rates = $result->getAllRates();
    		$warehouse = $this->_shippingWarehouse[$key];

    		foreach ($rates as $rate) {
    			$rate->setWarehouse($warehouse);
    			$rate->setPrice($this->getHandlingFee($rate->getPrice()));
    			$bigRates[]=$rate;
    		}
    	}
    	return $bigRates;
    }

    private function getCalculatedWeight($weight) {
    	$weightAdjPercentage = $this->getConfig('weight_percentage');
        if (!empty($weightAdjPercentage)) {
        	$calculatedWeight = $weight*(1+($weightAdjPercentage/100));
        } else {
        	$calculatedWeight = $weight;
        }

        return $calculatedWeight;
    }


    private function processSingleWarehouse($warehouse) {

   		 if (self::$_debug) {
	    	Mage::helper('wsalogger/log')->postDebug('dropship','Processing single warehouse',$warehouse);
    	}
    	$splitRequests = Mage::getModel('dropship/shipseparate')->getSeparateRequests($this->_request->getAllItems());
     	if (count($splitRequests)>1) {
     		$this->collectSeparateSplitRates($splitRequests,$warehouse);

 			if (count($this->_shippingResults)<1 || count($this->_shippingResults[0]->getAllRates())<1) {
	            $result=$this->getErrorResult();
	        } else {
	        	$result=$this->collectMergedRates();
	        }
            $this->_request->setDropshipCollecting(false);
			// doesnt support insurance extn
            return $result;
     	}

    	$this->_request->setPackageWeight($this->getCalculatedWeight($this->_request->getPackageWeight()));

    	if ($warehouse=='') {
    		return $this->getErrorResult();
    	}
    	if (!$this->setReqWarehouseDetails($warehouse)) {
    		if (self::$_debug) {
        		Mage::helper('wsalogger/log')->postDebug('dropship','Cant get any carriers to call','');
        	}
    		return $this->getErrorResult();
    	}

    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shippingoverride2','shipping/shippingoverride2/active')) {
        	$result = $this->calloutCollectRates($this->_request);
        } else {
        	$result = Mage::getModel('dropship/shipping_shipping')
            	->collectRates($this->_request)
                ->getResult();
        }

        if (empty($result) || count($result->getAllRates())<1) {
            $result=$this->getErrorResult();
        } else {
        	$this->applyHandlingFees($result);
        }
        $this->_request->setDropshipCollecting(false);
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingmatrix','shipping/handlingmatrix/active')) {
    		$handlingMatrixModel = Mage::getModel('handlingmatrix/handlingmatrix');
    		$handlingMatrixModel->addHandlingCosts($this->_request,$result);
    	}
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingproduct','shipping/handlingproduct/active') &&
                !Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa','shipping/shipusa/active')) {
            $handlingProductModel = Mage::getModel('handlingproduct/handlingproduct');
    	    $handlingProductModel->addHandlingCosts($this->_request,$result);
    	}
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Insurance','shipping/insurance/active')) {
    		return Mage::helper('insurance')->getInsuranceResults($this->_request,$result);
    	}
        return $result;
    }

    private function applyHandlingFees(&$result) {

    	$rates=$result->getAllRates();
	    $result->reset();

    	foreach ($rates as $key=>$rate) {
     		$rate->setPrice($this->getFinalPriceWithHandlingFee($rate->getPrice()));
    		$result->append($rate);
    	}
    }

    /**
     * Doesnt support insurance
     * @param $request
     */
 	private function calloutCollectRates($request) {

    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shippingoverride2','shipping/shippingoverride2/active')) {
        	$override2Request = clone $request;

    	    // else want to restrict what's sent to the carriers
	     	$override2ResourceModel = Mage::getResourceModel('shippingoverride2/shippingoverride2');
	     	$exclusionList = array();
     		$totalShipPrice=0;

     		$override2Groups = $override2ResourceModel->getNewRate($request, $exclusionList,$totalShipPrice,$this->_customError);
	     	if (empty($override2Groups) && count($exclusionList)<1) {
	     		return Mage::getModel('dropship/shipping_shipping')
            	->collectRates($request)
                ->getResult();
	     	}


            // now do override logic
            return Mage::getModel('shippingoverride2/shipping_shipping')->
            	collectSpecialRates($override2Request,$request, $exclusionList,$override2Groups,$totalShipPrice,$this->_customError)
            	->getResult();

    	}

    }

    private function setReqWarehouseDetails($warehouse) {

    	$this->_checkedFreightItems=false;
    	// set origin
    	$warehouseDetails = Mage::getModel('dropship/dropship')->load($warehouse);
        if ($warehouse!='') {
        	$this->_request
                ->setOrigCountry($warehouseDetails->getCountry())
                ->setOrigRegionCode($warehouseDetails->getRegion())
                ->setOrigCity($warehouseDetails->getCity())
                ->setOrigPostcode($warehouseDetails->getZipcode());


            $this->_request->setOrig(true);
            if ($warehouseDetails->getFedexAccountId()!='') {
                $this->_request->setFedexAccount($warehouseDetails->getFedexAccountId());
        	}

            if ($warehouseDetails->getFedexsoapKey()!='') {
                $this->_request->setFedexSoapKey($warehouseDetails->getFedexsoapKey());
        	}
            if ($warehouseDetails->getFedexsoapPassword()!='') {
                $this->_request->setFedexPassword($warehouseDetails->getFedexsoapPassword());
        	}
            if ($warehouseDetails->getFedexsoapMeterNumber()!='') {
                $this->_request->setFedexMeterNumber($warehouseDetails->getFedexsoapMeterNumber());
        	}
					if ($warehouseDetails->getFedexsoapAllowedMethods()!='') {
                $this->_request->setFedexsoapAllowedMethods($warehouseDetails->getFedexsoapAllowedMethods());
        	}
					if ($warehouseDetails->getFedexsoapSpecifyAllowedMethods()!='') {
                $this->_request->setFedexsoapSpecifyAllowedMethods($warehouseDetails->getFedexsoapSpecifyAllowedMethods());
        	}
            if ($warehouseDetails->getUpsPassword()!='') {
                $this->_request->setUpsPassword($warehouseDetails->getUpsPassword());
        	}
            if ($warehouseDetails->getUpsAccessLicenseNumber()!='') {
                $this->_request->setUpsAccessLicenseNumber($warehouseDetails->getUpsAccessLicenseNumber());
        	}
            if ($warehouseDetails->getUpsUserId()!='') {
                $this->_request->setUpsUserId($warehouseDetails->getUpsUserId());
        	}
            if ($warehouseDetails->getUpsShipperNumber()!='') {
                $this->_request->setUpsShipperNumber($warehouseDetails->getUpsShipperNumber());
        	}

        	if($warehouseDetails->getMaxPackageWeight()!=''){
        		$this->_request->setMaxPackageWeight($warehouseDetails->getMaxPackageWeight());
        	}
            if ($warehouseDetails->getUspsUserId()!='') {
                $this->_request->setUspsUserid($warehouseDetails->getUspsUserId());
        	}
						if ($warehouseDetails->getUspsAllowedMethods()!='') {
                $this->_request->setUspsAllowedMethods($warehouseDetails->getUspsAllowedMethods());
        	}
					if ($warehouseDetails->getUspsSpecifyAllowedMethods()!='') {
                $this->_request->setUspsSpecifyAllowedMethods($warehouseDetails->getUspsSpecifyAllowedMethods());
        	}
         	if ($warehouseDetails->getUpsShippingOrigin()!='') {
                $this->_request->setUpsShippingOrigin($warehouseDetails->getUpsShippingOrigin());
                $this->_request->setUpsAllowedMethods($warehouseDetails->getUpsAllowedMethods());
                $this->_request->setUpsUnitOfMeasure($warehouseDetails->getUpsUnitOfMeasure());
         	} else {
                $this->_request->setUpsShippingOrigin(null);
                $this->_request->setUpsAllowedMethods(null);
                $this->_request->setUpsUnitOfMeasure(null);
         	}
        }

        $carriersAllowed=$warehouseDetails->getShippingMethods();

        if (self::$_debug) {
        	Mage::helper('wsalogger/log')->postDebug('dropship','Finding carriers for warehouse:'.$warehouse,$carriersAllowed);
        }
        //determine shipping carriers for each warehouse
        $this->_request->setLimitCarrier($carriersAllowed);
        $this->_request->setDropshipCollecting(true);

        // freight check
      	$limitCarriers=array();
      	$removeFreightCarrier=array();
      	$removeOtherCarriersFreight=false;

  	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsafreightcommon','shipping/wsafreightcommon/active') ) {

		$freightCarrierNameArr = Mage::helper('wsafreightcommon')->getAllFreightCarriers();

		foreach ($freightCarrierNameArr as $freightCarrierName) {
			if ( $this->limitFreight($freightCarrierName,$removeFreightCarrier,$removeOtherCarriersFreight)) {
					$limitCarriers[] = $freightCarrierName;
				}
		}
	}

		if (count($limitCarriers)>0 && $removeOtherCarriersFreight) {
            // check for all carriers allowed
            $this->_addAllCarriersAllowed($carriersAllowed,$limitCarriers);
			$this->_request->setLimitCarrier($limitCarriers);
			return true;
     	}
     	$newLimitCarriers=array();
     	foreach ($this->_request->getLimitCarrier() as $carrierCode) {
     		if (in_array($carrierCode,$carriersAllowed) &&
     				!in_array($carrierCode,$removeFreightCarrier)) {
     			$newLimitCarriers[]=$carrierCode;
     		}
     	}

     	$this->_request->setLimitCarrier($newLimitCarriers);
    	if (self::$_debug) {
        	Mage::helper('wsalogger/log')->postDebug('dropship','Limiting carriers for warehouse:'.$warehouse,$this->_request->getLimitCarrier());
     	}
     	if (count($this->_request->getLimitCarrier())==0) {
     		return false;
     	}

     	return true;
    }

    private function _addAllCarriersAllowed($carriersAllowed,&$limitCarriers) {
        $alwaysShowCarriersArr  = explode(',',Mage::getStoreConfig('shipping/wsafreightcommon/show_carriers'));
        foreach ($carriersAllowed as $carrierName) {
            if (in_array($carrierName,$alwaysShowCarriersArr)) {
                $limitCarriers[] = $carrierName;
            }
        }
    }


    private function limitFreight($freightname,&$removeFreightCarrier,&$removeOtherCarriers) {

        $this->_checkedFreightItems=false;

    	$carriers = $this->_request->getLimitCarrier();

    	$freightConfigPart = 'carriers/'.$freightname;
    	if (!Mage::getStoreConfig($freightConfigPart.'/active') || !in_array($freightname,$carriers)) {
    	    return false;
    	}

    	$weight = $this->_request->getPackageWeight();
    	$items = $this->_request->getAllItems();

        $forceFreight  = Mage::getStoreConfig('shipping/wsafreightcommon/force_freight');
		if (!$forceFreight) {$forceFreight = Mage::getStoreConfig('shipping/wsafreightcommon/force_freight');} // If not found, check in freightcommon
        $restrictRates = Mage::getStoreConfig('shipping/wsafreightcommon/restrict_rates');
		if (!$restrictRates) {$restrictRates = Mage::getStoreConfig('shipping/wsafreightcommon/restrict_rates');} // If not found, check in freightcommon
        $minWeight = Mage::getStoreConfig('shipping/wsafreightcommon/min_weight');
		if (!$minWeight) {$minWeight = Mage::getStoreConfig('shipping/wsafreightcommon/min_weight');} // If not found, check in freightcommon
        $weightApplyOrder = Mage::getStoreConfig($freightConfigPart.'/weight_apply') == "" ? true : Mage::getStoreConfig($freightConfigPart.'/weight_apply')=='Order';
        $hasFreightItems = $this->hasFreightItems($items);

        if (($restrictRates && $weightApplyOrder && $weight >= $minWeight)
        || ( $forceFreight && $hasFreightItems)) {
            $removeOtherCarriers=true;
	    	return true;
		} else if ( $weight < $minWeight && !$hasFreightItems) {
	    	$removeFreightCarrier[]=$freightname;
	    	return false;
		}
    	return false;
    }

    private function hasFreightItems($items) {
     	if (!$this->_checkedFreightItems) {
	    $this->_checkedFreightItems=true;
	    return Mage::helper('wsafreightcommon')->hasFreightItems($items);
     	}
    }


  	private function getErrorResult() {
        $this->_request->setDropshipCollecting(false);

  		if ($this->getConfigData('showmethod')) {
	    	$error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('dropship');
            $error->setCarrierTitle($this->getConfigData('title'));
            if(!empty($this->_customError)) {
            	$error->setErrorMessage($this->_customError);
            } else {
            	$error->setErrorMessage($this->getConfigData('specificerrmsg'));
            }
  		} else {
  			$error=Mage::getModel('shipping/rate_result');
  		}
      	return $error;
    }


    private function collectStdResult($ratesToAdd) {
    	$result = Mage::getModel('shipping/rate_result');
        foreach ($ratesToAdd as $rateToAdd) {
	    	$result->append($rateToAdd);
        }
    	return $result;
    }

    public function getAllowedMethods()
    {
        return array('dropship'=>$this->getConfigData('name'));
    }

 	public function createMergedRate($ratesToAdd) {


	        $result = Mage::getModel('shipping/rate_result');
	        foreach ($ratesToAdd as $rateToAdd) {
		    	$method = Mage::getModel('shipping/rate_result_method');
		        $method->setPrice((float)$rateToAdd['price']);
		        $method->setCost((float)$rateToAdd['price']);
		        $method->setCarrier('dropship');
		    	$method->setCarrierTitle($this->getConfigData('title'));
		    	$method->setMethod($rateToAdd['title']);
		    	$method->setMethodTitle($rateToAdd['title']);
		    	$method->setFreightQuoteId($rateToAdd['freight_quote_id']);
				$method->setExpectedDelivery($rateToAdd['expected_delivery']);
	            $method->setDispatchDate($rateToAdd['dispatch_date']);
		    	$result->append($method);
	        }
	    	return $result;
 	}


	/**
	 * Have received more than one set of rates - multiple warehouses
	 * Try to merge the rates together
	 */
	private function collectMergedRates() {
		if (self::$_debug) {
			Mage::helper('wsalogger/log')->postDebug('dropship','Merging Rates',$this->_shippingResults);
		}
		$ratesToAdd=array();
	    $rateAdded=false;
	    $freightErrorRate = -1;
        $innerRates = array();

    	foreach ($this->_shippingResults as $key=>$result) {

    		$rates=$result->getAllRates();
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				//Mage::log('All Rates', null, 'aaron_dropship_log.log');
				//Mage::log($rates, null, 'aaron_dropship_log.log');
				//Rates look healthy here
			}
    		if (empty($rates)) {
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					//Mage::log('empty($rates) == true', null, 'aaron_dropship_log.log');
				}
				return $this->getErrorResult();
    		}
    		if ($key==0) {

    			$currentCarrier=-1;
				foreach ($rates as $rate) {
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
						//Mage::log('Current Rate inside $key==0', null, 'aaron_dropship_log.log');
						//Mage::log($rate, null, 'aaron_dropship_log.log');
					}
					if ($rate->getCarrier()!=$currentCarrier) {
						if ($currentCarrier!=-1) {
							$ratesToAdd[$currentCarrier]=$innerRates;
							if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
								//Mage::log('$ratesToAdd[$currentCarrier]=$innerRates', null, 'aaron_dropship_log.log');
								//Mage::log($innerRates, null, 'aaron_dropship_log.log');
							}
						}
						$innerRates=array();
						$currentCarrier=$rate->getCarrier();
					}

	    			if ($rate->getErrorMessage()!="") {
						if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							Mage::log('($rate->getErrorMessage()!="") == true (rate found)', null, 'aaron_dropship_log.log');
						}
	    				if ($rate->getCarrier()=='freightrate') {
	    					$freightErrorRate=$this->getFreightErrorRate($rate);
	    					continue;
	    				} else {
	    					$innerRates[$currentCarrier]=array (
		    						'price'		=> $rate['price'],
		    						'found'		=> true,
	    						 	'rate'		=> $rate,
	    							'shipping_details' => array (array (
							        	'warehouse'		=> $this->_shippingWarehouse[$key],
							        	'code'			=> $rate->getMethod(),
							        	'price'			=> (float)$rate->getPrice(),
							        	'cost'			=> (float)$rate->getCost(),
							        	'carrierTitle'	=> $rate->getCarrierTitle(),
							        	'methodTitle'	=> $rate->getMethodTitle(),
	    							)),
		    					);
		    				break; // error found lets exit
	    				}
    				} else {
						if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							Mage::log('($rate->getErrorMessage()!="") != true (rate not found)', null, 'aaron_dropship_log.log');
							Mage::log($rate->getErrorMessage(), null, 'aaron_dropship_log.log');
							Mage::log('Adding $rate->getMethod()', null, 'aaron_dropship_log.log');
							Mage::log($rate->getMethod(), null, 'aaron_dropship_log.log');
						}
    					$innerRates[$rate->getMethod()]=array (
	    						'price'		=> $rate['price'],
								// Aaron Karol - Changed 'found' == true (default is false) to get all the shipping rates to show up
	    						'found'		=> true,
    						 	'rate'		=> $rate,
    							'shipping_details' => array (array (
							        	'warehouse'		=> $this->_shippingWarehouse[$key],
							        	'code'			=> $rate->getMethod(),
							        	'price'			=> (float)$rate->getPrice(),
							        	'cost'			=> (float)$rate->getCost(),
							        	'carrierTitle'	=> $rate->getCarrierTitle(),
							        	'methodTitle'	=> $rate->getMethodTitle(),
    									'freightQuoteId'=> $rate->getFreightQuoteId(),
    							)),
    					);
    				}
    			}
    			if(count($innerRates>0)) {
    				$ratesToAdd[$currentCarrier]=$innerRates;
    			}
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					//Mage::log('Innner Rates', null, 'aaron_dropship_log.log');
					//Mage::log($innerRates, null, 'aaron_dropship_log.log');
				}
    			continue;
       		}
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				//Mage::log('All Rates after $key == 0', null, 'aaron_dropship_log.log');
				//Mage::log($rates, null, 'aaron_dropship_log.log');
			}
       		foreach ($rates as $rate) {
       			$currentCarrier=$rate->getCarrier();
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					//Mage::log('$currentCarrier', null, 'aaron_dropship_log.log');
					//Mage::log($currentCarrier, null, 'aaron_dropship_log.log');
				}
    			if (array_key_exists($currentCarrier, $ratesToAdd)) {
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
						//Mage::log('array_key_exists($currentCarrier, $ratesToAdd)', null, 'aaron_dropship_log.log');
					}
    				if (array_key_exists($rate->getMethod(), $ratesToAdd[$currentCarrier])) {
						if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							//Mage::log('array_key_exists($rate->getMethod(), $ratesToAdd[$currentCarrier])', null, 'aaron_dropship_log.log');
						}
    					$ratesToAdd[$currentCarrier][$rate->getMethod()]['price']+=$rate['price'];
    					$ratesToAdd[$currentCarrier][$rate->getMethod()]['shipping_details'][] = array (
						        	'warehouse'		=> $this->_shippingWarehouse[$key],
						        	'code'			=> $rate->getMethod(),
						        	'price'			=> (float)$rate->getPrice(),
						        	'cost'			=> (float)$rate->getCost(),
						        	'carrierTitle'	=> $rate->getCarrierTitle(),
						        	'methodTitle'	=> $rate->getMethodTitle(),
    								'freightQuoteId'=> $rate->getFreightQuoteId(),
    					);
    					$ratesToAdd[$currentCarrier][$rate->getMethod()]['found']=true;
    				}
    			}
    		}
    		// clean out not found
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				//Mage::log('$ratesToAdd', null, 'aaron_dropship_log.log');
				//Mage::log($ratesToAdd, null, 'aaron_dropship_log.log');
			}
    		foreach ($ratesToAdd as $code=>$rates) {
    			foreach ($rates as $rateKey=>$rate) {
    				if (!$rate['found']) {
						if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							//Mage::log('!$rate[found] == true, unsetting rate', null, 'aaron_dropship_log.log');
							//Mage::log($rates[$rateKey], null, 'aaron_dropship_log.log');
						}
    					$rates[$rateKey]="";
    					unset($rates[$rateKey]);
    				} else {
    					$rates[$rateKey]['found']=false;
    				}
    			}
    			if (count($rates)>0) {
    				$ratesToAdd[$code]=$rates;
    			} else {
    				$ratesToAdd[$code]="";
    				unset($ratesToAdd[$code]);
    			}
    		}
    	}

    	$shipMethodModel = Mage::getModel("dropship/shipmethods");
    	$shipMethodResource = Mage::getResourceModel("dropship/shipmethods");
    	$shipMethods = $shipMethodModel->getShipmethods();
    	$mergedRatesToAdd=array();

    	foreach ($this->_shippingResults as $key=>$result) {
    		if ($key==0) {
    			foreach($shipMethods as $shipMethodId=>$shipMethod) {
    				$rates=$result->getAllRates();
    				foreach ($rates as $rate) {
	    				if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error && $rate->getCarrier()=='freightrate') {
	    					$freightErrorRate=$this->getFreightErrorRate($rate);
	    					continue;
	    				}
    					if ($shipMethodResource->isShipmethodPresent($shipMethodId,$rate->getCarrier(),
                            $rate->getMethod(),$rate->getWarehouse()) &&
    						!array_key_exists($shipMethodId,$mergedRatesToAdd)) {

                                $shippingDetails[] =
                                    array (
                                        'warehouse'		=> $this->_shippingWarehouse[$key],
                                        'code'			=> $rate->getMethod(),
                                        'price'			=> (float)$rate->getPrice(),
                                        'cost'			=> (float)$rate->getCost(),
                                        'carrierTitle'	=> $rate->getCarrierTitle(),
                                        'methodTitle'	=> $rate->getMethodTitle(),
                                        'freightQuoteId'=> $rate->getFreightQuoteId(),
                                        'carrierMethod' => $rate->getCarrier().$rate->getMethod(),
                                    );

    							$mergedRatesToAdd[$shipMethodId]=array(	'title'=>$shipMethod->getTitle(),
    								'price'=>$rate->getPrice(),
    								'freight_quote_id'=>$rate->getFreightQuoteId(),
    								'dispatch_date'=>$rate->getDispatchDate(),
    							    'expected_delivery'=>$rate->getExpectedDelivery(),
    								'shipping_details' => $shippingDetails);
    					}
    				}
   				}
   				continue;
    		}
    		// otherwise
    		foreach ($mergedRatesToAdd as $shipMethodId=>$mergedRate) {
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					//Mage::log('$mergedRatesToAdd as $shipMethodId=>$mergedRate', null, 'aaron_dropship_log.log');
					//Mage::log($mergedRate, null, 'aaron_dropship_log.log');
				}
    			$found=false;
    			$rates=$result->getAllRates();
    			foreach ($rates as $rate) {
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							//Mage::log('rates', null, 'aaron_dropship_log.log');
							//Mage::log($rate, null, 'aaron_dropship_log.log');
					}
    				if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error && $rate->getCarrier()=='freightrate') {
    					$freightErrorRate=$this->getFreightErrorRate($rate);
						if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							//Mage::log('$rate instanceof Mage_Shipping_Model_Rate_Result_Error && $rate->getCarrier()==freightrate', null, 'aaron_dropship_log.log');
							//Mage::log($freightErrorRate, null, 'aaron_dropship_log.log');
						}
    					continue;
    				}
    				if (!$found && $shipMethodResource->isShipmethodPresent($shipMethodId,$rate->getCarrier(),
                            $rate->getMethod(),$rate->getWarehouse()) &&
                            !array_key_exists($rate->getCarrier().$rate->getMethod(),
                                $mergedRatesToAdd[$shipMethodId]['shipping_details']  )) {
     					$mergedRatesToAdd[$shipMethodId]['price'] = $mergedRatesToAdd[$shipMethodId]['price']+$rate['price'];
    					if ($rate->getFreightQuoteId()!='') {
    						$mergedRatesToAdd[$shipMethodId]['freight_quote_id'] = $rate->getFreightQuoteId();
    					}
    					if ($rate->getDispatchDate()!='') {
    						$mergedRatesToAdd[$shipMethodId]['dispatch_date'] = $rate->getDispatchDate();
    					}
    				   	if ($rate->getExpectedDelivery()!='') {
    						$mergedRatesToAdd[$shipMethodId]['expected_delivery'] = $rate->getExpectedDelivery();
    					}
    					$mergedRatesToAdd[$shipMethodId]['shipping_details'][] = array (
						        	'warehouse'		=> $this->_shippingWarehouse[$key],
						        	'code'			=> $rate->getMethod(),
						        	'price'			=> (float)$rate->getPrice(),
						        	'cost'			=> (float)$rate->getCost(),
						        	'carrierTitle'	=> $rate->getCarrierTitle(),
						        	'methodTitle'	=> $rate->getMethodTitle(),
    								'freightQuoteId'=> $rate->getFreightQuoteId(),
                                    'carrierMethod' => $rate->getCarrier().$rate->getMethod(),
                        );
    					$found=true;
    					//break;
    				}
    			}

    			if (!$found) {
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							Mage::log('!$found == true unsetting mergedRatesToAdd', null, 'aaron_dropship_log.log');
						}
    				$mergedRatesToAdd[$shipMethodId]="";
    				unset($mergedRatesToAdd[$shipMethodId]);
    			}
    		}
   		}

        // now lets clear out merged rates where carrier is same throughout
        foreach ($mergedRatesToAdd as $shipMethodId=>$mergedRates) {
            $matchedCarrierMethod ='';
            $diffCarrierMethods = false;
            foreach ($mergedRates['shipping_details'] as $shippingDetails) {
                if ($matchedCarrierMethod=='') {
                    $matchedCarrierMethod = $shippingDetails['carrierMethod'];
                    continue;
                }
                if ($shippingDetails['carrierMethod'] != $matchedCarrierMethod) {
                    $diffCarrierMethods = true;
                    break;
                }
            }
            if (!$diffCarrierMethods) {
                $mergedRatesToAdd[$shipMethodId]="";
                unset($mergedRatesToAdd[$shipMethodId]);
            }
        }

		if (!empty($ratesToAdd)) {
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				//Mage::log('!empty($ratesToAdd) == true', null, 'aaron_dropship_log.log');
			}
    		$finalResult = Mage::getModel('shipping/rate_result');
    		foreach ($ratesToAdd as $rates) {
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					//Mage::log('$ratesToAdd as $rates', null, 'aaron_dropship_log.log');
					//Mage::log($rates, null, 'aaron_dropship_log.log');
				}
	    		foreach ($rates as $rate) {
	    			$rateToAdd= $rate['rate'];
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
						//Mage::log('$rates as $rate', null, 'aaron_dropship_log.log');
						//Mage::log($rate, null, 'aaron_dropship_log.log');
					}
	    			$shippingDetails = $rate['shipping_details'];
	    			if ($rateToAdd->getErrorMessage()!="") {
						if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
							//Mage::log('$rateToAdd->getErrorMessage()!=""', null, 'aaron_dropship_log.log');
							//Mage::log($rateToAdd->getErrorMessage(), null, 'aaron_dropship_log.log');
						}
	    				$method = Mage::getModel('shipping/rate_result_error');
	    				$method->setErrorMessage($rateToAdd->getErrorMessage());
	    			    $method->setCarrier($rateToAdd->getCarrier());
			    		$method->setCarrierTitle($rateToAdd->getCarrierTitle());
			    		$finalResult->append($method);
			    		$rateAdded=true;
			    		break;
	    			} else {
	    				$rate['rate']['price']=$rate['price'];
	    				$method = Mage::getModel('shipping/rate_result_method');
	        			$method->setPrice($this->getHandlingFee($rateToAdd['price']));
						$method->setCost($rateToAdd['price']);
				        $method->setCarrier($rateToAdd->getCarrier());
				    	$method->setCarrierTitle($rateToAdd->getCarrierTitle());
                    	$method->setFreightQuoteId($rateToAdd->getFreightQuoteId());
                    	$method->setExpectedDelivery($rateToAdd->getExpectedDelivery());
                    	$method->setDispatchDate($rateToAdd->getDispatchDate());
				    	$method->setMethod($rateToAdd->getMethod());
				    	$method->setMethodTitle($rateToAdd->getMethodTitle());
	    				$method->setWarehouseShippingDetails(Mage::helper('dropship')->encodeShippingDetails($shippingDetails));
			    		$finalResult->append($method);
						$rateAdded=true;
	    			}
	    		}
	    	}
		}
		if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
			//Mage::log('$ratesToAdd', null, 'aaron_dropship_log.log');
			//Mage::log($$ratesToAdd, null, 'aaron_dropship_log.log');
			//Mage::log('$mergedRatesToAdd', null, 'aaron_dropship_log.log');
			//Mage::log($$mergedRatesToAdd, null, 'aaron_dropship_log.log');
		}
    	if (!empty($mergedRatesToAdd)  ) {  // Will add merged rates to carrier rates as long as not same carrier code
            // See DROP-9 and DROP-20 for logic here
            if (!isset($finalResult)) {$finalResult = NULL;}
    		$finalResult = $this->collectMergedResult($mergedRatesToAdd, $finalResult);
    	} else if (!$rateAdded) {
    		$finalResult = $this->getErrorResult();
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				//Mage::log('$finalResult', null, 'aaron_dropship_log.log');
				//Mage::log($this->getErrorResult(), null, 'aaron_dropship_log.log');
			}
    	}

    	if ($freightErrorRate instanceof Mage_Shipping_Model_Rate_Result_Error && ($rateAdded ||!empty($mergedRatesToAdd)) ) {
    		$finalResult->append($freightErrorRate);
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
			//Mage::log('$freightErrorRate', null, 'aaron_dropship_log.log');
			//Mage::log($freightErrorRate, null, 'aaron_dropship_log.log');
		}
    	}

		if (self::$_debug) {
    		Mage::helper('wsalogger/log')->postDebug('dropship','Merged Result',$finalResult);
    	}
		if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
			//Mage::log('$finalResult', null, 'aaron_dropship_log.log');
			//Mage::log($finalResult, null, 'aaron_dropship_log.log');
		}
    	return $finalResult;
	}

	private function getFreightErrorRate($rate) {
		$method = Mage::getModel('shipping/rate_result_error');
    	$method->setErrorMessage($rate->getErrorMessage());
        $method->setCarrier($rate->getCarrier());
    	$method->setCarrierTitle($rate->getCarrierTitle());
    	return $method;
	}


  	private function collectMergedResult($ratesToAdd, $result) {

        if (is_null($result)) {
            $result = Mage::getModel('shipping/rate_result');
        }
        foreach ($ratesToAdd as $rateToAdd) {
	    	$method = Mage::getModel('shipping/rate_result_method');
	        $method->setPrice($this->getHandlingFee($rateToAdd['price']));
	        $method->setCost((float)$rateToAdd['price']);
	        $method->setCarrier('dropship');
	    	$method->setCarrierTitle($this->getConfigData('title'));
            $method->setFreightQuoteId($rateToAdd['freight_quote_id']);
			$method->setExpectedDelivery($rateToAdd['expected_delivery']);
            $method->setDispatchDate($rateToAdd['dispatch_date']);
            $method->setMethod($rateToAdd['title']);
	    	$method->setMethodTitle($rateToAdd['title']);
	    	if (array_key_exists('shipping_details',$rateToAdd)) {
	    		$method->setWarehouseShippingDetails(Mage::helper('dropship')->encodeShippingDetails($rateToAdd['shipping_details']));
	    	}
	    	$result->append($method);
        }
    	return $result;
    }

	private function getHandlingFee($price) {
		if ($this->_handlingCount>1 && $this->_handlingType !=  self::HANDLING_TYPE_PERCENT
		&& $this->_handlingAction == 'W') {
			$handlingFee = $this->getFinalPriceWithHandlingFee($price) - $price;
			if (self::$_debug) {
				Mage::helper('wsalogger/log')->postDebug('dropship','Handling Fee','Count:'.$this->_handlingCount.', Fee:'.$handlingFee);
			}
			return $price + ($handlingFee*$this->_handlingCount);
		} else {
			return $this->getFinalPriceWithHandlingFee($price);
		}
	}
	
  /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code='')
    {
        $codes = array(
            'weight'=>array(
                'lbs'       => 'Lbs',
                'kgs'       => 'Kgs',
                'grams'     => 'Grams',
                'none'      => 'No weight',
            ),
            'handling_action'=>array(
                'O'         => 'Per Order',
                'W'         => 'Per Warehouse',
            ),
            'shipment_email'=>array(
                'order'     => 'On Place Order',
                'invoice'   => 'On Invoice Creation',
                'never'     => 'Never',
            ),
           
        );

        if (!isset($codes[$type])) {
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

}
