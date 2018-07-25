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
 
class Hawksearch_Datafeed_Block_System_Config_Version extends Mage_Adminhtml_Block_System_Config_Form_Field //extends Varien_Data_Form_Element_Abstract
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $config = Mage::getConfig()->getModuleConfig('Hawksearch_Datafeed');
        if($config && isset($config->version)){
            return $config->version;
        }
        return 'Unknown';
    }
}
