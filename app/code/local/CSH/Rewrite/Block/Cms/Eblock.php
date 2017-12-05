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
 * NOTE: this file does not exist in Core Magento
 *
 * Moved file from app/code/local/Mage/Cms/Block/Eblock.php
 */
class CSH_Rewrite_Block_Cms_Eblock extends Mage_Core_Block_Template
{
    public function getEHtml()
    {
        $blockId = $this->getBlockId();
        $html = '';
        
        if ($blockId) {
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
            
            if ($block->getIsActive()) {
                /* @var $helper Mage_Cms_Helper_Data */
                $helper = Mage::helper('cms');
                $processor = $helper->getBlockTemplateProcessor();
                $html = $processor->filter($block->getContent());
            }
            
            return $html;
        }
        
        return false;
    }
    
    
    public function getETitle()
    {
        $blockId = $this->getBlockId();
        $title = Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($blockId)->getTitle();
        
        return $title;
    }
}