<?php
/**
 * Sitesquad - Custom Wishlist functionality
 *
 * =============================================================================
 * NOTE: See README.txt for more information about this extension
 * =============================================================================
 *
 * @category   CSH
 * @package    CSH_Wishlist
 * @copyright  Copyright (c) 2015 Sitesquad. (http://www.sitesquad.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Phil Mobley <phil.mobley@sitesquad.net>
 */

class CSH_Wishlist_Block_Customer_Wishlist_Items
    extends Mage_Wishlist_Block_Customer_Wishlist_Items
{
    /**
     * Check if the totals should be shown
     *
     * @return boolean
     */
    public function getShowTotals()
    {
        return true;
    }
    
    
    /**
     * Calculates the total price of all items in the wishlist
     *
     * @return string
     */
    public function getTotalPrice()
    {
        $listItems  = $this->getItems();
        $total      = 0.0;
        
        foreach ($listItems as $item) {
            $total += ($item->getQty() * $item->getPrice());
        }
        
        return Mage::helper('core')->currency($total);
    }
    
    
    /**
     * Override the template if this feature is enabled
     *
     * (non-PHPdoc)
     * @see Mage_Core_Block_Template::_toHtml()
     */
    public function _toHtml()
    {
        if ($this->getShowTotals()) {
            $this->setTemplate('csh/wishlist/item/list.phtml');
        }
    
        return parent::_toHtml();
    }
}
