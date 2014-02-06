<?php

AddColumn('shipmentdate', 'shipments', 'date', 'NOT NULL', '1901-01-01', 'vessel', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>