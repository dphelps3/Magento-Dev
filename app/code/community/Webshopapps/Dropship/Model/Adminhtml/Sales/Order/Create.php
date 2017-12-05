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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
/**
 * Order create model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webshopapps_Dropship_Model_Adminhtml_Sales_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
	
 	public function collectShippingRates()
    {
        $address=$this->getQuote()->getShippingAddress();
        $calculateModel = Mage::getModel('dropship/shipcalculate');
        
        $country=$address->getCountryId();
        $city=$address->getCity();
        $postcode=$address->getPostcode();
        $regionId=$address->getRegionId();
        $region=$address->getRegion();
        
        
        $calculateModel->retrieveRates($this->getQuote());
       ///  $this->collectRates();
       // $this->getQuote->save();
        
        return $this;
    }
}