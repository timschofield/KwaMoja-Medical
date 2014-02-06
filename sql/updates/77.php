<?php

CreateTable('bankaccountusers',
"CREATE TABLE IF NOT EXISTS `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `userid` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (accountcode,userid)
)",
$db);

$sql = "INSERT INTO bankaccountusers (SELECT bankaccounts.accountcode, www_users.userid FROM bankaccounts, www_users)";
$result = DB_query($sql, $db);

NewScript('BankAccountUsers.php', 15, $db);

NewMenuItem('system', 'Maintenance', _('Bank Account Authorised Users'), '/BankAccountUsers.php', 13, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>