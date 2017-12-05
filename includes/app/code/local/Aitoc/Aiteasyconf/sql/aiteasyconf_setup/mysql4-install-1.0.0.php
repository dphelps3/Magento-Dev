<?php

/* @copyright  Copyright (c) 2012 AITOC, Inc. */

$this->startSetup();

$this->run(
    "

  DROP TABLE IF EXISTS {$this->getTable('aiteasyconf_temporary')};
  CREATE TABLE IF NOT EXISTS {$this->getTable('aiteasyconf_temporary')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `configurable_id` int(11) NOT NULL,
  `product_id` text NOT NULL,
  `is_changed` tinyint(4) NOT NULL,
  `parameters` text NOT NULL,
  `attributes` text NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
  
"
);

$this->endSetup();