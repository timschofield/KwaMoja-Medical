<?php

$PageSecurity=0;

include('includes/session.inc');
$Title = _('Dashboard');
include('includes/header.inc');
include('includes/MainMenuLinksArray.php');

$SQL = "SELECT scripts FROM dashboard_users WHERE userid = '".$_SESSION['UserID']."' ";

$Result = DB_query($SQL);

$MyRow = DB_fetch_array($Result);
$arr = explode(',',$MyRow["scripts"]);

$UserSQL = "SELECT scripts FROM dashboard_users WHERE userid = '".$_SESSION['UserID']."' ";
$Result = DB_query($UserSQL);
if (DB_num_rows($Result) == 0) {
	$InsertSQL = "INSERT INTO dashboard_users VALUES(null, '" . $_SESSION['UserID'] . "', '')";
	$InsertResult = DB_query($InsertSQL);
}

if (isset($_GET['Remove'])) {
	if(($key = array_search($_GET['Remove'], $arr)) !== false) {
		unset($arr[$key]);
	}
	$UpdateSQL = "UPDATE dashboard_users SET scripts='" . implode(',', $arr) . "' WHERE userid = '".$_SESSION['UserID']."'";
	$UpdateResult = DB_query($UpdateSQL);
}

if (isset($_POST['Reports'])) {
	$arr[] = $_POST['Reports'];
	asort($arr);
	$UpdateSQL = "UPDATE dashboard_users SET scripts='" . implode(',', $arr) . "' WHERE userid = '".$_SESSION['UserID']."' ";
	$UpdateResult = DB_query($UpdateSQL);
}

$SQL = "SELECT id,
				scripts,
				pagesecurity,
				description
			FROM dashboard_scripts";

$Result = DB_query($SQL);

while($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['id'], $arr) and in_array($MyRow['pagesecurity'], $_SESSION['AllowedPageSecurityTokens'])) {
		echo '<iframe src="dashboard/' . $MyRow['scripts'] . '"></iframe>';
	}
}

DB_data_seek($Result, 0);

echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="submit" name="submit" value="" style="display:none;" />';
echo '<div style="width:100%;font-size:120%;color:#2F4F4F;">' .
		_('Add reports to your dashboard') . '&nbsp;&nbsp;&nbsp;
		<select name="Reports" style="font-size:100%;margin-top:10px;margin-bottom:0px;color:#2F4F4F;" id="favourites" onchange="ReloadForm(submit)">';
echo '<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (!in_array($MyRow['id'], $arr) and in_array($MyRow['pagesecurity'], $_SESSION['AllowedPageSecurityTokens'])) {
		echo '<option value="' . $MyRow['id'] . '">' . $MyRow['description'] . '</option>';
	}
}
echo '</select></div>';
echo '</form>';

include('includes/footer.inc');
?>