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
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*
 *
 * @category   Webshopapps
 * @package    Webshopapps_Conwayfreight
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Dropship_Model_Sales_Quote_Address_Rate extends Mage_Sales_Model_Quote_Address_Rate
{


    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {
    	if(!Mage::getStoreConfig('carriers/dropship/active', Mage::app()->getStore()->getId())) {
    		return parent::importShippingRate($rate);
    	}

        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
            $this
                ->setCode($rate->getCarrier().'_error')
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setWarehouse($rate->getWarehouse())
                ->setWarehouseShippingDetails($rate->getWarehouseShippingDetails())
                ->setErrorMessage($rate->getErrorMessage())
            ;
        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
        	$this
                ->setCode($rate->getCarrier().'_'.$rate->getMethod())
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setMethod($rate->getMethod())
                ->setWarehouse($rate->getWarehouse())
                ->setWarehouseShippingDetails($rate->getWarehouseShippingDetails())
                ->setExpectedDelivery($rate->getExpectedDelivery())
                ->setDispatchDate($rate->getDispatchDate())
                ->setFreightQuoteId($rate->getFreightQuoteId())
                ->setMethodTitle($rate->getMethodTitle())
                ->setMethodDescription($rate->getMethodDescription())
                ->setOverridePriceInfo($rate->getOverridePriceInfo())
                ->setPrice($rate->getPrice())
            ;
        }
        return $this;
    }
}