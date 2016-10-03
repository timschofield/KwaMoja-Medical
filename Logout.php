<?php

$AllowAnyone=True; /* Allow all users to log off  */

include('includes/session.php');
// Cleanup
unset($_SESSION);
session_unset();
session_destroy();
?>