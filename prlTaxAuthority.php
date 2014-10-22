<?php

include('includes/session.inc');

$Title = _('Tax Authority Section');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/prlFunctions.php');

if (isset($_POST['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID = $_POST['SelectedTaxAuthID'];
} elseif (isset($_GET['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID = $_GET['SelectedTaxAuthID'];
}
?>
<a href="prlUserSettings.php">Back to User Settings
    </a>
	<?php

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if (trim($_POST['Description']) == '') {
		$InputError = 1;

		prnMsg(_('The tax type description may not be empty'), 'error');
	}

	if (isset($SelectedTaxAuthID)) {

		/*SelectedTaxAuthID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = 'UPDATE prltaxauthority
				SET description = ' . $_POST['Description'] . "',
				bank = '" . $_POST['Bank'] . "',
				bankacctype = '" . $_POST['BankAccType'] . "',
				bankacc = '" . $_POST['BankAcc'] . "',
			WHERE taxid = " . $SelectedTaxAuthID;

		$ErrMsg = _('The update of this tax authority failed because');
		$result = DB_query($sql, $ErrMsg);

		$msg = _('The tax authority for record has been updated');

	} elseif ($InputError != 1) {

		/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$sql = "INSERT INTO prltaxauthority (
						taxid,
						description,
						bank,
						bankacctype,
						bankacc)
			VALUES (
				'" . $_POST['taxid'] . "',
				'" . $_POST['Description'] . "',
				'" . $_POST['Bank'] . "',
				'" . $_POST['BankAccType'] . "',
				'" . $_POST['BankAcc'] . "')";

		$Errmsg = _('The addition of this tax authority failed because');
		$result = DB_query($sql, $Errmsg);

		$msg = _('The new tax authority record has been added to the database');

		$NewTaxID = DB_Last_Insert_ID('prltaxauthority', 'taxid');

		$sql = 'INSERT INTO prltaxauthrates (
					taxauthority,
					dispatchtaxprovince,
					taxcatid
					)
				SELECT
					' . $NewTaxID . ',
					prltaxprovinces.taxprovinceid,
					prltaxcategories.taxcatid
				FROM prltaxprovinces,
					prltaxcategories';

		$InsertResult = DB_query($sql);
	}
	//run the SQL from either of the above possibilites
	if (isset($InputError) and $InputError != 1) {
		unset($_POST['taxid']);
		unset($_POST['Description']);
		unset($_POST['bank']);
		unset($_POST['bankacctype']);
		unset($_POST['bankacc']);
		unset($SelectedTaxID);
	}

	prnMsg($msg);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$sql = 'SELECT COUNT(*)
			FROM prltaxgrouptaxes
		WHERE taxauthid=' . $SelectedTaxAuthID;

	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		prnmsg(_('Cannot delete this tax authority because there are tax groups defined that use it'), 'warn');
	} else {
		/*Cascade deletes in TaxAuthLevels */
		$result = DB_query('DELETE FROM prltaxauthrates WHERE taxauthority= ' . $SelectedTaxAuthID);
		$result = DB_query('DELETE FROM prltaxauthority WHERE taxid= ' . $SelectedTaxAuthID);
		prnMsg(_('The selected tax authority record has been deleted'), 'success');
		unset($SelectedTaxAuthID);
	} // end of related records testing
}

if (!isset($SelectedTaxAuthID)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxAuthID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = 'SELECT taxid,
			description,
			bank,
			bankacctype,
			bankacc
		FROM prltaxauthority';

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The defined tax authorities could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the tax authorities was used');
	$result = DB_query($sql, $ErrMsg, $DbgMsg);
	echo "<table>";
	echo '<table border=1>';
	echo "<tr>
		<th>" . _('ID') . "</td>
		<th>" . _('Tax Authority') . "</td>
		<th>" . _('Country') . "</td>
		<th>" . _('Bank With A/C ') . "</td>
		<th>" . _('Account Number') . "</td>
		</tr></font>";

	while ($myrow = DB_fetch_row($result)) {

		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&SelectedTaxAuthID=%s\">" . _('Edit') . "</a></td>
				<td><a href=\"%s&SelectedTaxAuthID=%s&delete=yes\">" . _('Delete') . '</a></td>
			</tr>', $myrow[0], $myrow[1], $myrow[3], $myrow[2], $myrow[4], $RootPath . '/prlTaxAuthorityRates.php?', $myrow[0], $_SERVER['PHP_SELF'], $myrow[0], $_SERVER['PHP_SELF'], $myrow[0]);

	}
	//END WHILE LIST LOOP

	//end of ifs and buts!

	echo '</table><p>';
}



if (isset($SelectedTaxAuthID)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Review all defined tax authority records') . '</a></div>';
}


echo '<p><form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

if (isset($SelectedTaxAuthID)) {
	//editing an existing tax authority

	$sql = 'SELECT
			taxid,
			description,
			bank,
			bankacctype,
			bankacc
		FROM prltaxauthority
		WHERE taxid=' . $SelectedTaxAuthID;

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['taxid'] = $myrow['taxid'];
	$_POST['Description'] = $myrow['description'];
	$_POST['Bank'] = $myrow['bank'];
	$_POST['BankAccType'] = $myrow['bankacctype'];
	$_POST['BankAcc'] = $myrow['bankacc'];

	echo '<input type="hidden" name="SelectedTaxAuthID" value="' . $SelectedTaxAuthID . '">';

} //end of if $SelectedTaxAuthID only do the else when a new record is being entered


$SQL = 'SELECT accountcode,
		accountname
	FROM chartmaster,
		prlaccountgroups
	WHERE chartmaster.group_=prlaccountgroups.groupname
	AND prlaccountgroups.pandl=0
	ORDER BY accountcode';
$result = DB_query($SQL);

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}
echo '<table>
<tr><td>' . _('Tax Authority Name') . ":</td>
<td><input type=Text name='Description' size=30 maxlength=50 value='" . $_POST['Description'] . "'></td></tr>";

while ($myrow = DB_fetch_array($result)) {
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' (' . $myrow['accountcode'] . ')';

} //end while loop

echo '</select></td></tr>';

DB_data_seek($result, 0);
while ($myrow = DB_fetch_array($result)) {

	echo $myrow['accountcode'] . "'>" . $myrow['accountname'] . ' (' . $myrow['accountcode'] . ')';

} //end while loop

if (!isset($_POST['Bank'])) {
	$_POST['Bank'] = '';
}
if (!isset($_POST['BankAccType'])) {
	$_POST['BankAccType'] = '';
}
if (!isset($_POST['BankAcc'])) {
	$_POST['BankAcc'] = '';
}
echo '</select></td></tr>';
echo "<table>";
echo '<tr><td>' . _('Country') . ':</td>';
echo '<td><input type=Text name="BankAccType" size=41 maxlength=40 value="' . $_POST['BankAccType'] . '"></td></tr>';
echo '<tr><td>' . _('Bank With Account') . ':</td>';
echo '<td><input type=Text name="Bank" size=15 maxlength=20 value="' . $_POST['Bank'] . '"></td></tr>';
echo '<tr><td>' . _(' Account Number') . ':</td>';
echo '<td><input type=Text name="BankAcc" size=21 maxlength=20 value="' . $_POST['BankAcc'] . '"></td></tr>';

echo '</table>';

echo '<div class="centre"><input type=submit name=submit value=' . _('Enter Information') . '></div></form>';

include('includes/footer.inc');

?>