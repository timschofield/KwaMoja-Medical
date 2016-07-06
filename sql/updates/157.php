<?php

AddColumn('symbol', 'currencies', 'CHAR(3)', 'NOT NULL', "$", 'hundredsname');
AddColumn('symbolbefore', 'currencies', 'TINYINT(1)', 'NOT NULL', "0", 'symbol');

UpdateDBNo(basename(__FILE__, '.php'));

?>