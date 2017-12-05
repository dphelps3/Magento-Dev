<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MAX_SKU_LENGTH = 64;

    public function getAutoconfiguratedSku($sku)
    {
        if (Mage::getModel('catalog/product')->getIdBySku($sku)) {
            return $this->getAutoconfiguratedSku($this->getNextSku($sku));
        } else {
            if (strlen($sku) > self::MAX_SKU_LENGTH) {
                return $this->getAutoconfiguratedSku($this->shortenSku($sku));
            }
            return $sku;
        }
    }

    private function getNextSku($sku)
    {
        // if sku is like sku__number
        // return sku__number++
        // else return sku__1

        if (preg_match('/__(\d+)$/', $sku, $num)) {
            $next = (1 + $num[1]);
            return preg_replace('/(__\d+)$/', '__' . $next, $sku);
        } else {
            return $sku . '__1';
        }
    }

    private function shortenSku($sku)
    {
        // magento has sku length limit of 64 characters
        // so we will cut it, but keep the __number to avoid duplicates

        if (preg_match('/.*(__\d+)$/', $sku, $parts)) {
            $number = $parts[1];
        } else {
            $number = '';
        }

        $length = self::MAX_SKU_LENGTH - strlen($number);

        $sku = substr($sku, 0, $length);
        $sku .= $number;

        return $sku;
    }

    public function getBatchSize($side)
    {
        // $side is "js" or "php"
        return (int)Mage::getStoreConfig('aitsys/aiteasyconf/batchsize_' . $side);
    }

    public function getHtmlSizeLimit()
    {
        return ((int)Mage::getStoreConfig('aitsys/aiteasyconf/html_size_limit') * 1024 * 1024);
    }

    public function getUseItemsLimit()
    {
        return (int)Mage::getStoreConfig('aitsys/aiteasyconf/use_items_limit');
    }

    public function compresshtml($html)
    {
        // uncomment the following line if you need a readable html in simple products columns
        // return $html;

        $html = preg_replace('/[\r\n\t]+/', '', $html); // end of line
        $html = preg_replace('/[\s]{2,}/', ' ', $html); // 2+ spaces
        $html = preg_replace('/>[\s]+</', '><', $html); // all spaces between tags
        $html = preg_replace('/<!--.*-->/U', '', $html); // html comments

        return $html;
    }

    public function getPhrases()
    {
        // phrases said by EC_Translator.say (js/aitoc/aiteasyconf.js)

        return array(

            /* ui elements */

            'combinations' => $this->__('You are about to generate <strong>###</strong> product(s)'),

            /* alerts */

            'confirmdelete' => $this->__('Are you sure?'),
            'selectvalues' => $this->__('Please select at least one option for each attribute.'),
            'deleteduplicates' => $this->__('### duplicate(s) are detected upon the latest products configuration. Delete the duplicate(s) automatically?'),
            'erroroccured' => $this->__('An error has occured.'),
            'tryagain' => $this->__('Would you like to try again?'),
            'retriesfailed' => $this->__('### retries have failed. Try again later.'),
            'limitexceeded_create' => $this->__('The simple products quantity limit is exceeded.'),
            'limitexceeded_generate' => $this->__('Total number of attributes options combinations is ###\nIt will exceed the simple products quantity limit.\nPlease select less attributes options.'),

            /* messages */

            'productsinvalid' => $this->__('### product(s) are not configured properly.'),
            'duplicatesmarked' => $this->__('Duplicates will be marked in the table.'),
            'allproductsremoved' => $this->__('### product(s) have been removed. Actual products will only be deleted upon saving the main product.'),
            'generated' => $this->__('### product(s) have been generated.<br />Save main product to create them.'),
            'restored' => $this->__('### product(s) have been restored from temporary data.'),
            'savingmain' => $this->__('Process completed.<br />Saving the main product now.<br />Please wait...'),
            'productslimitrecommended' => $this->__('Recommended simple products limit is ###.'),
            'productslimitenabled' => $this->__('Simple products limit is enabled.<br />You will not be able to create more than ### product(s).<br />You can turn the limit off in the admin configuration.'),

            /* progress bar */

            'pleasewait' => $this->__('Please wait...'),

            'ajax_cleartemp' => $this->__('Clearing temporary data...'),
            'ajax_cleartemp_success' => $this->__('Temporary data is cleared.'),

            'ajax_storetemp' => $this->__('Uploading products data...'),
            'ajax_storetemp_success_batch' => $this->__('### item(s) have been uploaded.'),
            'ajax_storetemp_success_all' => $this->__('All items have been uploaded.'),

            'ajax_validate' => $this->__('Validating products data...'),
            'ajax_validate_success' => $this->__('Products data is valid.'),

            'ajax_remove_duplicates' => $this->__('Removing duplicates...'),
            'ajax_remove_duplicates_success' => $this->__('### duplicate(s) have been removed.'),

            'ajax_delete_products' => $this->__('Deleting products...'),
            'ajax_delete_products_success' => $this->__('### product(s) have been deleted.'),

            'ajax_save' => $this->__('Saving data...'),
            'ajax_save_success_batch' => $this->__('### product(s) have been saved.'),
            'ajax_save_success_all' => $this->__('All products saved.'),

            'ajax_generate' => $this->__('Generating new products...'),

            'ajax_restore' => $this->__('Restoring temporary data...')
        );
    }
}
