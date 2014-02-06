<?php

CreateTable('securitytokens',
"CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT '0',
  `tokenname` text NOT NULL,
  PRIMARY KEY (`tokenid`)
)", $db);

InsertRecord('securitytokens', array('tokenid'), array(0), array('tokenid', 'tokenname'), array(0, 'Main Index Page'), $db);
InsertRecord('securitytokens', array('tokenid'), array(1), array('tokenid', 'tokenname'), array(1, 'Order Entry/Inquiries customer access only'), $db);
InsertRecord('securitytokens', array('tokenid'), array(2), array('tokenid', 'tokenname'), array(2, 'Basic Reports and Inquiries with selection options'), $db);
InsertRecord('securitytokens', array('tokenid'), array(3), array('tokenid', 'tokenname'), array(3, 'Credit notes and AR management'), $db);
InsertRecord('securitytokens', array('tokenid'), array(4), array('tokenid', 'tokenname'), array(4, 'Purchasing data/PO Entry/Reorder Levels '), $db);
InsertRecord('securitytokens', array('tokenid'), array(5), array('tokenid', 'tokenname'), array(5, 'Accounts Payable'), $db);
InsertRecord('securitytokens', array('tokenid'), array(6), array('tokenid', 'tokenname'), array(6, 'Petty Cash'), $db);
InsertRecord('securitytokens', array('tokenid'), array(7), array('tokenid', 'tokenname'), array(7, 'Bank Reconciliations'), $db);
InsertRecord('securitytokens', array('tokenid'), array(8), array('tokenid', 'tokenname'), array(8, 'General ledger reports/inquiries'), $db);
InsertRecord('securitytokens', array('tokenid'), array(9), array('tokenid', 'tokenname'), array(9, 'Supplier centre - Supplier access only'), $db);
InsertRecord('securitytokens', array('tokenid'), array(10), array('tokenid', 'tokenname'), array(10, 'General Ledger Maintenance, stock valuation & Configuration'), $db);
InsertRecord('securitytokens', array('tokenid'), array(11), array('tokenid', 'tokenname'), array(11, 'Inventory Management and Pricing'), $db);
InsertRecord('securitytokens', array('tokenid'), array(12), array('tokenid', 'tokenname'), array(12, 'Unknown'), $db);
InsertRecord('securitytokens', array('tokenid'), array(13), array('tokenid', 'tokenname'), array(13, 'Unknown'), $db);
InsertRecord('securitytokens', array('tokenid'), array(14), array('tokenid', 'tokenname'), array(14, 'Unknown'), $db);
InsertRecord('securitytokens', array('tokenid'), array(15), array('tokenid', 'tokenname'), array(15, 'User Management and System Administration'), $db);
InsertRecord('securitytokens', array('tokenid'), array(1000), array('tokenid', 'tokenname'), array(1000, 'User can view and alter sales prices'), $db);
InsertRecord('securitytokens', array('tokenid'), array(1001), array('tokenid', 'tokenname'), array(1001, 'User can bypass purchasing security and go straight from order to invoice'), $db);

$sql ="INSERT INTO securitygroups SELECT secroleid, tokenid FROM securityroles,securitytokens)";
executeSQL($sql, $db);

?>