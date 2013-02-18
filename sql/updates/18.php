<?php

DropColumn('lastcurcostdate', 'stockmaster', $db);
AddColumn('lastcostupdate', 'stockmaster', 'DATE', 'NOT NULL', "DEFAULT '0000-00-00'", 'netweight', $db);

NewConfigValue('InventoryManagerEmail',  'inventory@example.com', $db);

AddIndex(array('ref'), 'bantrans', 'ref', $db);

AddColumn('tag', 'pcexpenses', 'TINYINT( 4 )', 'NOT NULL', "DEFAULT 0", 'glaccount', $db);

DropConstraint('debtotrans', 'debtortrans_ibfk_1', $db);

ChangeConfigValue('VersionNumber', '4.06.6', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>