<?php

ChangeConfigValue('ShopFreightModule', 'ShopFreightMethod', 'Default', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>