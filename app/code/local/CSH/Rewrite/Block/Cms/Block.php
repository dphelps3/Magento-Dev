<?php
/**
 * Override cms/block to add cache key. This started being a problem as of EE 1.14.2 when the _construct
 * method was added which turns on caching for cms blocks
 */
class CSH_Rewrite_Block_Cms_Block extends Mage_Cms_Block_Block
{
 
    /**
     * If this block has a block id, use that as the cache key.
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if ($this->getBlockId()) {
            return array(
                Mage_Cms_Model_Block::CACHE_TAG,
                Mage::app()->getStore()->getId(),
                $this->getBlockId(),
                (int) Mage::app()->getStore()->isCurrentlySecure()
            );
        } else {
            return parent::getCacheKeyInfo();
        }
    }
}