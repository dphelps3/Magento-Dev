<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Block_Easyconf extends Mage_Adminhtml_Block_Widget
{
    private $_product = null;
    private $_usedProductsIds = null;
    private $_simpleProductsCollection = null;
    private $_attributes = null;

    // biggest product column html size
    private $_maxSize = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aiteasyconf/easyconf.phtml');
    }

    private function _getProduct()
    {
        if (null === $this->_product) {
            $this->_product = Mage::registry('product');
        }

        return $this->_product;
    }

    public function getProductId()
    {
        return $this->_getProduct()->getId();
    }

    public function getAttributes()
    {
        if (null === $this->_attributes) {
            $this->_attributes = array();
            foreach ($this->_getProduct()
                         ->getTypeInstance()
                         ->getUsedProductAttributes($this->_getProduct()) as $attribute) {
                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $this->_attributes;
    }

    public function getAttributeOptions($attribute)
    {
        return $attribute->getSource()->getAllOptions(false, true);
    }

    public function getUsedProductsIds()
    {
        if (null === $this->_usedProductsIds) {
            $this->_usedProductsIds = $this->_getProduct()
                ->getTypeInstance(true)
                ->getUsedProductIds($this->_getProduct());
        }

        return $this->_usedProductsIds;
    }

    public function getUsedProductsCount()
    {
        return (string)count($this->getUsedProductsIds());
    }

    public function getControllerUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/aiteasyconf_index/xxxxx');
    }

    public function getSimpleProductHtml($id = null)
    {
        $html = $this->getLayout()
            ->createBlock('aiteasyconf/SimpleProduct')
            ->setTemplate(($id) ? 'aiteasyconf/simpleproduct.phtml' : 'aiteasyconf/simpleproductblank.phtml')
            ->setId($id)
            ->setAttributes($this->getAttributes())
            ->toHtml();

        $html = Mage::helper('aiteasyconf')->compresshtml($html);

        $this->updateMaxSize(strlen($html));

        return $html;
    }

    public function getRestoreHtml()
    {
        return $this->getLayout()
            ->createBlock('aiteasyconf/Restore')
            ->setId($this->getProductId())
            ->toHtml();
    }

    public function _getHelper()
    {
        return Mage::helper('aiteasyconf');
    }

    private function updateMaxSize($size)
    {
        if (null === $this->_maxSize) {
            $this->_maxSize = $size;
        } else {
            $this->_maxSize = max($this->_maxSize, $size);
        }
    }

    public function getProductsLimit()
    {
        return floor($this->_getHelper()->getHtmlSizeLimit() / $this->_maxSize);
    }
}
