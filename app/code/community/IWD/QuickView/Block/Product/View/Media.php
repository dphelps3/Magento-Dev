<?php
if(
    file_exists(Mage::getBaseDir('code') . '/community/IWD/Productvideo/Block/Frontend/Media.php') &&
    class_exists('IWD_Productvideo_Block_Frontend_Media') &&
   Mage::getConfig()->getModuleConfig('IWD_Productvideo')->is('active', 'true')) {
    class IWD_QuickView_Block_Product_View_MediaBaseOvv extends IWD_Productvideo_Block_Frontend_Media {  }
} else {
    class IWD_QuickView_Block_Product_View_MediaBaseOvv extends Mage_Catalog_Block_Product_View_Media {  }
}

class IWD_QuickView_Block_Product_View_Media extends IWD_QuickView_Block_Product_View_MediaBaseOvv
{
    const MEDIA_IMAGE_TYPE_BASE = 'base_image';
    const MEDIA_IMAGE_TYPE_SMALL = 'small_image';
    const XML_NODE_PRODUCT_BASE_IMAGE_WIDTH = 'catalog/product_image/base_width';
    const XML_NODE_PRODUCT_SMALL_IMAGE_WIDTH = 'catalog/product_image/small_width';

    public function getImageType() {
        $type = IWD_QuickView_Block_Product_View_Media::MEDIA_IMAGE_TYPE_BASE;
        return $type;
    }

    public function getProduct(){
        return $product = Mage::registry ('current_product');
    }

    public function getProducts() {
        $product = Mage::registry('product');
        if (!$product) {
            return array();
        }
        return array($product);
    }

    public function getProductImageFallbacks($keepFrame = null) {
        $fallbacks = array();

        $products = $this->getProducts();

        if ($keepFrame === null) {
            $listBlock = $this->getLayout()->getBlock('product_list');
            if ($listBlock && $listBlock->getMode() == 'grid') {
                $keepFrame = true;
            } else {
                $keepFrame = false;
            }
        }

        /* @var $product Mage_Catalog_Model_Product */
        foreach ($products as $product) {
            $imageFallback = $this->getConfigurableImagesFallbackArray($product, $this->_getImageSizes(), $keepFrame);

            $fallbacks[$product->getId()] = array(
                'product' => $product,
                'image_fallback' => $this->_getJsImageFallbackString($imageFallback)
            );
        }

        return $fallbacks;
    }

    protected function _getImageSizes() {
        return array('image');
    }

