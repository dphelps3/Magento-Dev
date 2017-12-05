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

/* @var $installer Mage_Core_Model_Resource_Setup */
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
    UPDATE {$this->getTable('eav_entity_type')}
    SET `increment_model` = 'eav/entity_increment_numeric'
    WHERE `increment_model` = 'eav/entity_increment_order';
");

$installer->endSetup();