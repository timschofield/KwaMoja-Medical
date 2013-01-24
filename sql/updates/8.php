<?php

AddColumn('itemdue', 'salesorderdetails', 'DATE', 'NOT NULL', "DEFAULT '0000-00-00'", 'narrative', $db);
AddColumn('poline', 'salesorderdetails', 'VARCHAR(10)', 'NOT NULL', "DEFAULT ''", 'itemdue', $db);

AddColumn('customerpoline', 'debtorsmaster', 'TinyInt(1)', 'NOT NULL', "DEFAULT 0", 'taxref', $db);


UpdateDBNo(basename(__FILE__, '.php'), $db);

?>