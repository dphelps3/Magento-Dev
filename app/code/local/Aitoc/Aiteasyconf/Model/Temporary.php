<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */
class Aitoc_Aiteasyconf_Model_Temporary extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('aiteasyconf/temporary');
    }

    public function clear($id)
    {
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('aiteasyconf_temporary');
        $query = 'delete from ' . $table . ' where `configurable_id` = ' . (int)$id;
        $connection = $resource->getConnection('core_write');
        return (($connection->query($query)) ? true : false);
    }

    public function getTempData($mainId)
    {
        return $collection = $this->getCollection()->filterByMainId($mainId)->load();
    }

    public function getTempIds($mainId)
    {
        $tempIds = array();
        $collection = $this->getCollection()->filterByMainId($mainId)->load();
        foreach ($collection as $product) {
            $tempIds[] = $product['product_id'];
        }

        return $tempIds;
    }

    public function getBatch($mainId, $page = 1)
    {
        $batchSize = Mage::helper('aiteasyconf')->getBatchSize('php');

        $collection = $this->getCollection();
        $collection->filterByMainId($mainId);
        $collection->getSelect()->order('id', 'desc');
        $collection->setPageSize($batchSize)->setCurPage($page);

        return $collection->load();
    }

    public function getDuplicatesCount($mainId)
    {
        // if there are duplicates, 'select count()' will be greater than 'select distinct count()'

        $c1 = $this->getCollection()
            ->filterByMainId($mainId)
            ->addFieldToSelect('attributes');

        $c2 = $this->getCollection()
            ->filterByMainId($mainId)
            ->addFieldToSelect('attributes');

        $c2->getSelect()->distinct(true);

        return ($c1->count() - $c2->count());
    }

    public function getDuplicatesIds($mainId)
    {
        $collection = $this->getCollection()
            ->filterByMainId($mainId);

        $collection->getSelect()
            ->group('attributes')
            ->having('count(`id`) > 1');

        $ids = array();

        foreach ($collection->load() as $record) {
            $duplicates = $this->getCollection()
                ->filterByMainId($mainId)
                ->addFieldToSelect('product_id')
                ->addFieldToFilter('attributes', array('eq' => $record->getData('attributes')));

            foreach ($duplicates->load() as $duplicate) {
                $ids[] = $duplicate->getData('product_id');
            }
        }

        return $ids;
    }

    public function getDuplicatesAttributes($mainId)
    {
        $collection = $this->getCollection()
            ->filterByMainId($mainId);

        $collection->getSelect()
            ->group('attributes')
            ->having('count(`id`) > 1');

        $attributes = array();

        foreach ($collection->load() as $record) {
            $attributes[] = $record->getData('attributes');
        }

        return $attributes;
    }

    public function removeDuplicates($mainId)
    {
        $duplicatesAttributes = $this->getDuplicatesAttributes($mainId);

        $counter = 0;
        foreach ($duplicatesAttributes as $attributes) {
            $skip = true;

            $collection = $this->getCollection()
                ->filterByMainId($mainId)
                ->addFieldToSelect('id')
                ->addFieldToFilter('attributes', array('eq' => $attributes));

            $collection->getSelect()->order('is_changed', 'asc'); // preferably to leave unchanged one

            foreach ($collection->load() as $record) {
                if (true == $skip) {
                    /* leave one record and remove the others */
                    $skip = false;
                    continue;
                }

                $this->load($record->getId())->delete();
                $counter++;
            }
        }

        return $counter;
    }

    public function detect($mainId)
    {
        return $this->getCollection()->filterByMainId($mainId)->count();
    }
}
