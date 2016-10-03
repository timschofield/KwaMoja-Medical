<?php
define("VERSIONFILE", "1.10");

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include('includes/session.php');
$Title = _('KwaMoja to OpenCart Hourly Synchronizer ' . VERSIONFILE);
include('includes/header.php');
include('includes/GetPrice.php');

// include ('includes/KLGeneralFunctions.php');
include('includes/OcKwaMojaOpenCartDefines.php');
include('includes/OcOpenCartGeneralFunctions.php');
include('includes/OcKwaMojaToOpenCartSync.php');
include('includes/OcOpenCartConnectDB.php');

KwaMojaToOpenCartHourlySync(TRUE, $oc_tableprefix, TRUE, '');

include('includes/footer.php');

?>