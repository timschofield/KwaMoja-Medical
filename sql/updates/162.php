<?php

ChangeColumnSize('bankact', 'suppliers', 'VARCHAR(40)', 'NOT NULL', ' ', '40');

UpdateDBNo(basename(__FILE__, '.php'));

?>