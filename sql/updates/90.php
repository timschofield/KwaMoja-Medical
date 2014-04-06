<?php

NewScript('PDFWOPrint.php', 11);
NewScript('PDFFGLabel.php', 11);
NewScript('PDFQALabel.php', 2);

AddColumn('comments', 'woitems', 'text', 'NULL', 'NULL', 'nextlotsnref');

$DirHandle = dir('companies/');
while (false !== ($CompanyEntry = $DirHandle->read())) {
	if (is_dir('companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry != '.') {
		copy('companies/kwamoja/FormDesigns/FGLabel.xml', 'companies/' . $CompanyEntry . '/FormDesigns/FGLabel.xml');
		copy('companies/kwamoja/FormDesigns/WOPaperwork.xml', 'companies/' . $CompanyEntry . '/FormDesigns/WOPaperwork.xml');
		copy('companies/kwamoja/FormDesigns/QALabel.xml', 'companies/' . $CompanyEntry . '/FormDesigns/QALabel.xml');
	}
}
$DirHandle->close();

UpdateDBNo(basename(__FILE__, '.php'));

?>