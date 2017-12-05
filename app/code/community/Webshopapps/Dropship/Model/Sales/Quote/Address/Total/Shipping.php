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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Sales_Quote_Address_Total_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping
{

    /**
     * Collect totals information about shipping
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {

    	if (!Mage::getStoreConfig('carriers/dropship/active')) {
    		return parent::collect($address);
    	}

        parent::collect($address);

        $address->collectShippingRates();

        $this->_setAmount(0)
            ->_setBaseAmount(0);
        $method = $address->getShippingMethod();
        $expectedDeliveryFound=false;

        if  (strpos($method,'dropship_Multiple')!== false) {
            // required for 1.4.2+ && OneStep
            $warehouseShipDetails = Mage::helper('dropship')->decodeShippingDetails($address->getWarehouseShippingDetails());
            $found=true;
            $runningPrice=0;
            foreach ($warehouseShipDetails as $checkedRate) {
            	$found=false;


    			foreach ($address->getAllShippingRates() as $rate) {

            		if ($checkedRate['code'] == $rate->getCode() && $checkedRate['price'] == $rate->getPrice()) {
            			$runningPrice+=$rate->getPrice();
            			// matrixdays
            			if ($rate->getExpectedDelivery()!='') {
                        	$address->setExpectedDelivery($rate->getExpectedDelivery());
                        	$expectedDeliveryFound=true;
                    	}

                    	if ($rate->getFreightQuoteId()) {
							$address->setFreightQuoteId($rate->getFreightQuoteId());
				    	} else {
							$address->setFreightQuoteId('');
				    	}
            			$found=true;
            			if ($rate->getDispatchDate()!='') {
	                        $address->setDispatchDate($rate->getDispatchDate());
            			}
            			break;
            		}
    				if ($rate->getDispatchDate()!='') {
                        $address->setDispatchDate($rate->getDispatchDate());
    				}
    				if ($rate->getOverridePriceInfo()!='') {
    				    $address->setOverridePriceInfo($rate->getOverridePriceInfo());
    				}
            	}
            	if (!$found) {
            		break;
            	}
            }

            if ($found) {
            	$amountPrice = $address->getQuote()->getStore()->convertPrice($runningPrice, false);
                $this->_setAmount($amountPrice);
                $this->_setBaseAmount($runningPrice);
                $title = 'Multiple '.Mage::helper('dropship')->getWarehouseDescription().'s';
                $carrierTitle = Mage::getStoreConfig('carriers/dropship/title');
                $address->setShippingDescription($carrierTitle.' - '.$title);
            }
        }


        if ($method) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode()==$method) {
                    $amountPrice = $address->getQuote()->getStore()->convertPrice($rate->getPrice(), false);
                    $this->_setAmount($amountPrice);
                    $this->_setBaseAmount($rate->getPrice());
                    $address->setShippingDescription($rate->getCarrierTitle().' - '.$rate->getMethodTitle());
                    if ($rate->getExpectedDelivery()!='') {
                        $address->setExpectedDelivery($rate->getExpectedDelivery());
                        $expectedDeliveryFound=true;
                    }
                    if ($rate->getDispatchDate()!='') {
	                	$address->setDispatchDate($rate->getDispatchDate());
            		}
                    if ($rate->getFreightQuoteId()) {
    					$address->setFreightQuoteId($rate->getFreightQuoteId());
    			    } else {
    					$address->setFreightQuoteId('');
				    }
				    if ($rate->getOverridePriceInfo()!='') {
				        $address->setOverridePriceInfo($rate->getOverridePriceInfo());
				    }
                    break;
                }
                if ($rate->getDispatchDate()!='') {
                    $address->setDispatchDate($rate->getDispatchDate());
            	}
            }
        }

        if (!$expectedDeliveryFound) {
        	$address->setExpectedDelivery('');
        }


        return $this;
    }

}
