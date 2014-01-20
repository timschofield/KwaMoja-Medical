<?php

AddColumn('sequence', 'bom', 'INT(11)', 'NOT NULL', '0', 'parent', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>