<?php

class Bread_BreadCheckout_Model_System_Config_Source_CategoryTree
{
    /**
     * Get categories for current config scope
     *
     * @return array
     */
    public function getCategoriesTreeView()
    {
        $storeId = Mage::helper('breadcheckout')->getConfigStoreScope();
        $rootCatId  = Mage::app()->getStore($storeId)->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCollection();
        if($storeId)
            $categories->addFieldToFilter('path', array('like'=> "1/$rootCatId/%"));
        $categories->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq'=>'1'))
            ->load()
            ->toArray();

        $categoryList = array();
        foreach ($categories as $catId => $category) {
            if (isset($category['name'])) {
                $categoryList[] = array(
                    'label' => $category['name'],
                    'level'  =>$category['level'],
                    'value' => $catId
                );
            }
        }

        return $categoryList;
    }

    /**
     * Get categories as option array
     *
     * @return array
     */
    public function toOptionArray()
    {

        $options = array();

        $options[] = array(
            'label' => Mage::helper('breadcheckout')->__('-- None --'),
            'value' => ''
        );


        $categoriesTreeView = $this->getCategoriesTreeView();

        foreach ($categoriesTreeView as $value) {
            $catName    = $value['label'];
            $catId      = $value['value'];
            $catLevel    = $value['level'];

            $hyphen = '-';
            for ($i=1; $i<$catLevel; $i++) {
                $hyphen = $hyphen ."-";
            }

            $catName = $hyphen .$catName;

            $options[] = array(
                'label' => $catName,
                'value' => $catId
            );
        }

        return $options;

    }
}
