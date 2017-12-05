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

// customization to dynamically set parent depending on CE or EE version
if (class_exists('Enterprise_Wishlist_Helper_Data')) {
    require_once  'Enterprise/Wishlist/controllers/IndexController.php';
    class CSH_Wishlist_Abstract_IndexController extends Enterprise_Wishlist_IndexController { }
} else {
    require_once  'Mage/Wishlist/controllers/IndexController.php';
    class CSH_Wishlist_Abstract_IndexController extends Mage_Wishlist_IndexController { }
}

// this class extends the "temp" class defined above
class CSH_Wishlist_IndexController extends CSH_Wishlist_Abstract_IndexController
{
    /**
     * Adding new item
     */
    public function addAction()
    {
        // will bypass all default functionality if params are identified to be
        // multi-item
        $input      = $this->getRequest()->getParam('product');
        $isMultiple = (strpos($input, '_') !== false);
        $wishlist   = $this->_getWishlist();
        
        if ($wishlist && $isMultiple) {
            // splint ids into an array and process
            $count      = $this->_addMultiple(explode('_', $input));
            $session    = Mage::getSingleton('customer/session');           
 
            if ($count) {
                $message = $this->__(
                    '%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.',
                    ($count == 1) ? '1 item' : $count . ' items',
                    Mage::helper('core')->escapeUrl($referer)
                );
                
                $referer = $session->getBeforeWishlistUrl();
                
                if ($referer) {
                    $session->setBeforeWishlistUrl(null);
                } else {
                    $referer = $this->_getRefererUrl();
                }
                
                // Set referer to avoid referring to the compare popup window
                $session->setAddActionReferer($referer);
                
                Mage::helper('wishlist')->calculate();
                
                $session->addSuccess($message);
                $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
                
            } else {
                $session->addError($this->__('Unable to add products to wishlist.'));
                $this->_redirect('*/');
            }
            
            // multiple item processing will stop default action behavior
            return;
        }
        
        return parent::addAction();
    }
    
    
    /**
     * Processes the multiple product ids to be saved in the wishlist. Returns
     * the total number of items added (without error) to the wishlist.
     *
     * @param  array $productIds
     * @return int
     */
    protected function _addMultiple($productIds = array())
    {
        $wishlist   = $this->_getWishlist();
        $session    = Mage::getSingleton('customer/session');
        $count      = 0;
        $errors     = array();
        $eventData  = array();

        foreach ($productIds as $pid) {
            $product = Mage::getModel('catalog/product')->load($pid);
            
            if ($product->getId() && $product->isVisibleInCatalog()) {
                try {
                    $requestParams = $this->getRequest()->getParams();
                    
                    if ($session->getBeforeWishlistRequest()) {
                        $requestParams = $session->getBeforeWishlistRequest();
                        $session->unsBeforeWishlistRequest();
                    }
                    
                    // oddly enough, this also loads the product (separately)
                    $buyRequest = new Varien_Object($requestParams);
                    $result     = $wishlist->addNewItem($product, $buyRequest);
                    
                    if (is_string($result)) {
                        Mage::throwException($result);
                    }

                    // technically this should be sent after wishlist is saved
                    /* @var $result Mage_Wishlist_Model_Item */
                    $eventData[$pid] = array(
                        'wishlist'  => $wishlist,
                        'product'   => $product,
                        'item'      => $result
                    );
                    
                    // was successful
                    $count++;
                    
                } catch (Mage_Core_Exception $e) {
                    $errors[$pid] = $this->__(
                        'An error occurred while adding item to wishlist: %s',
                        $e->getMessage()
                    );
                } catch (Exception $e) {
                    $errors[$pid] = $this->__(
                        'An error occurred while adding item to wishlist.'
                    );
                }
            } else {
                // product doesn't exist, or is not configured to be visible
                if ($product->getId()) {
                    $errors[$pid] = $this->__('Product not visible to front');
                } else {
                    $errors[$pid] = $this->__('Product does not exist');
                }
            }
        }
        
        // check if any items have been added, and post-process the stuff
        if ($count) {
            try {
                $wishlist->save();
                
                // save was successful so send out events for valid product adds
                foreach ($eventData as $data) {
                    try {
                        Mage::dispatchEvent('wishlist_add_product', $data);
                        
                    } catch (Exception $e) {
                        // exception occurred within an event, so log and continue
                        Mage::logException($e);
                    }
                }
            } catch (Exception $e) {
                $errors[] = $this->__('An error occurred saving wishlist');
            }
        }
        
        if (count($errors) && $count) {
            // only need error message if some of the items couldn't be added
            $session->addError(
                $this->__(
                    '%s products could not be added or do not exist.',
                    $count($errors)
                )
            );
        }

        return $count;
    }
}
