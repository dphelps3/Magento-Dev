<?php
/*
 * @category    Community
 * @package     MC_Mcautoreviewreminderemail
 * @Document    system.xml
 * @Created on  April 11, 2012, 7:05 PM
 * @copyright   Copyright (c) 2012 Magento Complete
 */


$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('review')} ADD `mc_mcautoreviewemailorder_id` int(11) DEFAULT '0';");
$installer->endSetup();
