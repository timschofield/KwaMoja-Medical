<?php

ChangeConfigName('DefaultTaxLevel', 'DefaultTaxCategory');
ChangeConfigValue('part_pics_dir', 'companies/kwamoja/part_pics');
ChangeConfigValue('EDI_Incoming_Orders', 'companies/kwamoja/EDI_Incoming_Orders');
ChangeConfigValue('EDI_MsgPending', 'companies/kwamoja/EDI_MsgPending');
ChangeConfigValue('EDI_Sent', 'companies/kwamoja/EDI_Sent');
ChangeConfigValue('companies/kwamoja/reports', 'reports_dir');
DropColumn('pinno', 'www_users');
DropColumn('swipecard', 'www_users');
ChangeColumnType('bankact', 'suppliers', 'VARCHAR( 30 )', 'NOT NULL', '');

UpdateDBNo(basename(__FILE__, '.php'));

?>