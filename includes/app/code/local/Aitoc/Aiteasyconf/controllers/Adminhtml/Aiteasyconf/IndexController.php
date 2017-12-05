<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Adminhtml_Aiteasyconf_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    /* saving process */

    public function cleartempAction()
    {
        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');

        try {
            Mage::getModel('aiteasyconf/temporary')->clear($mainId);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        if (!isset($response['error'])) {
            $response['success'] = true;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    public function storetempAction()
    {
        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');

        $products = $this->getRequest()->getParam('products');
        $count = 0;

        if (!empty($products)) {
            try {
                foreach ($products as $id => $product) {
                    $attributes = $product['attributes'];
                    unset($product['attributes']);

                    $isChanged = $product['is_changed'];
                    unset($product['is_changed']);

                    $temp = Mage::getModel('aiteasyconf/temporary');

                    $temp->setData('configurable_id', $mainId);
                    $temp->setData('product_id', $id);
                    $temp->setData('is_changed', $isChanged);
                    $temp->setData('parameters', serialize($product));
                    $temp->setData('attributes', serialize($attributes));

                    $temp->save();
                    $count++;
                }
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
        }

        if (!isset($response['error'])) {
            $response['success']['count'] = $count;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    private function parseProduct($product)
    {
        $data = unserialize($product->getData('parameters'));
        $data['id'] = $product->getData('product_id');
        $data['is_changed'] = $product->getData('is_changed');
        $attributes = unserialize($product->getData('attributes'));
        foreach ($attributes as $attrId => $attrValue) {
            $data[$attrId] = $attrValue;
        }

        return $data;
    }

    public function validateAction()
    {
        // it is not the real product validation
        // it just checks that all fields are not empty or null
        // then checks for duplicates

        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');

        $collection = Mage::getModel('aiteasyconf/temporary')->getTempData($mainId);
        $invalidIds = array();

        foreach ($collection as $_product) {
            $data = $this->parseProduct($_product);
            if (1 == $data['is_changed']) {
                if (false == $this->validateProduct($data)) {
                    $invalidIds[] = $data['id'];
                }
            }
        }

        if (!empty($invalidIds)) {
            $response['success']['status'] = 'invalid';
            $response['success']['count'] = count($invalidIds);
            $response['success']['ids'] = Mage::helper('core')->jsonEncode($invalidIds);

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        $duplicatesCount = Mage::getModel('aiteasyconf/temporary')->getDuplicatesCount($mainId);

        if ($duplicatesCount > 0) {
            $duplicatesIds = Mage::getModel('aiteasyconf/temporary')->getDuplicatesIds($mainId);

            $response['success']['status'] = 'duplicates';
            $response['success']['count'] = $duplicatesCount;
            $response['success']['ids'] = Mage::helper('core')->jsonEncode($duplicatesIds);

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        $response['success']['status'] = 'allok';

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    private function validateProduct($data)
    {
        unset($data['id']);
        unset($data['is_changed']);

        if (true == $data['autogenerate_name']) unset($data['name']);
        if (true == $data['autogenerate_sku']) unset($data['sku']);

        foreach ($data as $k => $v) {
            if (('' == $v) || 'undefined' == $v) return false;
        }

        return true;
    }

    public function removeduplicatesAction()
    {
        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');

        try {
            $count = Mage::getModel('aiteasyconf/temporary')->removeDuplicates($mainId);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        if (!isset($response['error'])) {
            $response['success']['count'] = $count;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    public function deleteAction()
    {
        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');

        $mainProduct = Mage::getModel('catalog/product')->load($mainId);

        $temp = Mage::getModel('aiteasyconf/temporary');

        $usedProductIds = $mainProduct->getTypeInstance(true)->getUsedProductIds($mainProduct);
        $tempProductIds = $temp->getTempIds($mainId);

        $deleteProductIds = array_diff($usedProductIds, $tempProductIds);
        $count = 0;

        try {
            foreach ($deleteProductIds as $id) {
                $product = Mage::getSingleton('catalog/product')->load($id);
                Mage::dispatchEvent('catalog_controller_product_delete', array('product' => $product));
                $product->delete();
                $count++;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        if (!isset($response['error'])) {
            $response['success']['count'] = $count;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    public function saveAction()
    {
        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');

        $temp = Mage::getModel('aiteasyconf/temporary');
        $batch = $temp->getBatch($mainId);
        $count = 0;

        if ($batch->getSize()) {
            $mainProduct = Mage::getModel('catalog/product')->load($mainId);

            try {
                foreach ($batch as $_product) {
                    $data = $this->parseProduct($_product);
                    if (1 == $data['is_changed']) {
                        $this->saveProduct($mainProduct, $data);
                    }

                    $temp->load($_product->getData('id'))->delete();
                    $count++;
                }
            } catch (Exception $e) {
                $errorText = Mage::helper('aiteasyconf')->__('Could not save product.') . '\n';
                $errorText .= Mage::helper('catalog')->__('Name') . ': ' . $data['name'] . '\n';
                $errorText .= Mage::helper('catalog')->__('SKU') . ': ' . $data['sku'] . '\n';
                $errorText .= Mage::helper('aiteasyconf')->__('Error') . ': ' . $e->getMessage();

                $response['error'] = $errorText;
            }

            if (!isset($response['error'])) {
                $response['success']['status'] = 'savedBatch';
                $response['success']['count'] = $count;
            }
        } else {
            $response['success']['status'] = 'savedAll';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    private function saveProduct($mainProduct, $data)
    {
        if (0 == (int)$data['id']) {
            $product = $this->_createNewSimpleProduct($mainProduct);
        } else {
            $product = Mage::getModel('catalog/product')->load($data['id']);
        }

        $product = $this->_setProductData($product, $mainProduct, $data);

        $product->validate();
        $product->save();

        // associate new simple product with the configurable
        if (0 == (int)$data['id']) {
            $mainProduct->getResource()
                ->linkSimpleToConfigurable($mainProduct->getId(), $product->getId());
        }

        return true;
    }

    protected function _getStockArray($data)
    {
        if (empty($data)) {
            return array();
        }

        $stockArray = array(
            'qty' => $data['qty'],
            'is_in_stock' => $data['stock']
        );

        if (0 == (int)$data['id']) {
            $stockArray['use_config_min_qty'] = 1;
            $stockArray['use_config_min_sale_qty'] = 1;
            $stockArray['use_config_max_sale_qty'] = 1;
            $stockArray['use_config_backorders'] = 1;
            $stockArray['use_config_notify_stock_qty'] = 1;
            $stockArray['is_qty_decimal'] = 0;
            $stockArray['use_config_enable_qty_increments'] = 1;
            $stockArray['use_config_qty_increments'] = 1;
            $stockArray['use_config_manage_stock'] = 1;
            $stockArray['original_inventory_qty'] = 0;

        }
        //setting for simple products from Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple::_prepareForm()
        return $stockArray;

    }

    protected function _getAutogenerateString($mainProduct, $data)
    {
        if (empty($data)) {
            return '';
        }

        if ($data['autogenerate_name'] || $data['autogenerate_sku']) {
            $autogeneratedString = '';

            foreach ($mainProduct->getTypeInstance(true)->getUsedProductAttributes($mainProduct) as $attribute) {
                foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
                    if ($option['value'] == $data[$attribute->getId()]) {
                        $autogeneratedString .= '-' . $option['label'];
                    }
                }
            }

            return $autogeneratedString;
        }

        return '';
    }

    protected function _createNewSimpleProduct($mainProduct)
    {
        $product = Mage::getModel('catalog/product')
            ->setStoreId(0)
            ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->setAttributeSetId($mainProduct->getAttributeSetId());

        $product->setWebsiteIds($mainProduct->getWebsiteIds());

        $this->_copyAttribute($product, $mainProduct);

        return $product;
    }

    protected function _copyAttribute($newProduct, $oldProduct)
    {
        foreach ($newProduct->getTypeInstance()->getEditableAttributes() as $attribute) {
            if ($attribute->getIsUnique()
                || $attribute->getAttributeCode() == 'url_key'
                || $attribute->getFrontend()->getInputType() == 'gallery'
                || $attribute->getFrontend()->getInputType() == 'media_image'
                || !$attribute->getIsVisible()
            ) {
                continue;
            }

            $newProduct->setData(
                $attribute->getAttributeCode(),
                $oldProduct->getData($attribute->getAttributeCode())
            );
        }
        return $newProduct;
    }

    protected function _setProductData($product, $mainProduct, $data)
    {
        $product->setData('name', $data['name']);
        $product->setData('sku', $data['sku']);
        $product->setData('status', $data['status']);
        $product->setData('visibility', $data['visibility']);
        $product->setData('weight', $data['weight']);
        $product->setData('price', $data['price']);

        $stockData = $this->_getStockArray($data);
        $product->setData('stock_data', $stockData);

        foreach ($mainProduct->getTypeInstance(true)->getUsedProductAttributes($mainProduct) as $attribute) {
            $product->setData($attribute->getAttributeCode(), $data[$attribute->getId()]);
        }

        $autogeneratedString = $this->_getAutogenerateString($mainProduct, $data);

        if ($data['autogenerate_name']) {
            $product->setName($mainProduct->getName() . $autogeneratedString);
        }
        if ($data['autogenerate_sku']) {
            $sku = Mage::helper('aiteasyconf')->getAutoconfiguratedSku($mainProduct->getSku() . $autogeneratedString);
            $product->setSku($sku);
        }

        return $product;
    }

    /* generation */

    public function generateAction()
    {
        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');

        // prepare common data
        $options = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('options'));
        $common = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('common'));

        $stockItem = array(
            'is_in_stock' => $common['stock'],
            'qty' => $common['qty']
        );

        $stock = new Varien_Object($stockItem);
        unset($common['is_in_stock']);
        unset($common['qty']);

        $blank = new Varien_Object($common);
        $blank->setStockItem($stock);

        $blank->setAutogenerateName(true);
        $blank->setAutogenerateSku(true);
        $blank->setIsChanged(true);

        $attributes = $this->getAttributes($mainId);

        // get a simple (id => name) array to convert ids to codes
        // because generator only knows ids
        // and template gets value by code
        // $this->getProduct()->getData($attribute->getAttributeCode())
        $convert = $this->getConvertArray($attributes);

        // to avoid duplicate ids if the user generates more than once
        $uniqueKey = 'g' . substr((string)time(), -3);

        // generate combinations array
        $combinations = $this->generateCombinations($options);

        $html = '';

        try {
            foreach ($combinations as $k => $combination) {
                $product = $blank;
                foreach ($combination as $attributeId => $attributeValue) {
                    $product->setData($convert[$attributeId], $attributeValue);
                }

                $id = $uniqueKey . $k;
                $html .= $this->getProductHtml($product, $id, $attributes);
            }

            if ('' == $html) {
                $response['error'] = Mage::helper('aiteasyconf')->__('Failed to generate products html.');
            } else {
                $response['success']['html'] = $html;
                $response['success']['count'] = count($combinations);
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    private function getProductHtml($product, $id, $attributes)
    {
        $productHtml =
            $this->getLayout()
                ->createBlock('aiteasyconf/SimpleProduct')
                ->setTemplate('aiteasyconf/simpleproduct.phtml')
                ->setProduct($product)
                ->setId($id)
                ->setAttributes($attributes)
                ->toHtml();

        $productHtml = '<td id="product-column-' . $id . '">' . $productHtml . '</td>';
        $productHtml = Mage::helper('aiteasyconf')->compresshtml($productHtml);

        return $productHtml;
    }

    private function generateCombinations($options)
    {
        $count = 1;
        $indexes = array();

        foreach ($options as $optionId => $optionValues) {
            $count *= count($optionValues);
            $indexes[$optionId] = 0;
        }

        $combinations = array();

        for ($i = 0; $i < $count; $i++) {
            $combination = array();
            foreach ($options as $optionId => $optionValues) {
                $combination[$optionId] = $optionValues[$indexes[$optionId]];
            }

            $combinations[] = $combination;
            $indexes = $this->incrementIndex($indexes, $options);
        }

        return $combinations;
    }

    private function incrementIndex($indexes, $options)
    {
        foreach ($options as $optionId => $optionValues) {
            if (($indexes[$optionId] + 1) == count($optionValues)) {
                $indexes[$optionId] = 0;
            } else {
                $indexes[$optionId] += 1;
                return $indexes;
            }
        }
    }

    private function getAttributes($mainId)
    {
        $mainProduct = Mage::getModel('catalog/product')->load($mainId);

        $attributes = array();
        foreach ($mainProduct->getTypeInstance()
                     ->getUsedProductAttributes($mainProduct) as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        return $attributes;
    }

    private function getConvertArray($attributes)
    {
        $convert = array();
        foreach ($attributes as $attribute) {
            $convert[$attribute->getId()] = $attribute->getAttributeCode();
        }

        return $convert;
    }

    /* temporary data restoration */

    public function restoreAction()
    {
        $response = array();
        $mainId = $this->getRequest()->getParam('mainId');
        $attributes = $this->getAttributes($mainId);
        $collection = Mage::getModel('aiteasyconf/temporary')->getTempData($mainId);

        $existingIds = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('existingIds'));

        $html = '';
        $counter = 0;

        foreach ($collection as $item) {
            $data = $this->parseProduct($item);

            if (in_array($data['id'], $existingIds)) {
                continue;
            }

            $stockItem = array(
                'is_in_stock' => $data['stock'],
                'qty' => $data['qty']
            );

            $stock = new Varien_Object($stockItem);
            unset($data['is_in_stock']);
            unset($data['qty']);

            $id = $data['id'];
            unset($data['id']);

            $product = new Varien_Object($data);
            $product->setStockItem($stock);

            foreach ($attributes as $attribute) {
                $product->setData($attribute->getAttributeCode(), $product->getData($attribute->getId()));
                $product->unsetData($attribute->getId());
            }

            $html .= $this->getProductHtml($product, $id, $attributes);
            $counter++;
        }

        if ('' == $html) {
            $response['error'] = Mage::helper('aiteasyconf')->__('Failed to generate products html.');
        } else {
            $response['success']['html'] = $html;
            $response['success']['count'] = $counter;

            Mage::getModel('aiteasyconf/temporary')->clear($mainId);
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }


    public function ajaxAction()
    {

        $this->_initProduct();

        $this->loadLayout();
        $block = $this->getLayout()->addBlock('aiteasyconf/easyconf', 'root');

        $this->renderLayout();
    }

    /**
     * Copy from Mage_Adminhtml_Catalog_ProductController
     * Initialize product from request parameters
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Manage Products'));

        $productId = (int)$this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if (!$productId) {
            if ($setId = (int)$this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
        }

        $product->setData('_edit_mode', true);
        if ($productId) {
            try {
                $product->load($productId);
            } catch (Exception $e) {
                $product->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE);
                Mage::logException($e);
            }
        }

        $attributes = $this->getRequest()->getParam('attributes');
        if ($attributes && $product->isConfigurable() &&
            (!$productId || !$product->getTypeInstance()->getUsedProductAttributeIds())
        ) {
            $product->getTypeInstance()->setUsedProductAttributeIds(
                explode(",", base64_decode(urldecode($attributes)))
            );
        }

        // Required attributes of simple product for configurable creation
        if ($this->getRequest()->getParam('popup')
            && $requiredAttributes = $this->getRequest()->getParam('required')
        ) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($this->getRequest()->getParam('popup')
            && $this->getRequest()->getParam('product')
            && !is_array($this->getRequest()->getParam('product'))
            && $this->getRequest()->getParam('id', false) === false
        ) {

            $configProduct = Mage::getModel('catalog/product')
                ->setStoreId(0)
                ->load($this->getRequest()->getParam('product'))
                ->setTypeId($this->getRequest()->getParam('type'));

            /* @var $configProduct Mage_Catalog_Model_Product */
            $data = array();
            foreach ($configProduct->getTypeInstance()->getEditableAttributes() as $attribute) {

                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if (!$attribute->getIsUnique()
                    && $attribute->getFrontend()->getInputType() != 'gallery'
                    && $attribute->getAttributeCode() != 'required_options'
                    && $attribute->getAttributeCode() != 'has_options'
                    && $attribute->getAttributeCode() != $configProduct->getIdFieldName()
                ) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }

            $product->addData($data)
                ->setWebsiteIds($configProduct->getWebsiteIds());
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $product;
    }
}
