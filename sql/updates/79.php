<?php

ChangeColumnSize('host', 'emailsettings', 'VARCHAR(50)', 'NOT NULL', '', 50, $db);
ChangeColumnSize('username', 'emailsettings', 'VARCHAR(50)', 'NOT NULL', '', 50, $db);
ChangeColumnSize('password', 'emailsettings', 'VARCHAR(50)', 'NOT NULL', '', 50, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>