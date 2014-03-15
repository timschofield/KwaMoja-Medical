<?php


InsertRecord('systypes', array('typeid'), array('600'), array('typeid' ,'typename' ,'typeno'), array('600',  _('Auto Supplier Number'),  '0'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>