    protected function _getJsImageFallbackString(array $imageFallback) {
        /* @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        return $coreHelper->jsonEncode($imageFallback);
    }

    public function getConfigurableImagesFallbackArray(Mage_Catalog_Model_Product $product, array $imageTypes, $keepFrame = false)
    {
        if (!$product->hasConfigurableImagesFallbackArray()) {
            $mapping = $product->getChildAttributeLabelMapping();

            $mediaGallery = $product->getMediaGallery();

            if (!isset($mediaGallery['images'])) {
                return array(); //nothing to do here
            }

            // ensure we only attempt to process valid image types we know about
            $imageTypes = array_intersect(array('image', 'small_image'), $imageTypes);

            $imagesByLabel = array();
            $imageHaystack = array_map(function ($value) {
                return Mage_ConfigurableSwatches_Helper_Data::normalizeKey($value['label']);
            }, $mediaGallery['images']);

            // load images from the configurable product for swapping
            if($mapping) {
                foreach ($mapping as $map) {
                    $imagePath = null;

                    //search by store-specific label and then default label if nothing is found
                    $imageKey = array_search($map['label'], $imageHaystack);
                    if ($imageKey === false) {
                        $imageKey = array_search($map['default_label'], $imageHaystack);
                    }

                    //assign proper image file if found
                    if ($imageKey !== false) {
                        $imagePath = $mediaGallery['images'][$imageKey]['file'];
                    }

                    $imagesByLabel[$map['label']] = array(
                        'configurable_product' => array(
                            Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_SMALL => null,
                            Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_BASE => null,
                        ),
                        'products' => $map['product_ids'],
                    );

                    if ($imagePath) {
                        $imagesByLabel[$map['label']]['configurable_product']
                        [Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_SMALL] =
                            $this->_resizeProductImage($product, 'small_image', $keepFrame, $imagePath);

                        $imagesByLabel[$map['label']]['configurable_product']
                        [Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_BASE] =
                            $this->_resizeProductImage($product, 'image', $keepFrame, $imagePath);
                    }
                }
            }


            $imagesByType = array(
                'image' => array(),
                'small_image' => array(),
            );

            // iterate image types to build image array, normally one type is passed in at a time, but could be two
            foreach ($imageTypes as $imageType) {
                // load image from the configurable product's children for swapping
                /* @var $childProduct Mage_Catalog_Model_Product */
                if ($product->hasChildrenProducts()) {
                    foreach ($product->getChildrenProducts() as $childProduct) {
                        if ($image = $this->_resizeProductImage($childProduct, $imageType, $keepFrame)) {
                            $imagesByType[$imageType][$childProduct->getId()] = $image;
                        }
                    }
                }

                // load image from configurable product for swapping fallback
                if ($image = $this->_resizeProductImage($product, $imageType, $keepFrame, null, true)) {
                    $imagesByType[$imageType][$product->getId()] = $image;
                }
            }

            $array = array(
                'option_labels' => $imagesByLabel,
                Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_SMALL => $imagesByType['small_image'],
                Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_BASE => $imagesByType['image'],
            );

            $product->setConfigurableImagesFallbackArray($array);
        }

        return $product->getConfigurableImagesFallbackArray();
    }


    protected function _resizeProductImage($product, $type, $keepFrame, $image = null, $placeholder = false)
    {
        $hasTypeData = $product->hasData($type) && $product->getData($type) != 'no_selection';
        if ($image == 'no_selection') {
            $image = null;
        }
        if ($hasTypeData || $placeholder || $image) {
            try{
                $helper = Mage::helper('catalog/image')
                    ->init($product, $type, $image)
                    ->keepFrame(($hasTypeData || $image) ? $keepFrame : false);  // don't keep frame if placeholder

                $size = Mage::getStoreConfig(self::XML_NODE_PRODUCT_BASE_IMAGE_WIDTH);
                if ($type == 'small_image') {
                    $size = Mage::getStoreConfig(self::XML_NODE_PRODUCT_SMALL_IMAGE_WIDTH);
                }
                if (is_numeric($size)) {
                    $helper->constrainOnly(true)->resize($size);
                }
                return (string)$helper;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function getGalleryImageUrl($image)
    {
        if ($image) {
            try{
                $helper = $this->helper('catalog/image')
                    ->init($this->getProduct(), 'image', $image->getFile())
                    ->keepFrame(false);

                $size = Mage::getStoreConfig(self::XML_NODE_PRODUCT_BASE_IMAGE_WIDTH);
                if (is_numeric($size)) {
                    $helper->constrainOnly(true)->resize($size);
                }
                return (string)$helper;
            }catch(Exception $e){
                return null;
            }
        }
        return null;
    }

    public function isGalleryImageVisible($image)
    {
        if(Mage::helper('iwd_quickview')->isStandardColorSwatch()){
            if (($filterClass = $this->getGalleryFilterHelper()) && ($filterMethod = $this->getGalleryFilterMethod())) {
                return Mage::helper($filterClass)->$filterMethod($this->getProduct(), $image);
            }
        }
        return true;
    }

    protected function getGalleryFilterHelper(){
        return Mage::helper('configurableswatches/productimg');
    }

    protected function setGalleryFilterMethod(){
        return 'filterImageInGallery';
    }
}
