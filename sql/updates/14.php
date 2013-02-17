<?php

ChangeColumnType('pagesecurity', 'scripts', 'INT', 'NOT NULL', 'DEFAULT 1', $db);

AddColumn('assigner', 'pctabs', 'VARCHAR', 'NOT NULL', "DEFAULT ''", 'tablimit', $db);

ChangeConfigValue('VersionNumber', '4.04.1', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>