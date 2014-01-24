<?php

DropConstraint('stockcatproperties', 'stockcatproperties_ibfk_2', $db);
DropConstraint('stockcatproperties', 'stockcatproperties_ibfk_3', $db);

RemoveScript('FixedAssetList.php', $db);
RemoveScript('MenuAccess.php', $db);
RemoveScript('OrderEntryDiscountPricing.php', $db);
RemoveScript('PrintSalesOrder.php', $db);
RemoveScript('ReportBug.php', $db);
RemoveScript('ReportletContainer.php', $db);
RemoveScript('SystemCheck.php', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>