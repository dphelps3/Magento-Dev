<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

class Magestore_Sociallogin_Block_Adminhtml_Field_Separator extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * render config row
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $id = $element->getHtmlId();
        $html = '<tr id="row_' . $id . '">'
            . '<td class="label" colspan="3">';
        $marginTop = $element->getComment() ? $element->getComment() : '0px';
        $html .= '<div style="margin-top: ' . $marginTop
            . '; font-weight: bold; border-bottom: 1px solid #dfdfdf;">';
        $html .= $element->getLabel();
        $html .= '</div></td></tr>';
        return $html;
    }
}