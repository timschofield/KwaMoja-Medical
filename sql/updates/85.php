<?php

NewScript('SalesTopCustomersInquiry.php', 2, $db);

NewMenuItem('orders', 'Reports', _('Top Customers Inquiry'), '/SalesTopCustomersInquiry.php', 16, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>