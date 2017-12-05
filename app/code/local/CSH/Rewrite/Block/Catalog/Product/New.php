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
 * Modifies the core functionality by:
 *
 * 1) Changing default count to 50 (was 5)
 */

class CSH_Rewrite_Block_Catalog_Product_New
    extends Mage_Catalog_Block_Product_New
{
    const DEFAULT_PRODUCTS_COUNT = 50;
}
