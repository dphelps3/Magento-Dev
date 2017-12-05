<?php

class Simplesolutions_Hardwareproduct_Block_Adminhtml_Catalog_Product_Tab
extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    /**
     * Set the template for the block
     *
     */
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('hardwareproduct/catalog/product/tab.phtml');
		//Mage::Log('getTemplate ' . $this->getTemplate());
		//Mage::Log('getCategoryIds ' . $this->getCategoryIds());
		//Mage::Log('getCategoryIds ' . $this->getProduct()->getCategoryIds());
		//Mage::Log('getAssociatedCategories ' . $this->getProduct()->getAssociatedCategories());
		//Mage::Log('getIdsString ' . $this->getIdsString());
    }

    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Associated Categories');
    }

    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Associated Categories');
    }

    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
		$this->setTemplate('hardwareproduct/catalog/product/tab.phtml');
		$product = Mage::registry('product');
		$type = $product->getTypeId();
        return ($type == '1hardware');
    }

    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    public function getIdsString()
    {
		return implode(',', $this->getCategoryIds());
    }

	protected function getCategoryIds()
    {
        return explode(',', $this->getProduct()->getAssociatedCategories());
    }

    /**
     * AJAX TAB's
     * If you want to use an AJAX tab, uncomment the following functions
     * Please note that you will need to setup a controller to recieve
     * the tab content request
     *
     */
    /**
     * Retrieve the class name of the tab
     * Return 'ajax' here if you want the tab to be loaded via Ajax
     *
     * return string
     */
#   public function getTabClass()
#   {
#       return 'my-custom-tab';
#   }

    /**
     * Determine whether to generate content on load or via AJAX
     * If true, the tab's content won't be loaded until the tab is clicked
     * You will need to setup a controller to handle the tab request
     *
     * @return bool
     */
#   public function getSkipGenerateContent()
#   {
#       return false;
#   }

    /**
     * Retrieve the URL used to load the tab content
     * Return the URL here used to load the content by Ajax
     * see self::getSkipGenerateContent & self::getTabClass
     *
     * @return string
     */
#   public function getTabUrl()
#   {
#       return null;
#   }

}
