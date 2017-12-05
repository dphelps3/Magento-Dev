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
require_once  'Mage/Checkout/controllers/OnepageController.php';
class Webshopapps_Dropship_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
 	public function saveShippingMethodAction()
    {
    	
    if (!Mage::getStoreConfig('carriers/dropship/active')) {
        		return parent::saveShippingMethodAction();
    	}
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
        	$quote = $this->getOnepage()->getQuote();
        	
        	$quote->getShippingAddress()
        	->setWarehouseShippingDetails("")
        	->setWarehouseShippingHtml("")
        	->save();
        	
            $data = $this->getRequest()->getPost('shipping_method', '');
            if (!empty($data)) {
            	return parent::saveShippingMethodAction();
            }
            
            $data = $this->getRequest()->getPost();
            
            $result = $this->getOnepage()->saveWarehouseShippingMethod($data);
            
            
            /*
            $result will have erro data if shipping method is empty
            */
            if(!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
	
}