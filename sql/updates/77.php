<?php

CreateTable('bankaccountusers',
"CREATE TABLE IF NOT EXISTS `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `userid` varchar(20) NOT NULL DEFAULT ''
)",
$db);

NewScript('BankAccountUsers.php', 15, $db);

NewMenuItem('system', 'Maintenance', _('Bank Account Authorized Users'), '/BankAccountUsers.php', 13, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>