<?php

AddColumn('statement', 'custcontacts', 'tinyint(4)', 'NOT NULL', "0", 'email');

UpdateDBNo(basename(__FILE__, '.php'));

?>