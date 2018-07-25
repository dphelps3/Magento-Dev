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

class Hawksearch_Datafeed_Adminhtml_HawkdatagenerateController
	extends Mage_Adminhtml_Controller_Action {

	public function validateCronStringAction() {
		$response = array('valid' => false);

		$cron = Mage::helper('hawksearch_datafeed');
		if($cron->isValidCronString($this->getRequest()->getParam('cronString'))){
			$response['valid'] = true;
		}
		$this->getResponse()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode($response));
	}

	public function runFeedGenerationAction() {
		$response = array('error' => false);
		try {
			$disabledFuncs = explode(',', ini_get('disable_functions'));
			$isShellDisabled = is_array($disabledFuncs) ? in_array('shell_exec', $disabledFuncs) : true;

			if($isShellDisabled) {
				$response['error'] = 'This installation cannot run one off feed generation because the PHP function "shell_exec" has been disabled. Please use cron.';
			} else {
				$helper = Mage::helper('hawksearch_datafeed');
				if(strtolower($this->getRequest()->getParam('force')) == 'true') {
					$helper->removeFeedLocks();
				}
				$helper->runDatafeed();
			}
		}
		catch (Exception $e) {
			Mage::logException($e);
			$response['error'] = "An unknown error occurred.";
		}
		$this->getResponse()
			->setHeader("Content-Type", "application/json")
			->setBody(json_encode($response));
	}
	/**
	 * Refreshes image cache.
	 */
	public function runImageCacheGenerationAction() {
		$response = array("error" => false);
		try {
			$disabledFuncs = explode(',', ini_get('disable_functions'));
			$isShellDisabled = is_array($disabledFuncs) ? in_array('shell_exec', $disabledFuncs) : true;
			$isShellDisabled = (stripos(PHP_OS, 'win') === false) ? $isShellDisabled : true;

			if($isShellDisabled) {
                $response['error'] = 'This installation cannot run one off feed generation because the PHP function "shell_exec" has been disabled. Please use cron.';
			} else {
				$helper = Mage::helper('hawksearch_datafeed');
				if(strtolower($this->getRequest()->getParam('force')) == 'true') {
					$helper->removeFeedLocks();
				}
				$helper->refreshImageCache();
			}
		}
		catch (Exception $e) {
			Mage::logException($e);
			$response['error'] = "An unknown error occurred.";
		}
		$this->getResponse()
			->setHeader("Content-Type", "application/json")
			->setBody(json_encode($response));
	}

    public function clearTimestampAction() {
        return $this;
    }
}

