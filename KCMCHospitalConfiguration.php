<?php

include('includes/session.php');

$Title = _('Hospital Configuration');

include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/Hospital.png" title="' . _('Hospital Configuration') . '" alt="" />' . $Title . '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if ($InputError != 1) {

		$SQL = array();

		if ($_SESSION['DispenseOnBill'] != $_POST['X_DispenseOnBill']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_DispenseOnBill'] . "' WHERE confname = 'DispenseOnBill'";
		}
		if ($_SESSION['CanAmendBill'] != $_POST['X_CanAmendBill']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_CanAmendBill'] . "' WHERE confname = 'CanAmendBill'";
		}
		if ($_SESSION['DefaultArea'] != $_POST['X_DefaultArea']){
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultArea'] . "' WHERE confname='DefaultArea'";
		}
		if ($_SESSION['DefaultSalesPerson'] != $_POST['X_DefaultSalesPerson']){
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultSalesPerson'] . "' WHERE confname='DefaultSalesPerson'";
		}
		$ErrMsg = _('The hospital configuration could not be updated because');
		$DbgMsg = _('The SQL that failed was') . ':';
		if (sizeof($SQL) > 0) {
			$Result = DB_Txn_Begin();
			foreach ($SQL as $SqlLine) {
				$Result = DB_query($SqlLine, $ErrMsg, $DbgMsg, true);
			}
			$Result = DB_Txn_Commit();
			prnMsg(_('Hospital configuration updated'), 'success');

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include($PathPrefix . 'includes/GetConfig.php');
			$ForceConfigReload = False;
		}
	} else {
		prnMsg(_('Validation failed') . ', ' . _('no updates or deletes took place'), 'warn');
	}

}
/* end of if submit */

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<table cellpadding="2" class="selection" width="98%">
		<tr>
			<th>' . _('Hospital Configuration Parameter') . '</th>
			<th>' . _('Value') . '</th>
			<th>' . _('Notes') . '</th>
		</tr>';

echo '<tr>
		<th colspan="3">' . _('General Settings') . '</th>
	</tr>';

echo '<tr>
		<td>' . _('Dispense on Bill') . ':</td>
		<td><select name="X_DispenseOnBill">';
if ($_SESSION['DispenseOnBill'] == '0') {
	echo '<option selected="selected" value="0">' . _('No') . '</option>
			<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option value="0">' . _('No') . '</option>
			<option selected="selected" value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
		<td>' . _('Should items be deducted from stock automatically on production of the bill, or on actual dispensing?') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Cashiers can Amend Bills') . ':</td>
		<td><select name="X_CanAmendBill">';
if ($_SESSION['CanAmendBill'] == '0') {
	echo '<option selected="selected" value="0">' . _('No') . '</option>
			<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option value="0">' . _('No') . '</option>
			<option selected="selected" value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
		<td>' . _('Can the cashiers delete and insert lines in patients bills?') . '</td>
	</tr>';

$SQL = "SELECT salesmancode, salesmanname FROM salesman";
$Result = DB_query($SQL);
echo '<tr>
		<td>' . _('Default Sales Person for Patients') . ':</td>
		<td><select required="required" minlength="1" tabindex="14" name="X_DefaultSalesPerson">
				<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['DefaultSalesPerson']) and $MyRow['salesmancode'] == $_SESSION['DefaultSalesPerson']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
} //end while loop
echo '</select>
			</td>
			<td>' . _('The default sales person that will be used when patients are transferred from care2x') . '</td>
		</tr>';

$SQL = "SELECT areacode, areadescription FROM areas";
$Result = DB_query($SQL);
echo '<tr>
		<td>' . _('Default Sales Area for Patients') . ':</td>
		<td><select required="required" minlength="1" tabindex="14" name="X_DefaultArea">
				<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['DefaultArea']) and $MyRow['areacode'] == $_SESSION['DefaultArea']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
} //end while loop
echo '</select>
			</td>
			<td>' . _('The default sales area that will be used when patients are transferred from care2x') . '</td>
		</tr>';

echo '</table>
		<div class="centre"><input type="submit" name="submit" value="' . _('Update') . '" /></div>
	</form>';

include('includes/footer.php');
?>