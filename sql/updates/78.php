<?php

ChangeColumnSize('reference', 'stockmoves', 'VARCHAR(100)', 'NOT NULL', '', 100, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>