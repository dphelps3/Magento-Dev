/**
 * Sitesquad - Custom Coupon functionality
 *
 * =============================================================================
 * NOTE: See README.txt for more information about this extension
 * =============================================================================
 *
 * @category   CSH
 * @package    CSH_Coupon
 * @copyright  Copyright (c) 2015 Sitesquad. (http://www.sitesquad.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Phil Mobley <phil.mobley@sitesquad.net>
 */
 
-------------------------------------------------------------------------------
DESCRIPTION:
-------------------------------------------------------------------------------

Enabling the ability of the customer to input a coupon during the final step
of checkout.

NOTE: not supported in Multishipping checkout mode


-------------------------------------------------------------------------------
HOW TO USE:
-------------------------------------------------------------------------------

When enabled, the coupon form is added to the "Order Review" tab during OnePage
checkout.


-------------------------------------------------------------------------------
EXTENSION FILES:
-------------------------------------------------------------------------------

  - app/code/local/CSH/Coupon/*
  - app/design/frontend/base/default/layout/csh/coupon.xml
  - app/design/frontend/base/default/template/csh/*
  - app/etc/modules/CSH_Coupon.xml
  

-------------------------------------------------------------------------------
COMPATIBILITY:
-------------------------------------------------------------------------------

  - Tested with Magento EE 1.12.0.0


-------------------------------------------------------------------------------
RELEASE NOTES:
-------------------------------------------------------------------------------

v.0.1.0: July 9, 2015
  - Initial private release.
  
  
-------------------------------------------------------------------------------
TODO:
-------------------------------------------------------------------------------

  - Create System Config option to hide/show the coupon form on checkout review
  - Support Multishipping checkout in addition to Onepage
  