<?php

DeleteConfigValue('DefaultTheme', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>