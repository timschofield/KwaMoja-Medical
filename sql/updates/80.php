<?php

CreateTable('pricematrix',
"CREATE TABLE IF NOT EXISTS `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`salestype`,`stockid`,`quantitybreak`),
  KEY `DiscountCategory` (`stockid`),
  KEY `SalesType` (`salestype`)
)",
$db);

NewScript('PriceMatrix.php', 11, $db);

NewMenuItem('system', 'Reports', _('Mantain prices by quantity break and sales types'), '/PriceMatrix.php', 15, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>