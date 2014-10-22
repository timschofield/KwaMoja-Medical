<?php

include('includes/session.inc');

$Title = _('Basic Pay Section');

include('includes/header.inc');

if (isset($_GET['Bracket'])) {
	$Bracket = $_GET['Bracket'];
} elseif (isset($_POST['Bracket'])) {
	$Bracket = $_POST['Bracket'];
} else {
	unset($Bracket);
	$_POST["New"] = True;
}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/payrol.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($Bracket) == 0) {
		$InputError = 1;
		prnMsg(_('The Salary Bracket cannot be empty'), 'error');
	}
	if (!is_numeric($_POST['Bracket'])) /* Check if the bank code is numeric */ {
		prnMsg(_('Salary Bracket must be numeric'), 'error');
		$InputError = 1;
	}

	if ($InputError != 1) {
		if (!isset($_POST["New"])) {
			$sql = "UPDATE prlbasicpaytable SET rangefrom='" . $_POST['RangeFr'] . "',
												rangeto='" . $_POST['RangeTo'] . "',
												salarycredit='" . $_POST['Credit'] . "',
												employerbasicpay='" . $_POST['ERPH'] . "',
												employeebasicpay='" . $_POST['EEPH'] . "',
												total='" . $_POST['Total'] . "'
											WHERE bracket='" . $Bracket . "'";
			$ErrMsg = _('The Basic Pay could not be updated because');
			$DbgMsg = _('The SQL that was used to update the Basic Pay but failed was');
			$result = DB_query($sql, $ErrMsg, $DbgMsg);
			prnMsg(_('The Basic Pay master record for') . ' ' . $Bracket . ' ' . _('has been updated'), 'success');
		} else { //its a new PhilHealth
			$sql = "INSERT INTO prlbasicpaytable (  bracket,
													rangefrom,
													rangeto,
													salarycredit,
													employerbasicpay,
													employeebasicpay,
													total
												) VALUES (
													'" . $Bracket . "',
													'" . $_POST['RangeFr'] . "',
													'" . $_POST['RangeTo'] . "',
													'" . $_POST['Credit'] . "',
													'" . $_POST['ERPH'] . "',
													'" . $_POST['EEPH'] . "',
													'" . $_POST['Total'] . "'
												)";
			$ErrMsg = _('The Basic Pay') . ' ' . $_POST['Credit'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the Basic Pay but failed was');
			$result = DB_query($sql, $ErrMsg, $DbgMsg);
			prnMsg(_('A new Basic Pay has been added to the database'), 'success');
		}
		unset($Bracket);
		unset($_POST['RangeFr']);
		unset($_POST['RangeTo']);
		unset($_POST['Credit']);
		unset($_POST['ERPH']);
		unset($_POST['EEPH']);
		unset($_POST['Total']);
	} else {
		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');
	}
} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts
	if ($CancelDelete == 0) {
		$sql = "DELETE FROM prlbasicpaytable WHERE bracket='$Bracket'";
		$result = DB_query($sql);
		prnMsg(_('The Basic Pay record for') . ' ' . $Bracket . ' ' . _('has been deleted'), 'success');
		unset($Bracket);
		unset($_SESSION['Bracket']);
	} //end if Delete paypayperiod
}

/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

//Bracket exists - either passed when calling the form or from the form itself

