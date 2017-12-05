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
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{

	private $_request;
	private $_debug;
	
    /**
     * Retrieve all methods for supplied shipping data
     *
     * @todo make it ordered
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    { 	
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Freightrate')) {
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				Mage::log('collectFreightRates1: ', null, 'aaron_dropship_error.log');
			}
    		return $this->collectFreightRates($request);
    	} else {
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				Mage::log('collectDropshipRates: ', null, 'aaron_dropship_error.log');
				//Mage::log($this->getErrorResult(), null, 'aaron_dropship_error.log');
				//Mage::log($this->debug(), null, 'aaron_dropship_error.log');
			}
			// This runs
    		return $this->collectDropshipRates($request);
    	}
    }
    
    public function collectDropshipRates(Mage_Shipping_Model_Rate_Request $request)
    { 
     	if (!Mage::helper('dropship')->isActive() || sizeof($request->getAllItems())<1) {
     		return parent::collectRates($request);
     	}
     	
     	$this->_debug=Mage::helper('wsalogger')->isDebug('Webshopapps_Dropship');
    	
     	if (!$request->getOrig()) {
            $request
                ->setCountryId(Mage::getStoreConfig('shipping/origin/country_id', $request->getStore()))
                ->setRegionId(Mage::getStoreConfig('shipping/origin/region_id', $request->getStore()))
                ->setCity(Mage::getStoreConfig('shipping/origin/city', $request->getStore()))
                ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode', $request->getStore()));
        }
        
        $this->_request = $request;
    	if ($this->_debug) {
    		Mage::helper('wsalogger/log')->postDebug('dropship','Dropship Collecting',$request->getDropshipCollecting());
    		Mage::helper('wsalogger/log')->postDebug('dropship','Limit carrier',$request->getLimitCarrier());
    	}
		if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				Mage::log('Dropship Collecting: ', null, 'aaron_dropship_error.log');
				Mage::log('Dropship Limit Carriers: ', null, 'aaron_dropship_error.log');
			}
     	
     	// else want to restrict what's sent to the carriers
     	if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvZHJvcHNoaXAvc2hpcF9vbmNl',
        	'aG9sZGluZ3Vw','Y2FycmllcnMvZHJvcHNoaXAvc2VyaWFs')) { return parent::collectRates($request);}
 
        $limitCarrier = $request->getLimitCarrier();
		if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
			Mage::log('Dropship Limit Carriers: ', null, 'aaron_dropship_error.log');
			Mage::log($limitCarrier, null, 'aaron_dropship_error.log');
		}
        if (!$limitCarrier) {
            $carriers = Mage::getStoreConfig('carriers', $request->getStoreId());
			if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
				Mage::log('Dropship Limit Carriers2: ', null, 'aaron_dropship_error.log');
				Mage::log($carriers, null, 'aaron_dropship_error.log');
			}
            foreach ($carriers as $carrierCode=>$carrierConfig) {
            	if (Mage::helper('dropship')->calculateDropshipRates() &&
                    (($carrierCode!='dropship' && !$request->getDropshipCollecting()) ||
            		($carrierCode=='dropship' && $request->getDropshipCollecting()))) {
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
						Mage::log('Continueing without adding the carrier', null, 'aaron_dropship_error.log');
						Mage::log($carrierCode, null, 'aaron_dropship_error.log');
					}
	    			continue;
				}
				$this->collectCarrierRates($carrierCode, $request);
				if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
					Mage::log('Added the carrier: ', null, 'aaron_dropship_error.log');
					Mage::log($carrierCode, null, 'aaron_dropship_error.log');
				}
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }
            foreach ($limitCarrier as $carrierCode) {
				if (Mage::helper('dropship')->calculateDropshipRates() &&
                    (($carrierCode!='dropship' && !$request->getDropshipCollecting()) ||
            		($carrierCode=='dropship' && $request->getDropshipCollecting()))) {
	    			continue;
				}
                $carrierConfig = Mage::getStoreConfig('carriers/'.$carrierCode, $request->getStoreId());
                if (!$carrierConfig) {
                    continue;
                }
                $this->collectCarrierRates($carrierCode, $request);
            }
        }	
        
        if (!empty($this->_result) && Mage::getStoreConfig('carriers/dropship/restrict_overnight'))  {
        	$this->restrictOvernight($request);
        }

        return $this;
    }

 	private function restrictOvernight(Mage_Shipping_Model_Rate_Request $request) {
 		$restrictOvernight=false;
 		foreach ($request->getAllItems() as $item) {
 			$product = Mage::getModel('catalog/product')->loadByAttribute('entity_id', $item->getProductId(), 'overnight_delivery');
            if (!$product->getData('overnight_delivery')) {
            	$restrictOvernight=true;
            	break;
            }
 		}
 		if ($restrictOvernight) {
 			$found=false;
 			// remove overnight rates
 			$localresults=$this->getResult();
 			$rates = $localresults->getAllRates();
 			foreach ($rates as $key=>$rate) {
 				if (strstr($rate->getMethodTitle(),"Next Day") || strstr($rate->getMethodTitle(),"Overnight")) {
 					$rates[$key]="";
 					unset($rates[$key]);
 					$found=true;
 				}
 			}
 			if ($found) {
	 			$this->getResult()->reset();
	 			foreach ($rates as $rate) {
	 				$this->getResult()->append($rate);
	 			}
 			}
 		}
 	}
 	
    public function collectMergedRates($storeId, $ratesToAdd) {
    	$this->resetResult();
    	$carrier = $this->getCarrierByCode("dropship", $storeId);
    	$this->getResult()->append($carrier->createMergedRate($ratesToAdd));
      	return $this;
    }
    
    
    
    /************************** Required for Freight Rate **********************/
 	public function collectFreightRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	$freightRateConfig=Mage::getStoreConfig('carriers/freightrate');    	
		$items = $request->getAllItems();
    	
     	if (!$freightRateConfig['active']  || sizeof($request->getAllItems())<1 || !Mage::helper('freightrate')->customerGroupApplies($items)
     		|| !$request->getDropshipCollecting() ) { 
     		return $this->collectDropshipRates($request);
     	}
     	
     	$limitCarrier = $request->getLimitCarrier();
     	if (is_array($limitCarrier) && !in_array('freightrate',$limitCarrier)) {
     		return $this->collectDropshipRates($request);
     	}

     	// else want to restrict what's sent to the carriers
     	$newRequest = clone $request;
     	$freightModel = Mage::getModel('freightrate/freightrate');
     	$nonFreightItemPresent = false;
       	$this->_freightFound=false;
     	$exceedsMinOrder = false;
     	$this->_debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Freightrate');
     	     	
     	if (Mage::helper('freightrate')->cartExceedsMinOrder($request)) {
     		$exceedsMinOrder = true;
     	}
     	
     	if ($freightModel->getNonFreightItems($newRequest,$nonFreightItemPresent) ) {
     		$this->_freightFound=true;   	
     	}
     	
    	if (!$this->_freightFound && !$exceedsMinOrder) {
     		return $this->collectDropshipRates($request);
     	}
    	if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvZnJlaWdodHJhdGUvc2hpcF9vbmNl',
        	'aG9yc2VzaG9l','Y2FycmllcnMvZnJlaWdodHJhdGUvc2VyaWFs')) { return $this->collectDropshipRates($request); }
     
    	if (($nonFreightItemPresent && !Mage::getStoreConfig('carriers/freightrate/force_freight')) &&
     		Mage::helper('freightrate')->showOtherRates($exceedsMinOrder)) {
	        $this->collectDropshipRates($request);
     	}     	
     	
        $myResults=$this->getResult();
        if ( (!$exceedsMinOrder || $this->_freightFound) && !empty($myResults) &&
		  			is_array($myResults->getAllRates()) && count($myResults->getAllRates())) {

	  		$rates=$myResults->getAllRates();
	  		foreach ($rates as $rate) {
  			    if ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
		  			return $this;
		  		}
	  		}
  	    }
  	    // otherwise not found rate, so use freightrate
      	$carrier = $this->getCarrierByCode("freightrate", $newRequest->getStoreId());
      	$this->getResult()->append($carrier->collectFreightRates($newRequest));
        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier("freightrate");
        $error->setCarrierTitle($carrier->getCarrierTitle());
        $error->setErrorMessage($freightModel->getDisclaimer());
        $this->getResult()->append($error);
        
        return $this;
     	
     	
    }
    
        public function collectCarrierRates($carrierCode, $request)
        {        	
    		if (!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Freightrate')) {
    			return parent::collectCarrierRates($carrierCode, $request);
    		}
        	$carrier = $this->getCarrierByCode($carrierCode, $request->getStoreId());
            if (!$carrier) {
                return $this;
            }
            $result = $carrier->checkAvailableShipCountries($request);
            if (false !== $result && !($result instanceof Mage_Shipping_Model_Rate_Result_Error)) {
                $result = $carrier->proccessAdditionalValidation($request);
            }
            /*
            * Result will be false if the admin set not to show the shipping module
            * if the devliery country is not within specific countries
            */
            if (false !== $result){
                if (!$result instanceof Mage_Shipping_Model_Rate_Result_Error) {
                    $result = $carrier->collectRates($request);
					if($_SERVER["REMOTE_ADDR"] == '72.239.158.50'){
						Mage::log('collectCarrierRates collecting rates', null, 'aaron_dropship_error.log');
						Mage::log($result, null, 'aaron_dropship_error.log');
					}
                }
                // sort rates by price
                if (method_exists($result, 'sortRatesByPrice')) {
                    $result->sortRatesByPrice();
                }
                if ($this->_freightFound && !empty($result) &&
		  			is_array($result->getAllRates()) && count($result->getAllRates())) {

		  				$freightModel = Mage::getModel('freightrate/freightrate');
		  				$rates=$result->getAllRates();
			  		    $rate=$rates[0];
		  			    if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
		  			    	// we have an error on our hands. Lets change the message
                			//$rate->setErrorMessage($freightModel->getDisclaimer());
                			return $this;
                		}	else {
                				if(Mage::getStoreConfig('carriers/freightrate/residential_add') && $request->getUpsDestType() != 'COM') {
                					foreach ($rates as $rate) {
                						$rate->setPrice($rate->getPrice() + Mage::getStoreConfig('carriers/freightrate/residential_surcharge'));
                					}
                				}
	                		$error = Mage::getModel('shipping/rate_result_error');
				            $error->setCarrier($rate->getCarrier());
				            $error->setCarrierTitle($rate->getCarrierTitle());
				            $error->setErrorMessage($freightModel->getDisclaimer());
				            $result->append($error);
                		}
  				}

                $this->getResult()->append($result);
            }
            return $this;
    }
    
	
}