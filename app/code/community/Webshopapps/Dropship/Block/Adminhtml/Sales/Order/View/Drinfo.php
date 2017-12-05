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
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Dropship_Block_Adminhtml_Sales_Order_View_Drinfo
    extends Mage_Adminhtml_Block_Sales_Order_View_Info
{

	public function getDropshipInfoHtml() {
		
		$order = $this->getOrder();
		$htmlOutput='';
		$whss = Mage::helper('dropship')->decodeShippingDetails($order->getWarehouseShippingDetails()) ;
		if (!empty($whss)) {
    		$warehouseText='';
    	    foreach ($whss as $whs) {
    	    	$warehouseText .= Mage::helper('dropship')->getDescription($whs['warehouse']);
    	    	$warehouseText .=  '<strong> : '.$whs['carrierTitle'];
                $warehouseText .= ' - '.$whs['methodTitle'];
    	    	$warehouseText .= '</strong> '.$order->formatPrice($whs['price']);
    	    	if (array_key_exists('freightQuoteId',$whs) && $whs['freightQuoteId']!='') {
                    $warehouseText .= ' Quote Id: '.$whs['freightQuoteId'];
    	    	}
                $warehouseText .= '<br />';
    	    }
    	     
    
    		$htmlOutput = '<div class="box-right"><div class="clear"></div><div class="entry-edit">';
    		$htmlOutput.= '<div class="entry-edit-head">';
    		$htmlOutput.= '<h4 class="icon-head head-shipping-method">';
    		$htmlOutput.= Mage::helper("sales")->__("Warehouse Shipping Information");
            $htmlOutput.= '</h4>';
    		$htmlOutput.= '</div><fieldset>';
    		$htmlOutput.= $warehouseText;
    		$htmlOutput.= '</fieldset> <div class="clear"/></div></div>';
		}
		return "'".$htmlOutput."'";
	}
	
}