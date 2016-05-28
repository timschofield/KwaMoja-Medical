<?php

AddColumn('qtygreaterthan', 'purchdata', 'int(11)', 'NOT NULL', "0", 'price');
DropPrimaryKey('purchdata', array('supplierno', 'stockid', 'effectivefrom'));
AddPrimaryKey('purchdata', array('supplierno', 'stockid' , 'effectivefrom', 'qtygreaterthan'));

UpdateDBNo(basename(__FILE__, '.php'));

?>