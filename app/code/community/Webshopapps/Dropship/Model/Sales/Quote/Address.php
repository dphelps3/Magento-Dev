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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
/**
 * Sales Quote address model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Webshopapps_Dropship_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{


    /**
     * Import address data from order address
     *
     * @param   Mage_Sales_Model_Order_Address $address
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function importOrderAddress(Mage_Sales_Model_Order_Address $address)
    {

        if (!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Desttype','shipping/desttype/active')) {
            return parent::importOrderAddress($address);
        }
        $this->setAddressType($address->getAddressType())
            ->setCustomerId($address->getCustomerId())
            ->setCustomerAddressId($address->getCustomerAddressId())
            ->setDestType($address->getDestType())
            ->setEmail($address->getEmail());

        Mage::helper('core')->copyFieldset('sales_convert_order_address', 'to_quote_address', $address, $this);

        return $this;
    }


    /**
     * Import quote address data from customer address object
     *
     * @param   Mage_Customer_Model_Address $address
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function importCustomerAddress(Mage_Customer_Model_Address $address)
    {
        if (!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Desttype','shipping/desttype/active')) {
            return parent::importCustomerAddress($address);
        }
        Mage::helper('core')->copyFieldset('customer_address', 'to_quote_address', $address, $this);
        $email = null;
        if ($address->hasEmail()) {
            $email =  $address->getEmail();
        }
        elseif ($address->getCustomer()) {
            $email = $address->getCustomer()->getEmail();
        }
        if ($email) {
            $this->setEmail($email);
        }
        $this->setDestType($address->getDestType());

        return $this;
    }


    /**
     * Collecting shipping rates by address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function collectShippingRates()
    {
    	if(!Mage::getStoreConfig('carriers/dropship/active', $this->getQuote()->getStoreId())) {
    		return parent::collectShippingRates();
    	}

        if (!$this->getCollectShippingRates()) {
            return $this;
        }

        $this->setCollectShippingRates(false);

        $this->removeAllShippingRates();

        if (!$this->getCountryId()) {
            return $this;
        }

        $found = $this->requestShippingRates();
        if (!$found) {
        	$this->setShippingAmount(0)
                ->setBaseShippingAmount(0)
                ->setShippingMethod('')
                ->setShippingDescription('');
        }

        return $this;
    }



    /**
     * Request shipping rates for entire address or specified address item
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return bool
     */
    public function requestShippingRates(Mage_Sales_Model_Quote_Item_Abstract $item = null)
    {
    	if(!Mage::getStoreConfig('carriers/dropship/active', $this->getQuote()->getStore()->getId())) {
    		return parent::requestShippingRates();
    	}

        $request = Mage::getModel('shipping/rate_request');
        $this->setWarehouseOnItems();
        if ($this->getWarehouse()==99) {
        	$request->setDropshipSplitRates(true);
        };

        // standard magento codebase START
        $request->setAllItems($item ? array($item) : $this->getAllItems());
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());
        /**
         * need to call getStreet with -1
         * to get data in string instead of array
         */
        $request->setDestStreet($this->getStreet(-1));
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $this->getBaseSubtotal());
        $packageValueWithDiscount = $item
            ? $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
            : $this->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageValueWithDiscount);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());
        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty());

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $item
            ? $item->getBaseRowTotal()
            : $this->getBaseSubtotal() - $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        $request->setFreeMethodWeight($item ? 0 : $this->getFreeMethodWeight());

        /**
         * Store and website identifiers need specify from quote
         */
        /*$request->setStoreId(Mage::app()->getStore()->getId());
        $request->setWebsiteId(Mage::app()->getStore()->getWebsiteId());*/

        $request->setStoreId($this->getQuote()->getStore()->getId());
        $request->setWebsiteId($this->getQuote()->getStore()->getWebsiteId());
        $request->setFreeShipping($this->getFreeShipping());
        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->getQuote()->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->getQuote()->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());

        $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax());

        // standard magento codebase END


        $request->setLiftgateRequired($this->getLiftgateRequired());
      	$request->setNotifyRequired($this->getNotifyRequired());
        $request->setShiptoType($this->getShiptoType());
		$request->setInsideDelivery($this->getInsideDelivery());
		
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Desttype','shipping/desttype/active')) {
            $destType=$this->getData('dest_type');
            if ( $destType!="" && Mage::helper('desttype')->isDestTypeActive($this->getBaseSubtotalWithDiscount(),
                $this->getQuote()->getCustomerGroupId()) ) {
                $request->setUpsDestType($destType);
            }
        }


        $result = Mage::getModel('shipping/shipping')
            ->collectRates($request)
                ->getResult();

        $found = false;
        $dispatchDateFound=false;

        if ($result) {
            $shippingRates = $result->getAllRates();
            $checkDropship=false;


            if (strpos($this->getShippingMethod(),'dropship_Multiple')!== false) {
            	$storeArr=array();
            	$checkDropship=true;
            }

        	foreach ($shippingRates as $shippingRate) {
                $rate = Mage::getModel('sales/quote_address_rate')
                    ->importShippingRate($shippingRate);
                $this->addShippingRate($rate);
        		if ($checkDropship) {
        			$storeArr[]=$rate;
               	}

                if ($this->getShippingMethod()==$rate->getCode()) {
                    $this->setShippingAmount($rate->getPrice());
                    $found = true;
                }
               	if ($rate->getDispatchDate()!='') {
               		$this->setDispatchDate($rate->getDispatchDate());
			    	$dispatchDateFound=true;
               	}

               	if ($rate->getOverridePriceInfo()!='') {
               	    $this->setOverridePriceInfo($rate->getOverridePriceInfo());
               	}

            }

            if (!$found && $checkDropship) {
            	// required for 1.4.2+ && OneStep
            	$warehouseShipDetails = Mage::helper('dropship')->decodeShippingDetails($this->getWarehouseShippingDetails());
            	$found=true;
            	$runningPrice=0;
            	foreach ($warehouseShipDetails as $checkedRate) {
            		$found=false;
	    			foreach ($storeArr as $rate) {
	            		if ($checkedRate['code'] == $rate->getCode() && $checkedRate['price'] == $rate->getPrice()) {
	            			$runningPrice+=$rate->getPrice();
	            			$found=true;
	            			break;
	            		}
            			if ($rate->getDispatchDate()!='') {
                			$this->setDispatchDate($rate->getDispatchDate());
			    			$dispatchDateFound=true;
						}
						if ($rate->getOverridePriceInfo()!='') {
						    $this->setOverridePriceInfo($rate->getOverridePriceInfo());
						}
	            	}
	            	if (!$found) {
	            		break;
	            	}
            	}
            	if ($found) {
            		$this->setShippingAmount($runningPrice);
            	}
            }

        }

        if (!$dispatchDateFound) {
        	$this->setDispatchDate('');
        }

        return $found;
	}

    public function createShippingRate($shippingDetails) {
    	$totalPrice=0;
    	$freightQuoteId='';
    	foreach ($shippingDetails as $details) {
    		$totalPrice += $details['price'];
    		if ($details['freightQuoteId']!='') {
    			$freightQuoteId=$details['freightQuoteId'];
    		}
    	}


    	$shippingRates = $this->getAllShippingRates();
    	$title = 'Multiple '.Mage::helper('dropship')->getWarehouseDescription().'s';
    	$found = false;

        foreach ($shippingRates as $shippingRate) {
        	if ($shippingRate->getCarrier()=='dropship' && $title==$shippingRate->getMethod()) {
        		$shippingRate->setPrice($totalPrice);
        		$found=true;
        		break;
        	}
        }

    	if (!$found) {
	    	$mergedRatesToAdd = array ( array (
	        	'price'				=> $totalPrice,
	    		'title'				=> $title,
	    		'freight_quote_id' 	=> $freightQuoteId,
	    		'expected_delivery'	=> '',
	    		'dispatch_date'		=> '',
	    	// TODO Add dispatch date, delivery date
	       	));

	    	 $result = Mage::getModel('dropship/shipping_shipping')
	            	->collectMergedRates($this->getQuote()->getStore()->getId(),$mergedRatesToAdd)
	                ->getResult();

	         if ($result) {
		     	$shippingRates = $result->getAllRates();

		        foreach ($shippingRates as $shippingRate) {
		        	$rate = Mage::getModel('sales/quote_address_rate')
		            	->importShippingRate($shippingRate);

		            $this->addShippingRate($rate);
	         		$this->setShippingMethod($rate->getCode());
	         		$this->setShippingAmount($rate->getPrice());
		        }
	         }
    	}

    }

       	public function setWarehouseOnItems()
          {
          	foreach($this->getAllItems() as $item) {

          		$wareAttr=Mage::helper('dropship/shipcalculate')->getWarehouse($item);
          		$warehouses=explode(',',$wareAttr);
          		$warehouse='';
          		if (count($warehouses)>1) {
          			$warehouse = Mage::helper('dropship')->getNearestWarehouse($this->getCountryId(),$this->getRegionCode(),$this->getPostcode(),$warehouses);
          		} else {
          			$warehouse=$warehouses[0];
          		}
          		$item->setWarehouse($warehouse);
          	}
    }
}