echo '<form onSubmit="VerifyForm(this)" method="post" class="noPrint" id="BasicPay" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST["New"]) and isset($Bracket)) {
	$sql = "SELECT bracket,
				rangefrom,
				rangeto,
				salarycredit,
				employerbasicpay,
				employeebasicpay,
				total
			FROM prlbasicpaytable
			WHERE bracket='" . $Bracket . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);
	$_POST['RangeFr'] = $myrow['rangefrom'];
	$_POST['RangeTo'] = $myrow['rangeto'];
	$_POST['Credit'] = $myrow['salarycredit'];
	$_POST['ERPH'] = $myrow['employerbasicpay'];
	$_POST['EEPH'] = $myrow['employeebasicpay'];
	$_POST['Total'] = $myrow['total'];
	echo '<input type="hidden" name="Bracket" value="' . $Bracket . '" />';
	echo '<table>';
	echo '<tr>
			<td>' . _('Basic Pay Code') . ':</td>
			<td>' . $Bracket . '</td>
		</tr>';

} else {
	$_POST['RangeFr'] = 0;
	$_POST['RangeTo'] = 0;
	$_POST['Credit'] = 0;
	$_POST['ERPH'] = 0;
	$_POST['EEPH'] = 0;
	$_POST['Total'] = 0;
	$Bracket = '';
	// its a new PhilHealth being added
	echo '<input type="hidden" name="New" value="Yes">';
	echo '<table>';
	echo '<tr>
			<td>' . _('Basic Pay Code') . ':</td>
			<td><input type="text" name="Bracket" value="' . $Bracket . '" size="5" maxlength="4" /></td>
		</tr>';
}

echo '<tr>
		<td>' . _('Range From') . ':</td>
		<td><input type="number" class="number" name="RangeFr" size="14" maxlength="12" value="' . $_POST['RangeFr'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Range To') . ':</td>
		<td><input type="number" class="number" name="RangeTo" size="14" maxlength="12" value="' . $_POST['RangeTo'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Salary Base') . ':</td>
		<td><input type="number" class="number" name="Credit" size="14" maxlength="12" value="' . $_POST['Credit'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Employer Share') . ':</td>
	<td><input type="number" class="number" name="ERPH" size="14" maxlength="12" value="' . $_POST['ERPH'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Employee Share') . ':</td>
		<td><input type="number" class="number" name="EEPH" size="14" maxlength="12" value="' . $_POST['EEPH'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Total') . ':</td>
		<td><input type="number" class="number" name="Total" size="14" maxlength="12" value="' . $_POST['Total'] . '" /></td>
	</tr>
</table>';

if (isset($_POST["New"])) {
	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Add These New Basic Pay Details') . '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Update Basic Pay') . '">
			<input type="submit" name="delete" value="' . _('Delete Basic Pay') . '" onclick="return confirm("' . _('Are you sure you wish to delete this Basic Pay?') . '");" />
		</div>';
}
echo '</form>';

// end of main ifs

$sql = "SELECT bracket,
				rangefrom,
				rangeto,
				salarycredit,
				employerbasicpay,
				employeebasicpay,
				total
			FROM prlbasicpaytable
			ORDER BY bracket";

$ErrMsg = _('Could not get Basic pay because');
$result = DB_query($sql, $ErrMsg);

echo '<table class="selection">
		<tr>
			<th>' . _('Salary Bracket') . '</th>
			<th>' . _('Range From') . '</th>
			<th>' . _('Range To') . '</th>
			<th>' . _('Salary Base') . '</th>
			<th>' . _('Employer Share') . '</th>
			<th>' . _('Employee Share') . '</th>
			<th>' . _('Total') . '</th>
		</tr>';

$k = 0; //row colour counter
while ($myrow = DB_fetch_array($result)) {

	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}
	echo '<td>' . $myrow['bracket'] . '</td>
		<td>' . $myrow['rangefrom'] . '</td>
		<td>' . $myrow['rangeto'] . '</td>
		<td>' . $myrow['salarycredit'] . '</td>
		<td>' . $myrow['employerbasicpay'] . '</td>
		<td>' . $myrow['employeebasicpay'] . '</td>
		<td>' . $myrow['total'] . '</td>
		<td><a href="' . $_SERVER['PHP_SELF'] . '?Bracket=' . $myrow['bracket'] . '">' . _('Edit') . '</a></td>
		<td><a href="' . $_SERVER['PHP_SELF'] . '?Bracket=' . $myrow['bracket'] . '&delete=1">' . _('Delete') . '</a></td>
	</tr>';
} //END WHILE LIST LOOP
echo '</table>';

include('includes/footer.inc');
?>