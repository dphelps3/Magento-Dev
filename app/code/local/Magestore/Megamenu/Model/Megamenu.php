<?php

class Magestore_Megamenu_Model_Megamenu extends Mage_Core_Model_Abstract
{
    const TOP_MENU = 0;
    const LEFT_MENU = 1;

    const NO_RESPONSIVE = 0;
    const RESPONSIVE = 1;

    const MOBILE_SLIDE = 0;
    const MOBILE_BLIND = 1;

    const CONTENT_ONLY = 1;
    const PRODUCT_LISTING = 2;
    const CATEGORY_LISTING = 3;
    const CONTACT_FORM = 4;
    const GROUP_CATEGORY_LISTING = 5;
    const ANCHOR_TEXT = 6;
    const PRODUCT_GRID =7 ;
    const CATEGORY_LEVEL = 8;
    const CATEGORY_DYNAMIC = 9;
    const PRODUCT_BY_CATEGORY_FILTER = 10;

    const FEATURED_NONE = 0;
    const FEATURED_PRODUCTS = 1;
    const FEATURED_CATEGORIES = 2;
    const FEATURED_CONTENT = 3;

    protected $_eventPrefix = 'megamenu_item';
    protected $_eventObject = 'megamenu_item';
    
    protected $_parentCategories;
    protected $_categoryCollection;


    protected $_html;


    public function _construct(){
		parent::_construct();
		$this->_init('megamenu/megamenu');
	}
    
    /**
     * get menu html from database
     * @return string
     */

    public function getFeaturedProductIds(){
        $productIds = array();
        if($this->getId()){
            $productIds = explode(',', $this->getFeaturedProducts());
        }
        return $productIds;
    }
    
