<?php

class Magestore_Megamenu_Adminhtml_MegamenuController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('megamenu/megamenu')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        return $this;
    }

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->_title($this->__('Mega Menu'))
             ->_title($this->__('Menu Manager'));
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('megamenu/megamenu')->load($id);

        $this->_title($this->__('Mega Menu'));
        if($id){
            $this->_title($this->__('Edit Menu \'%s\'',$model->getNameMenu()));
        }else{
            $this->_title($this->__('Add Menu Item'));
        }
        
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data))
                $model->setData($data);

            Mage::register('megamenu_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('megamenu/megamenu');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
            
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
		$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }  
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit'))
                    ->_addLeft($this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('megamenu')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    protected function _initItem() {
        if (!Mage::registry('megamenu_categories')) {
            if ($this->getRequest()->getParam('id')) {
                Mage::register('megamenu_categories', Mage::getModel('megamenu/megamenu')->load($this->getRequest()->getParam('id'))->getCategories());
            }
        }
    }

    public function categoriesAction() {
        $this->_initItem();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_content_maincontent_categories')->toHtml()
        );
    }

    public function categoriesJson2Action() {
        $this->_initItem();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_content_maincontent_categories')
                        ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**
     * 
     * @param type $data
     * @return type
     * Add information into data 
     */


    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
		
           // $data = $this->setDataDefault($data);
            if (isset($data['item_icon']['delete']) && $data['item_icon']['delete'] == 1) {
                $data['item_icon'] = '';
            } elseif (isset($data['item_icon']) && is_array($data['item_icon'])) {
                $data['item_icon'] = $data['item_icon']['value'];
            }
            if (isset($_FILES['item_icon']['name']) && $_FILES['item_icon']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('item_icon');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);

                    $path = Mage::getBaseDir('media') . DS . 'megamenu' . DS;

                    $result = $uploader->save($path, $_FILES['item_icon']['name']);
                    $data['item_icon'] = 'megamenu/'.$result['file'];
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            if (isset($data['position']) && is_null($data['position'])) {
                $data['position'] = null;
            }
            $model = Mage::getModel('megamenu/megamenu');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL)
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                else
                    $model->setUpdateTime(now());
                $model->setStores(implode(',', $data['stores']));
                if (isset($data['category_ids'])) {
                    $model->setCategories(implode(',', array_unique(explode(',', $data['category_ids']))));
                }
                Mage::app()->getCacheInstance()->cleanType('block_html');
                $model->save()->saveItem();
                Mage::app()->getCacheInstance()->cleanType('block_html');
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('megamenu')->__('Menu Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('megamenu')->__('Unable to find Menu to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('megamenu/megamenu');
                $model->setId($this->getRequest()->getParam('id'))
                        ->deleteItem()->delete();
                Mage::app()->getCacheInstance()->cleanType('block_html');
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Menu was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $megamenuIds = $this->getRequest()->getParam('megamenu');
        if (!is_array($megamenuIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($megamenuIds as $megamenuId) {
                    $megamenu = Mage::getModel('megamenu/megamenu')->load($megamenuId);
                    $megamenu->deleteItem()->delete();
                }
                Mage::app()->getCacheInstance()->cleanType('block_html');
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($megamenuIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $megamenuIds = $this->getRequest()->getParam('megamenu');
        if (!is_array($megamenuIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($megamenuIds as $megamenuId) {
                    $megamenu = Mage::getSingleton('megamenu/megamenu')
                            ->load($megamenuId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($megamenuIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    public function massRebuildAction() {
        $megamenuIds = $this->getRequest()->getParam('megamenu');
        if (!is_array($megamenuIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                Mage::app()->getCacheInstance()->cleanType('block_html');
                foreach ($megamenuIds as $megamenuId) {
                    $megamenu = Mage::getSingleton('megamenu/megamenu')
                        ->load($megamenuId)
                        ->saveItem();
                }
                Mage::app()->getCacheInstance()->cleanType('block_html');
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully rebuilded', count($megamenuIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    public function changeCacheAction(){
        $status  = $this->getRequest()->getParam('status');
        try {
            Mage::app()->getCacheInstance()->cleanType('config');
            if($status){
                Mage::getModel('core/config')->saveConfig('megamenu/general/cache','0');
                $label = 'disabled';
            }else{
                Mage::getModel('core/config')->saveConfig('megamenu/general/cache','1');
                $label = 'enabled';
            }
            Mage::app()->getCacheInstance()->cleanType('config');
            $this->_getSession()->addSuccess(
                $this->__('Mega Menu Cache were %s', $label)
            );
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }
    public function rebuildAllAction() {
        $collection = Mage::getModel('megamenu/megamenu')->getCollection();
        try {
            Mage::app()->getCacheInstance()->cleanType('block_html');
            foreach ($collection as $item) {
                $item->saveItem();
            }
            Mage::app()->getCacheInstance()->cleanType('block_html');
            $this->_getSession()->addSuccess(
                $this->__('Mega Menu Cache were successfully refreshed')
            );

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }
    public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('megamenu.edit.tab.content.maincontent.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null));
        $this->renderLayout();
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('megamenu.edit.tab.content.maincontent.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null));
        $this->renderLayout();
    }

    public function featuredproductAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('megamenu.edit.tab.content.featureditem.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null));
        $this->renderLayout();
    }

    public function featuredproductGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('megamenu.edit.tab.content.featureditem.product')
                ->setProducts($this->getRequest()->getPost('oproduct', null));
        $this->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'megamenu.csv';
        $content = $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'megamenu.xml';
        $content = $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_grid')->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction() {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                    $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                            ->getTreeJson($category)
            );
        }
    }

    public function chooserMainProductsAction() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
                'megamenu/adminhtml_megamenu_edit_tab_content_maincontent_grid', 'promo_widget_chooser_sku', array('input' =>'products','grid_url_call'=>'chooserMainProducts','id'=>'productGrid','js_form_object' => $request->getParam('form'),
                ));
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
    public function chooserMainProducts2Action() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
                'megamenu/adminhtml_megamenu_edit_tab_content_maincontent_grid', 'promo_widget_chooser_sku', array('input' =>'products_using_label','grid_url_call'=>'chooserMainProducts2','id'=>'productGrid2','js_form_object' => $request->getParam('form'),
                ));
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    public function chooserFeaturedProductsAction() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
                'megamenu/adminhtml_megamenu_edit_tab_content_maincontent_grid', 'promo_widget_chooser_sku', array('input' =>'featured_products','grid_url_call'=>'chooserFeaturedProducts','id'=>'productfeaturedGrid','js_form_object' => $request->getParam('form'),
                ));
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    public function chooserMainCategoriesAction() {
        $request = $this->getRequest();
        $ids = $request->getParam('selected', array());

        if (is_array($ids)) {
            foreach ($ids as $key => &$id) {
                $id = (int) $id;
                if ($id <= 0) {
                    unset($ids[$key]);
                }
            }

            $ids = array_unique($ids);
        } else {
            $ids = array();
        }

        $block = $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_content_maincontent_categories','maincontent_category', array('js_form_object' => $request->getParam('form')))
                ->setCategoryIds($ids)
        ;

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
    public function chooserCategoriesAction() {
        $request = $this->getRequest();
        $ids = $request->getParam('selected', array());
        $check = $request->getParam('check');
        if ($check == 1) {
            $ids = Mage::getResourceModel('catalog/category_collection')->getAllIds();
        } elseif ($check == 2) {
            $ids = array();
        } else {
            if (is_array($ids)) {
                foreach ($ids as $key => &$id) {
                    $id = (int) $id;
                    if ($id <= 0) {
                        unset($ids[$key]);
                    }
                }

                $ids = array_unique($ids);
            } else {
                $ids = array();
            }
        }

        $block = $this->getLayout()->createBlock(
                        'megamenu/adminhtml_megamenu_edit_tab_content_featureditem_categories','featured_categories', array('js_form_object' => $request->getParam('form'))
                )
                ->setCategoryIds($ids)
        ;

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
    /**
     * Initialize category object in registry
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory() {
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        $storeId = (int) $this->getRequest()->getParam('store');

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current' => true, 'id' => null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('megamenu/megamenu');
    }
}
