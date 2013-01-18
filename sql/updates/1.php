<?php

ChangeColumnType('language', 'www_users', 'char(5)', 'NOT NULL', 'en_GB', $db)

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>