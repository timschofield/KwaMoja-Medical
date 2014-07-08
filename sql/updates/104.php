<?php

AddColumn('pettycash', 'bankaccounts', 'TINYINT(1)', 'NOT NULL', 0, 'bankaddress');
AddColumn('userid', 'banktrans', 'VARCHAR(20)', 'NOT NULL', '', 'currcode');

UpdateDBNo(basename(__FILE__, '.php'));

?>