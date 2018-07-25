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
class Hawksearch_Datafeed_Block_System_Config_Frontend_Feed_Timestamp
	extends Mage_Adminhtml_Block_System_Config_Form_Field {

	private $button_id = 'clear_timestamp_button';

	protected function _prepareLayout() {
		$block = $this->getLayout()->createBlock('hawksearch_datafeed/system_config_frontend_clearts_js');
		$block->setData('button_id', $this->button_id);
		$this->getLayout()->getBlock('js')->append($block);
		return parent::_prepareLayout();

	}
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		$timeinfo = $this->getTimeInfo();
		$button = $this->getButton();
		if($timeinfo == false) {
			$timeinfo = '<p>Last generation time unknown, next generation will be a full export</p>';
			$button->setData('class', 'disabled');
		}
		return  $timeinfo . $button->toHtml();
	}

	private function getTimeInfo() {
		$filename = Mage::helper('hawksearch_datafeed')->getFeedFilePath() . DS . "timestamp.txt";
		if(!file_exists($filename)) {
			return false;
		}
		$lines = file($filename);
		$lastrun = new Zend_Date($lines[0]);
		$lastrun->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
		return sprintf('<p id="hawksearch_lastgen_time">Last Generation at <strong>%s</strong> contained <strong>%d</strong> records</p>', $lastrun->toString("M/d/YYYY h:mm a"), $lines[1]);
	}
	private function getButton() {
		$button = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setData(array(
				'id' => $this->button_id,
				'label' => $this->helper('hawksearch_datafeed')->__('Clear Timestamp'),
				'onclick' => 'hawkSearchTimestamp.clear(); return false;'
			));
		return $button;
	}
}
