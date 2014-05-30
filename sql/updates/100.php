<?php

AddColumn('authoriser', 'purchorders', 'VARCHAR(20)', 'NOT NULL', '', 'initiator');

$sql = "UPDATE purchorders SET authoriser=initiator";
$result = DB_query($sql);

$DirHandle = dir('companies/');
while (false !== ($CompanyEntry = $DirHandle->read())) {
	if (is_dir('companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry != '.') {
		copy('companies/' . $DefaultDatabase . '/FormDesigns/PurchaseOrder.xml', 'companies/' . $CompanyEntry . '/FormDesigns/PurchaseOrder.xml');
	}
}
$DirHandle->close();

UpdateDBNo(basename(__FILE__, '.php'));

?>