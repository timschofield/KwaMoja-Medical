<?php

NewScript('Z_ImportDebtors.php', 15, $db);
NewScript('Z_ImportSuppliers.php', 15, $db);

NewMenuItem('Utilities', 'Transactions', _('Import Debtors'), '/Z_ImportDebtors.php', 13, $db);
NewMenuItem('Utilities', 'Transactions', _('Import Suppliers'), '/Z_ImportSuppliers.php', 14, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>