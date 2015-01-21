<?php

AddColumn('needsrevision', 'stockdescriptiontranslations', 'INT(1)', 'NOT NULL', '0', 'descriptiontranslation');
AddColumn('needsrevision', 'stocklongdescriptiontranslations', 'INT(1)', 'NOT NULL', '0', 'longdescriptiontranslation');

NewScript('AutomaticTranslationDescriptions.php', '15');
NewMenuItem('Utilities', 'Transactions', _('Automatic Translation - Item descriptions'), '/AutomaticTranslationDescriptions.php', 6);

UpdateDBNo(basename(__FILE__, '.php'));

?>