    public function getTemplateFilename(){
        $filename = '';
        if($this->getId()){
            $menu_type = $this->getMenuType();
            if($menu_type == self::CONTENT_ONLY){
                $filename = 'content_only/default.phtml';
            }elseif($menu_type == self::PRODUCT_LISTING){
                $filename = 'product_listing/general_products.phtml';
            }elseif($menu_type == self::CATEGORY_LISTING){
                $filename = 'category_listing/categories_static.phtml';
            }elseif($menu_type == self::CONTACT_FORM){
                $filename = 'contact_form/default.phtml';
            }elseif ($menu_type == self::GROUP_CATEGORY_LISTING) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::ANCHOR_TEXT) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::PRODUCT_GRID) {
                $filename = 'product_listing/detailed_products.phtml';
            }elseif ($menu_type == self::CATEGORY_LEVEL) {
                $filename = 'category_listing/categories_level.phtml';
            }elseif ($menu_type == self::CATEGORY_DYNAMIC) {
                $filename = 'category_listing/categories_dynamic.phtml';
            }elseif ($menu_type == self::PRODUCT_BY_CATEGORY_FILTER) {
                $filename = 'product_listing/products_by_category_filter.phtml';
            }
            
        }
        return $filename;
    }
    public function getTemplateFilenameforMobile(){
        $filename = '';
        if($this->getId()){
            $menu_type = $this->getMenuType();
            if($menu_type == self::CONTENT_ONLY){
                $filename = 'content_only/default.phtml';
            }elseif($menu_type == self::PRODUCT_LISTING){
                $filename = 'product_listing/general_products.phtml';
            }elseif($menu_type == self::CATEGORY_LISTING){
                $filename = 'category_listing/m_categories.phtml';
            }elseif($menu_type == self::CONTACT_FORM){
                $filename = 'contact_form/mobile_default.phtml';
            }elseif ($menu_type == self::GROUP_CATEGORY_LISTING) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::ANCHOR_TEXT) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::PRODUCT_GRID) {
                $filename = 'product_listing/detailed_products.phtml';
            }elseif ($menu_type == self::CATEGORY_LEVEL) {
                $filename = 'category_listing/m_categories.phtml';
            }elseif ($menu_type == self::CATEGORY_DYNAMIC) {
                $filename = 'category_listing/m_categories.phtml';
            }elseif ($menu_type == self::PRODUCT_BY_CATEGORY_FILTER) {
                $filename = 'product_listing/m_products_by_category_filter.phtml';
            }
        }
        return $filename;
    }
    public function getMenutypeOptions() {
        return array(
            array(
                'label' => 'Anchor Text',
                'value' => self::ANCHOR_TEXT
            ),
            array(
                'label' => 'Default Category Listing',
                'value' => self::CATEGORY_LEVEL
            ),
            array(
                'label' => 'Static Category Listing',
                'value' => self::CATEGORY_LISTING
            ),
            array(
                'label' => 'Dynamic Category Listing',
                'value' => self::CATEGORY_DYNAMIC
            ),
            array(
                'label' => 'Product Listing',
                'value' => self::PRODUCT_LISTING
            ),
            array(
                'label' => 'Product Grid',
                'value' => self::PRODUCT_GRID
            ),
            array(
                'label' => 'Dynamic products listing by category',
                'value' => self::PRODUCT_BY_CATEGORY_FILTER
            ),
            array(
                'label' => 'Content',
                'value' => self::CONTENT_ONLY
            ),
        );
    }
    public function getSubmenualignOptions() {
        return array(
            array(
                'label' => 'From left menu',
                'value' => 0
            ),
            array(
                'label' => 'From right menu',
                'value' => 1
            ),
            array(
                'label' => 'From left item',
                'value' => 2
            ),
            array(
                'label' => 'From right item',
                'value' => 3
            ),
        );
    }
    public function getLeftSubmenualignOptions() {
        return array(
            array(
                'label' => 'From top menu',
                'value' => 0
            ),
            array(
                'label' => 'From top item',
                'value' => 1
            ),
           
        );
    }
    public function getMegamenutypeOptions() {
        return array(
            array(
                'label' => 'Top Menu',
                'value' => self::TOP_MENU
            ),
            array(
                'label' => 'Left Menu',
                'value' => self::LEFT_MENU
            ),
           
        );
    }
    
    public function getCategoryCollection($store = null){
         if(is_null($this->_categoryCollection)){
            $data = $this->getData('menu_item');
            $catIds = array(0);
            if($this->getId()){
                $catIds = explode(', ', $this->getCategories());
            }
            
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $catIds))
                ->addFieldToFilter('is_active', 1)
				->setOrder('position','ASC');
            if(!is_null($store))
                $collection->setStore($store);
            $this->_categoryCollection = $collection;
        }
        return $this->_categoryCollection;
    }
    
    public function getParentCategories($store = null){
        if(is_null($this->_parentCategories)){
            $parentIds = array();
            $categories = $this->getCategoryCollection();
            $categoryIds = $categories->getAllIds();
            foreach($categories as $category){
                $parents = $category->getParentIds();
                if(count(array_intersect($parents, $categoryIds))== 0)
                        $parentIds[] = $category->getId();
            }
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $parentIds))
                ->addFieldToFilter('is_active', 1)
				->setOrder('position','ASC');
            if(!is_null($store))
                $collection->setStore($store);
            $this->_parentCategories = $collection;
        }
        return $this->_parentCategories;
    }
    
    public function getCategoryIds(){
        $categoryIds = array();
        if($this->getId()){
            $stringIds = $this->getCategories();
            $categoryIds = explode(', ', $stringIds);
        }
        return $categoryIds;
    }
    public function getCategorytypeOptions(){
        return array(
            array(
                'label' => 'List all items of each category in one column',
                'value' => 0
            ),
            array(
                'label' => 'Automatically arrange items of category in columns equally',
                'value' => 1
            ),  
        );
    }
    public function getCategoryImageOptions(){
        return array(
           
            array(
                'label' => 'Yes',
                'value' => 1
            ),  
             array(
                'label' => 'No',
                'value' => 0
            ),
        );
    }
    /* -- Save Static block for Item   */
    public function saveItem(){
        if($this->getMenuType()!= self::ANCHOR_TEXT){
            $currentStore = Mage::app()->getStore()->getStoreId();
            $datastores = explode(',',$this->getStores());
            $stores = Mage::app()->getStores(false);
            foreach ($stores as $id => $store) {
                Mage::app()->setCurrentStore($store->getId());
                $package = Mage::getStoreConfig('design/package/name',$store->getId());
                $template = Mage::getStoreConfig('design/theme/template',$store->getId());
                $default = Mage::getStoreConfig('design/theme/default',$store->getId());
                if($template){
                    $theme = $template;
                }elseif(!$template && $default){
                    $theme = $default;
                }else{
                    $theme = 'default';
                }
                if(in_array(0,$datastores) || in_array($store->getId(),$datastores)) {
                    Mage::getDesign()->setArea('frontend')
                        ->setPackageName($package)
                        ->setTheme($theme);
                    $html = Mage::app()->getLayout()->createBlock('megamenu/item')
                        ->setArea('frontend')
                        ->setStore($id)
                        ->setItem($this)
                        ->setTemplate('ms/megamenu/templates/item.phtml')
                        ->toHtml();
                    $data = array();
                    $staticBlock = Mage::getModel('cms/block')->load('mega_item_' .$this->getId().'_'. $id, 'identifier');
                    $data['title'] = 'Mega Item ' . $this->getNameMenu();
                    $data['identifier'] = 'mega_item_' .$this->getId().'_'. $id;
                    $data['stores'] = array($id);
                    $data['block_id'] = $staticBlock->getId();
                    $data['content'] = $html;
                    $staticBlock->setData($data)->setId($staticBlock->getId())->save();

                }else{
                    $staticBlock = Mage::getModel('cms/block')->load('mega_item_' .$this->getId().'_'. $id, 'identifier');
                    $staticBlock->delete();
                }
            }
            Mage::app()->setCurrentStore($currentStore);
        }
        return $this;
    }
    public function deleteItem(){
        $datastores = explode(',',$this->getStores());
        $stores = Mage::app()->getStores(false);
        foreach ($stores as $id => $store) {
            if(in_array(0,$datastores) || in_array($store->getId(),$datastores)) {
                $staticBlock = Mage::getModel('cms/block')->load('mega_item_' . $this->getId() . '_' . $store->getId(), 'identifier');
                $staticBlock->delete();
            }
        }
        return $this;
    }
}