<?php

CreateTable('stocktypes',
"CREATE TABLE `stocktypes` (
  `type` char(1) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `physicalitem` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`type`)
)",
$db);

NewScript('StockTypes.php', 15, $db);

NewMenuItem('system', 'Reports', _('Mantain stock types'), '/StockTypes.php', 16, $db);

InsertRecord('stocktypes', array('type'), array('D'), array('type', 'name', 'physicalitem'), array('D', _('Dummy Item - (No Movements)'), 0), $db);
InsertRecord('stocktypes', array('type'), array('F'), array('type', 'name', 'physicalitem'), array('F', _('Finished Goods'), 1), $db);
InsertRecord('stocktypes', array('type'), array('L'), array('type', 'name', 'physicalitem'), array('L', _('Labour'), 0), $db);
InsertRecord('stocktypes', array('type'), array('M'), array('type', 'name', 'physicalitem'), array('M', _('Raw Materials'), 1), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>