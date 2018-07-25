<?php
/**
 * Copyright (c) 2013 Hawksearch (www.hawksearch.com) - All Rights Reserved
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

class Hawksearch_Datafeed_Block_System_Config_Frontend_Generate
    extends Mage_Adminhtml_Block_System_Config_Form_Field {

    private $button_id = "hawk_generate_feed_button";

    /**
     * @return string
     */
    public function getButtonHtml() {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id' => $this->button_id,
                'label' => $this->helper('hawksearch_datafeed')->__('Generate Feeds'),
                'onclick' => 'hawkSearchFeed.generateFeed(); return false;'
            ));
		$force = '';
        if (Mage::helper('hawksearch_datafeed')->isFeedLocked()) {
			$button->setDisabled(true);
			$force = ' <input type="checkbox" value="feedforce" id="feedforce" name="feedforce" onclick="hawkSearchFeed.forceFeed();"> force';
        }

        return $button->toHtml() . $force;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $html = $this->getButtonHtml();

        if (Mage::helper('hawksearch_datafeed')->isFeedLocked()) {
            $html .= "<p id='hawksearch_display_msg' class='note' style='color:red;'>Hawksearch Feeds currently locked.</p>";
        }
        return $html;
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout() {
        $block = $this->getLayout()->createBlock("hawksearch_datafeed/system_config_frontend_generate_js");
        $block->setData("button_id", $this->button_id);

        $this->getLayout()->getBlock('js')->append($block);
        return parent::_prepareLayout();
    }
}
