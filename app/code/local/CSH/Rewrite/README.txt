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
 
-------------------------------------------------------------------------------
DESCRIPTION:
-------------------------------------------------------------------------------

Magento install during 1.12.0.0 had a number of core modifications to the
functionality of the website that needed to be refactored in order to perform
the upgrade to Magento 1.14+.

Moving the customizations into this extension will help with the organization
of the modifications as well as provide an easier path to upgrade.

1.  Deleted app/code/local/Mage/Adminhtml/Block/Page/Menu.php, and restored the
    core modifications to the original 1.12.0.0 version. The modifications were
    related to bypassing ACL only for NextBits_FormBuilder extension (which is
    current disabled).
    
2.  Deleted dead (non functioning files):
    app/code/local/Mage/Catalog/Model/Category.php (no changes from core)
    app/code/local/Mage/Catalog/Block/Product/Listmedia.php (not used)
    app/code/local/Mage/Cms/Block/landing.php (not used)
    app/code/local/Mage/Cms/Block/landing.phtml (not used)
    app/code/local/Mage/Checkout/Block/Onepage.php (rewrite to CSH_Custom_Block_Checkout_Onepage)
    app/code/local/Mage/Checkout/Block/Onepage/Abstract.php (no changes from core)

3.  Removed files that are currently not being used in Mage_Catalog... it
    appears they were originally part of the CommerceThemes source code but that
    is no longer within the system (and seems renamed from the original).
    The files include:
        Mage_Catalog_Model_Convert_Adapter_ProductAttributesimport
        Mage_Catalog_Model_Convert_Parser_ProductAttributesexport
    
4.  Rewrite of Mage_GoogleAnalytics_Block_Ga to include additional functionality
    to CSH_Rewrite_Block_Googleanalytics_Ga
    
5.  Moved file CSH_Rewrite_Block_Cms_Eblock from app/code/local/Mage/Cms/Block/Eblock.php
    (NOTE: this file doesn't exist in Core Magento)

6.  Mage_Checkout
    a)  Moved file CSH_Rewrite_Block_Checkout_Onepage_Deliveryinstructions from 
        app/code/local/Mage/Checkout/Block/Onepage/deliveryinstructions.php
        (NOTE: this file doesn't exist in Core Magento)
    b)  Moved file CSH_Rewrite_Block_Checkout_Cart_Header from 
        app/code/local/Mage/Checkout/Block/Cart/Header.php
        (NOTE: this file doesn't exist in Core Magento)
        
7.  Mage_Eav - Moved file CSH_Rewrite_Model_Eav_Entity_Increment_Order from 
    app/code/local/Mage/Eav/Model/Entity/Increment/Order.php. This class is
    called by the database as a custom increment model for the 'sales/order'.
    Disabled the call to evaulate the products for 'is_freight' since the results
    were not being used in creating the order increment_id. 


-------------------------------------------------------------------------------
HOW TO USE:
-------------------------------------------------------------------------------

Since these are modifications to core functionality, there are no instructions
on how to use... 


-------------------------------------------------------------------------------
EXTENSION FILES:
-------------------------------------------------------------------------------

  - app/code/local/CSH/Rewrite/*
  - app/etc/modules/CSH_Rewrite.xml


-------------------------------------------------------------------------------
COMPATIBILITY:
-------------------------------------------------------------------------------

  - Tested with Magento EE 1.12.0.0


-------------------------------------------------------------------------------
RELEASE NOTES:
-------------------------------------------------------------------------------

v.0.1.0: July 8, 2015
  - Initial private release.
  
