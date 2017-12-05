<?php

$installer = $this;

$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('megamenu/megamenu')}
  ADD COLUMN `position` float;  
");
$installer->endSetup();