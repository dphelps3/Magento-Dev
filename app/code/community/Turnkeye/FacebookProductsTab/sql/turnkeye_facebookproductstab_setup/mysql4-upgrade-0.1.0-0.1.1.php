<?php
/**
 * TurnkeyE Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send
 * an email to info@turnkeye.com so we can send you a copy immediately.
 *
 * @category   Turnkeye
 * @package    Turnkeye_FacebookProductsTab
 * @copyright  Copyright (c) 2010-2012 TurnkeyE Co. (http://turnkeye.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Upgrade script 0.1.0 -> 0.1.1
 *
 * @category   Turnkeye
 * @package    Turnkeye_FacebookProductsTab
 * @author     Viacheslav Fedorenko <v.fedorenko@turnkeye.com>
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('catalog/setup', 'core_setup');

$installer->startSetup();
$installer->updateAttribute('catalog_product', 'is_facebook', 'used_in_product_listing', 1);
$installer->endSetup();
