<?php
/**
 * Bread upgrade script
 *
 * @author Bread   copyright   2017
 */
$installer = $this;
$installer->startSetup();

$status = Mage::getModel('sales/order_status')->load('bread_split_auth_failed');

if (!$status->getId()) {
    $statusTable = $installer->getTable('sales/order_status');
    $statusStateTable = $installer->getTable('sales/order_status_state');

    $installer->getConnection()->insertArray(
        $statusTable,
        array(
            'status',
            'label'
        ),
        array(
            array('status' => 'bread_split_auth_failed', 'label' => 'Bread split-pay authorization failed'),
        )
    );

    $installer->getConnection()->insertArray(
        $statusStateTable,
        array(
            'status',
            'state',
            'is_default'
        ),
        array(
            array(
                'status' => 'bread_split_auth_failed',
                'state' => 'processing',
                'is_default' => 0
            )
        )
    );
}

$installer->endSetup();
