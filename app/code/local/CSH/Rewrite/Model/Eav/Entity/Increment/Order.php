<?php
/**
 * Sitesquad - Refactoring modifications to core Magento files
 *
 * =============================================================================
 * NOTE: See README.txt for more information about this extension
 * =============================================================================
 *
 * @category   CSH
 * @package    CSH_Rewrite
 * @copyright  Copyright (c) 2015 Sitesquad. (http://www.sitesquad.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Phil Mobley <phil.mobley@sitesquad.net>
 */

/**
 * NOTE: this file does not exist in Core Magento
 *
 * Moved file from app/code/local/Mage/Eav/Model/Entity/Increment/Order.php
 */

/**
 * =============================================================================
 * SITESQUAD NOTE:
 * -----------------------------------------------------------------------------
 * This file does not exist in the core Magento files, but the database has been
 * modified to use this increment model instead of the default 'numeric' one.
 * =============================================================================
 *
 * SITESQUAD CHANGES:
 *
 * Because the freight setting was not being used in the current code, I
 * disabled the call to load the products and check for freight... and also
 * moved that logic into another function
 */
class CSH_Rewrite_Model_Eav_Entity_Increment_Order
    extends Mage_Eav_Model_Entity_Increment_Abstract
{
    /**
     * Get chars allowed in the increment ID string
     *
     * @return string
     */
    public function getAllowedChars()
    {
        return '0123456789';
    }

    
    /**
     * Calculates the next increment ID
     *
     * (non-PHPdoc)
     * @see Mage_Eav_Model_Entity_Increment_Interface::getNextId()
     */
    public function getNextId()
    {
        $lastId = $this->getLastId();

        if (strpos($lastId, $this->getPrefix())===0) {
            $lastId = substr($lastId, strlen($this->getPrefix()));
        }

        $lastId         = str_pad((string)$lastId, $this->getPadLength(), $this->getPadChar(), STR_PAD_LEFT);
        $nextId         = '';
        $bumpNextChar   = true;
        $chars          = $this->getAllowedChars();
        $lchars         = strlen($chars);
        $lid            = strlen($lastId)-1;
        $isFreight      = false;
        
        // disabled for now
        if (false) {
            $isFreight = $this->_orderHasFreight();
        }

        for ($i = $lid; $i >= 0; $i--) {
            $p = strpos($chars, $lastId{$i});
            
            if (false===$p) {
                throw Mage::exception(
                    'Mage_Eav',
                    Mage::helper('eav')->__('Invalid character encountered in increment ID: %s', $lastId)
                );
            }
            
            if ($bumpNextChar) {
                $p++;
                $bumpNextChar = false;
            }
            
            if ($p===$lchars) {
                $p = 0;
                $bumpNextChar = true;
            }
            
            $nextId = $chars{$p}.$nextId;
        }
		/*if($isFreight == true) {
			$nextId = substr_replace($nextId, 'C', 1, 0);
		} else {
			$nextId = substr_replace($nextId, 'C', 1, 0);
		}*/
        
        return $this->format($nextId);
    }
    
    
    /**
     * Checks if one or more items in the order has freight setting
     *
     * @return boolean
     */
    protected function _orderHasFreight()
    {
        $cart = Mage::getSingleton('checkout/session');
        
        $freightflag = false;
        
        foreach ($cart->getQuote()->getAllItems() as $item) {
            //Mage::log('test');
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
        
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                        $product_id = $child->getProductId();
                        $productObj = Mage::getModel('catalog/product')->load($product_id);
                        $isFreight = $productObj->getData('is_freight'); //our shipping attribute code
                        
                        if ($isFreight == true) {
                            return true;
                        }
                    }
                }
            } else {
                $product_id = $item->getProductId();
                $productObj = Mage::getModel('catalog/product')->load($product_id);
                $isFreight = $productObj->getData('is_freight'); //our shipping attribute code
                
                if ($isFreight == true) {
                    return true;
                }
            }
        }
        
        return $freightflag;
    }
}
