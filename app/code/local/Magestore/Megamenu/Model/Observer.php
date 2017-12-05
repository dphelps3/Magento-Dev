<?php

class Magestore_Megamenu_Model_Observer {

    public function prepareLayoutBefore($observer){
        $block = $observer->getEvent()->getBlock();
        if ("head" == $block->getNameInLayout()) {
            $store_id = Mage::app()->getStore()->getStoreId();
            if (Mage::getStoreConfig('megamenu/general/enable',$store_id)) {
                /* Add Css */
                $block->addCss('megamenu/css/megamenulibrary.css');
                $block->addCss('megamenu/css/megamenu.css');
                /* Add jQuery */
                if (Mage::getStoreConfig('megamenu/general/jquery', $store_id)) {
                    $file = 'ms/megamenu/jquery-1.11.2.min.js';
                    $block->addJs($file);
                }
                /* Add config Css */
                $website = Mage::app()->getWebsite()->getCode();
                $store = Mage::app()->getStore()->getCode();
                $path = 'megamenu' . DS . 'css' . DS . 'config';
                if (file_exists(Mage::getBaseDir('skin') . DS . 'frontend' . DS . 'base' . DS . 'default' . DS . $path . DS . 'custom' . DS . 'custom_' . $website . '_' . $store . '.css')) {
                    $css_file = 'megamenu/css/config/custom/custom_' . $website . '_' . $store . '.css';
                } else {
                    if (file_exists(Mage::getBaseDir('skin') . DS . 'frontend' . DS . 'base' . DS . 'default' . DS . $path . DS . 'custom' . DS . 'custom_' . $website . '_default.css')) {
                        $css_file = 'megamenu/css/config/custom/custom_' . $website . '_default.css';
                    } else {
                        $css_file = 'megamenu/css/config/default.css';
                    }
                }
                $block->addCss($css_file);
            }
        }

        return $this;
    }
    public function cms_wysiwyg_config_prepare($observer){
		if(Mage::app()->getRequest()->getModuleName() !='megamenu')
			return $this;
        $config = $observer->getEvent()->getConfig();

        if ($config->getData('add_variables')) {
            $settings = $this->getWysiwygPluginSettings($config);
            $config->addData($settings);
        }
		if ($config->getData('add_widgets')) {
            $settings = $this->getPluginSettings($config);
            $config->addData($settings);
        }
        return $this;
    }
    public function addDataSample($observer){
        // --- create sample data
        $check_sample = Mage::getStoreConfig('megamenu/general/sample_data');
        if(!$check_sample){
            $data = Mage::getModel('megamenu/megamenu')->getCollection();
            if(!$data->getSize()){
                $data = array();
                $data[]= array(
                    'name_menu' =>'Home',
                    'stores'=> 0,
                    'link' =>Mage::getBaseUrl(),
                    'sort_order'=>0,
                    'megamenu_type'=>0,
                    'status'=>1,
                    'menu_type'=>6,
                    'submenu_align'=>2,
                    'submenu_width'=>20,
                    'featured_type'=> 0,
                );
                $model = Mage::getModel('megamenu/megamenu');
                $rootcat = Mage::getModel('catalog/category')->getCollection()->getFirstItem();
                $catids = $rootcat->getAllChildren();
                $catids = explode(',',$catids);
                if(count($catids) > 0){
                    unset($catids[0]);unset($catids[1]);
                    $parentIds = array();
                    $categories = Mage::getResourceModel('catalog/category_collection')
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('entity_id', array('in' => $catids))
                        ->addFieldToFilter('is_active', 1)
                        ->setOrder('position','ASC');
                    $categoryIds = $categories->getAllIds();
                    foreach($categories as $category){
                        $parents = $category->getParentIds();
                        if(count(array_intersect($parents, $categoryIds))== 0)
                            $parentIds[] = $category->getId();
                    }
                    $i= 1;
                    foreach($parentIds as $parentId){
                        $parrent = Mage::getModel('catalog/category')->load($parentId);
                        $childIds = $parrent ->getAllChildren();
                        $childIds = explode(',',$childIds);
                        unset($childIds[0]);
                        if(count($childIds)> 0)
                            $type = Magestore_Megamenu_Model_Megamenu::CATEGORY_LEVEL;
                        else
                            $type = Magestore_Megamenu_Model_Megamenu::ANCHOR_TEXT;
                        $childIds = implode(', ',$childIds);
                        $data[]= array(
                            'name_menu' =>$parrent->getName(),
                            'stores'=> 0,
                            'link' =>$parrent->getUrl(),
                            'sort_order'=>$i,
                            'megamenu_type'=>0,
                            'status'=>1,
                            'menu_type'=>$type,
                            'categories'=>$childIds,
                            'submenu_align'=>2,
                            'submenu_width'=>20,
                            'featured_type'=> 0,
                        );
                        $i++;
                    }
                }
                for($i=0;$i< min(count($data),8);$i++){
                    $model->setData($data[$i])->save()->saveItem();
                }
                Mage::getModel('core/config')->saveConfig('megamenu/general/sample_data','1');
            }
        }
        return $this;
    }
	public function getWysiwygPluginSettings($config)
    {
        $variableConfig = array();
        $onclickParts = array(
            'search' => array('html_id'),
            'subject' => 'MagentovariablePlugin.loadChooser(\''.$this->getVariablesWysiwygActionUrl().'\', \'{{html_id}}\');'
        );
        $variableWysiwygPlugin = array(array('name' => 'magentovariable',
            'src' => Mage::getModel('core/variable_config')->getWysiwygJsPluginSrc(),
            'options' => array(
                'title' => Mage::helper('adminhtml')->__('Insert Variable...'),
                'url' => $this->getVariablesWysiwygActionUrl(),
                'onclick' => $onclickParts,
                'class'   => 'add-variable plugin'
        )));
        $configPlugins = $config->getData('plugins');
        $variableConfig['plugins'] = $variableWysiwygPlugin;
        return $variableConfig;
    }
	public function getVariablesWysiwygActionUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
    }
	public function getPluginSettings($config)
    {
        $settings = array(
            'widget_plugin_src'   => Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js',
            'widget_images_url'   => Mage::getModel('widget/widget_config')->getPlaceholderImagesBaseUrl(),
            'widget_placeholders' => Mage::getModel('widget/widget_config')->getAvailablePlaceholderFilenames(),
            'widget_window_url'   => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index')
        );

        return $settings;
    }
    public function cssGen($observer){
        $websiteCode = Mage::app()->getRequest()->getParam('website');
        $storeCode = Mage::app()->getRequest()->getParam('store');
        $path = 'megamenu'.DS.'css'.DS.'config'.DS.'custom'.DS;
        $storeId = null;
        if(isset($storeCode) && isset($websiteCode)){
            $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
            $file = 'megamenu'.DS.'css'.DS.'config'.DS.$websiteCode.DS.$storeCode.DS.'custom.css';
            $file = 'custom_'.$websiteCode.'_'.$storeCode.'.css';
        }elseif(!isset($storeCode) && isset($websiteCode)){
            $storeId = Mage::getModel('core/website')->load($websiteCode,'code')->getDefaultGroup()->getDefaultStoreId();
            $file = 'custom_'.$websiteCode.'_default.css';
        }else{
            $file = 'default.css';
            $path = 'megamenu'.DS.'css'.DS.'config'.DS;
        }
        $file = Mage::getBaseDir('skin').DS.'frontend'.DS.'base'.DS.'default'.DS.$path.$file;
        $css = Mage::app()->getLayout()->createBlock('megamenu/megamenu')->setArea('frontend')->setBilly($storeId)->setTemplate('ms/megamenu/cssgen.phtml')->toHtml();
        $gen = new Varien_Io_File();
        $gen->setAllowCreateFolders(true);
        $gen->open(array( 'path' => $path ));
        $gen->streamOpen($file, 'w+', 0777);
        $gen->streamLock(true);
        $gen->streamWrite($css);
        $gen->streamUnlock();
        $gen->streamClose();
    }
}

