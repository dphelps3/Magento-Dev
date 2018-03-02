<?php

class CSH_Cshgroupedproduct_Block_Adminhtml_Catalog_Product_Tab
extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
implements Mage_Adminhtml_Block_Widget_Tab_Interface {

  /**
  * Set the template for the block
  *
  */
  public function _construct() {

    parent::_construct();

    $this->setTemplate('cshgroupedproduct/catalog/product/tab.phtml');
  }

  /**
  * Retrieve the label used for the tab relating to this block
  *
  * @return string
  */
  public function getTabLabel() {

    return $this->__('Associated Categories');
  }

  /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle() {

        return $this->__('Associated Categories');
    }

    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab() {

  		$this->setTemplate('cshgroupedproduct/catalog/product/tab.phtml');
  		$product = Mage::registry('product');
  		$type = $product->getTypeId();
          return ($type == '1cshgrouped');
    }

    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden() {

        return false;
    }

    public function getIdsString() {

		    return implode(',', $this->getCategoryIds());
    }

	   protected function getCategoryIds() {

        return explode(',', $this->getProduct()->getAssociatedCategories());
    }

}
