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
class Hawksearch_Datafeed_Model_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{

    const CRON_DATAFEED_CRON_EXPR = 'crontab/jobs/hawksearch_datafeed/schedule/cron_expr';
    const CRON_IMAGECACHE_CRON_EXPR = 'crontab/jobs/hawksearch_datafeed_imagecache/schedule/cron_expr';

    protected function _afterSave() {
        switch ($this->getPath()) {
            case "hawksearch_datafeed/feed/cron_string":
                $targetPath = self::CRON_DATAFEED_CRON_EXPR;
                break;
            case "hawksearch_datafeed/imagecache/cron_string":
                $targetPath = self::CRON_IMAGECACHE_CRON_EXPR;
                break;
        }
        Mage::getModel('core/config_data')
            ->load($targetPath, 'path')
            ->setValue(trim($this->getValue()))
            ->setPath($targetPath)
            ->save();

    }
}
