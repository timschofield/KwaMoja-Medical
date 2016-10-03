<?php
define("VERSIONFILE", "1.05");

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include_once('includes/session.php');
$Title = _('KwaMoja to OpenCart Daily Synchronizer ' . VERSIONFILE);
include_once('includes/header.php');
include_once('includes/GetPrice.php');

// include ('includes/KLGeneralFunctions.php');
include_once('includes/OcKwaMojaOpenCartDefines.php');
include_once('includes/OcOpenCartGeneralFunctions.php');
include_once('includes/OcKwaMojaToOpenCartSync.php');
include_once('includes/OcOpenCartConnectDB.php');

KwaMojaToOpenCartDailySync(TRUE, $oc_tableprefix);

include_once('includes/footer.php');

?>