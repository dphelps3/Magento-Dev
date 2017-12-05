<?php

class Magestore_Megamenu_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getSelectedStateSegment($currentUrl, $baseUrl) {
        $currentUrl = str_replace($baseUrl, '', $currentUrl);
        $currentUrl = str_replace('index.php/', '', $currentUrl);
        $currentUrl = $this->_hasStartingSlash($currentUrl);
        return $this->_removeDotHtml($this->_getSelectedStateSegment($currentUrl));
    }

    private function _getSelectedStateSegment($currentUrl) {
        $explodedCurrentUrl = explode('/', $currentUrl);
        return array_key_exists(0, $explodedCurrentUrl) ? $explodedCurrentUrl[0] : false;
    }
    public function returntext() {
        return 'If you enable this module, it will replace automatically your current top menu by a new mega menu. Also, you can show mega menu in other places by choosing one of the options below (recommended for developers)';
    }

    public function getUrlImage() {
        return Mage::getBaseUrl('media') . 'megamenu/image/';
    }

    public function returnlayout() {
        return 'Add Top Menu:<br/>
        &lt;block name="topmenu" type="megamenu/megamenu" template="ms/megamenu/topmenu.phtml"/&gt<br/>
        Add Left Menu:<br/>
        &lt;block name="topmenu" type="megamenu/megamenu" template="ms/megamenu/leftmenu.phtml"/&gt';
    }

    public function returnblock() {
        return 'Add Top Menu:<br/>
            &nbsp;&nbsp{{block type="megamenu/megamenu" template="ms/megamenu/topmenu.phtml"}}<br>
            Add Left Menu:<br/>
            &nbsp;&nbsp{{block type="megamenu/megamenu" template="ms/megamenu/leftmenu.phtml"}}<br>';
    }


    public function returntemplate() {
        return "Add Top Menu:<br/>
                &nbsp;\$this->getLayout()->createBlock('megamenu/megamenu')->setTemplate('ms/megamenu/topmenu.phtml')<br/>&nbsp;&nbsp;->tohtml();<br/>
                Add Left Menu:<br/>
                &nbsp;\$this->getLayout()->createBlock('megamenu/megamenu')->setTemplate('ms/megamenu/leftmenu.phtml')<br/>&nbsp;&nbsp;->tohtml();
                ";
    }

    /**
     * get menu type
     * @return menu type array
     */
    public function getMenutypeOptions() {
        return array(
            array(
                'label' => 'Anchor Text',
                'value' => Magestore_Megamenu_Model_Megamenu::ANCHOR_TEXT
            ),
            array(
                'label' => 'Default Category Listing',
                'value' => Magestore_Megamenu_Model_Megamenu::CATEGORY_LEVEL
            ),
            array(
                'label' => 'Static Category Listing',
                'value' => Magestore_Megamenu_Model_Megamenu::CATEGORY_LISTING
            ),
            array(
                'label' => 'Dynamic Category Listing',
                'value' => Magestore_Megamenu_Model_Megamenu::CATEGORY_DYNAMIC
            ),
            array(
                'label' => 'Products Listing',
                'value' => Magestore_Megamenu_Model_Megamenu::PRODUCT_LISTING
            ),
            array(
                'label' => 'Products Grid',
                'value' => Magestore_Megamenu_Model_Megamenu::PRODUCT_GRID
            ),
            array(
                'label' => 'Dynamic products listing by category',
                'value' => Magestore_Megamenu_Model_Megamenu::PRODUCT_BY_CATEGORY_FILTER
            ),
            array(
                'label' => 'Content',
                'value' => Magestore_Megamenu_Model_Megamenu::CONTENT_ONLY
            ),
        );
    }
    public function getMegamenutypeOptions() {
        return array(
            array(
                'label' => 'Top Menu',
                'value' => 0
            ),
            array(
                'label' => 'Left Menu',
                'value' => 1
            ),
           
        );
    }
    /**
     * get menu type options for grid menu item
     * @return menu type options
     */
    public function menuTypeToOptionArray() {
        $result = array();
        $array = $this->getMenutypeOptions();
        foreach ($array as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
     public function megamenuTypeToOptionArray() {
        $result = array();
        $array = $this->getMegamenutypeOptions();
        foreach ($array as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }

    /**
     * get featured type: none, product, category
     * @return array
     */
    public function getFeaturedTypes() {
        return array(
            array(
                'label' => 'None',
                'value' => '0'
            ),
            array(
                'label' => 'Product',
                'value' => '1'
            ),
            array(
                'label' => 'Category',
                'value' => '2'
            ),
            array(
                'label' => 'Content',
                'value' => '3'
            )
        );
    }

    public function positionSubAuto($align) {
        $sub_position = '';
        switch ($align) {
            case 0:
                $sub_position = 'sub_left';
                break;
            case 1:
                $sub_position = 'sub_right';
                break;
            case 2:
                $sub_position = 'sub_left position_auto';
                break;
            case 3:
                $sub_position = 'sub_right position_auto';
                break;
            default:
                break;
        }
        return $sub_position;
    }
    public function positionLeftSubAuto($align) {
        $sub_position = '';
            switch ($align) {
                case 0:
                    $sub_position = 'position_menu';
                    break;
                case 1:
                    $sub_position = 'position_item';
                    break;
                default:
                    break;
            }
            return $sub_position;
        }
    public function setLevel($level){
        switch ($level) {
                case 1:
                    $class = 'level1';
                    break;
                case 2:
                    $class = 'level2';
                    break;
                case 3:
                    $class = 'level3';
                    break;
                default:
                    break;
            }
            return $class;
    }
}
