<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php $PageSecurity now comes from session.php (and gets read in by GetConfig.php*/

include('includes/session.php');

$DebtorSQL = "SELECT name
				FROM debtorsmaster
					WHERE debtorno='" . $_SESSION['DebtorNo'] . "'";
$DebtorResult = DB_query($DebtorSQL);
$DebtorInfo = DB_fetch_array($DebtorResult);

$Title = _('Raise an invoice for') . ' ' . $DebtorInfo['name'];

include('includes/header.php');

include('includes/footer.php');

?>