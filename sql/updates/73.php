<?php

AddColumn('userid', 'stockrequest', 'VARCHAR( 20 )', 'NOT NULL', '', 'dispatchid', $db);

NewMenuItem('stock', 'Transactions', _('Amend an internal stock request'), '/InternalStockRequest.php?Edit=Yes', 11, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>