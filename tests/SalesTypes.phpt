#!/usr/bin/php5
<?php

include('includes/config.php');

include('includes/login.php');
include('includes/SelectModule.php');
include('includes/SelectMenuOption.php');
include('includes/FillForm.php');

//include('includes/validators/XhtmlValidator.php');
include('classes/URLDetails.class.php');

include('data/suppliers.data');

//Set up the session ID
//open connection
$ch = curl_init();
$TestSessionID = sha1(uniqid(mt_rand(), true));

$IndexPage=KwaMojaLogIn($ch, $TestSessionID, $RootPath, $ServerPath, $CompanyName, $UserName, $Password);

$SetupPage=FindModule($ch, $RootPath, $ServerPath, $TestSessionID, $IndexPage, 'Setup');

$SalesTypePage=ChooseMenuOption($ch, $RootPath, $ServerPath, $TestSessionID, $SetupPage, 'Sales Types');

$PostData=FillFormWithRandomData($SalesTypePage[2]);

$SalesTypeInsertPage = new URLDetails($TestSessionID);
$SalesTypeInsertPage->SetURL($ServerPath.$SalesTypePage[2]['Action']);
$SalesTypeInsertPage->SetPostArray($PostData);

$Page=$SalesTypeInsertPage->FetchPage($RootPath, $ServerPath, $ch);

if (!strstr($Page[0], 'success')) {
	$InputDump = print_r($PostData, true);
	error_log('**Error**'.' The sales type does not seem to have been inserted correctly using the following data:'."\n", 3, '/home/tim/kwamoja'.date('Ymd').'.log');
	error_log($InputDump."\n\n", 3, '/home/tim/kwamoja'.date('Ymd').'.log');
} else {
	$InputDump = print_r($PostData, true);
	error_log('**Success**'.$InputDump."\n\n", 3, '/home/tim/kwamoja'.date('Ymd').'.log');
}
curl_close($ch);

?>