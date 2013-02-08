<?php

$installer = $this;
$installer->startSetup();
$installer->run("
  CREATE TABLE `{$installer->getTable('dhlshipment/dhlshipment')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL DEFAULT '',
  `status_awb` text NOT NULL,
  `tracking_awb` varchar(100) DEFAULT NULL,
  `status_return` text NOT NULL,
  `return_awb` varchar(100) NOT NULL,
  `status_pickup` text NOT NULL,
  `pickup` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
      
");
$installer->endSetup();