<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 11/12/14
 * Time: 2:05 PM
 */

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');
$attSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$select = $installer->getConnection()->select();
$select
    ->from(array('eag' => $installer->getTable('eav_attribute_group')), 'attribute_group_id')
    ->joinInner(array('eea' => $installer->getTable('eav_entity_attribute')), 'eea.attribute_group_id = eag.attribute_group_id', array())
    ->where('eag.attribute_set_id = :asid')
    ->group(array('eag.attribute_group_id', 'eag.attribute_group_name'))
    ->order('if(eag.default_id = 1, 0, eag.sort_order) asc')
    ->limit(1);
$attGroupId = $conn->fetchOne($select, array('asid' => $attSetId));

$installer->addAttribute($entityTypeId, 'hawk_landing_page', array(
	'type' => 'int',
	'input' => 'select',
	'label' => 'HawkSearch Landing Page',
	'source' => 'eav/entity_attribute_source_boolean',
	'user_defined' => false,
	'default' => 0
));


$installer->addAttributeToGroup(
	$entityTypeId,
	$attSetId,
	$attGroupId,
	'hawk_landing_page',
	'30'
);

$installer->endSetup();

Mage::log('done installing the proxy module');