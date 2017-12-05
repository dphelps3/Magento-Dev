<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Block_SimpleProduct extends Mage_Adminhtml_Block_Template
{
    private $_product = null;
    private $_stock = null;

    public function getProduct()
    {
        if (null === $this->_product) {
            $this->_product = Mage::getModel('catalog/product')->load($this->getId());
        }
        return $this->_product;
    }

    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    public function getStock()
    {
        if (null === $this->_stock) {
            $this->_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($this->getProduct()->getId());
        }
        return $this->_stock;
    }
}