<?php

AddColumn('language', 'accountsection', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'sectionid');
DropPrimaryKey('accountsection', Array('sectionid'));
AddPrimaryKey('accountsection', Array('sectionid', 'language'));

AddColumn('language', 'accountgroups', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'groupcode');
DropPrimaryKey('accountgroups', Array('groupcode'));
AddPrimaryKey('accountgroups', Array('groupcode', 'language'));

AddColumn('language', 'chartmaster', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'accountcode');
DropPrimaryKey('chartmaster', Array('accountcode'));
AddPrimaryKey('chartmaster', Array('accountcode', 'language'));

UpdateDBNo(basename(__FILE__, '.php'));

?>