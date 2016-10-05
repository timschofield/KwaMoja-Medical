<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
$Title = _('Register a Patient');
include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/PatientData.png" title="' . _('Search') . '" alt="" />' . $Title . '</p>';

if (isset($_POST['Create'])) {

	$InputError = 0;

	$SQL = "SELECT debtorno FROM debtorsmaster WHERE debtorno='" . $_POST['FileNumber'] . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($result) != 0) {
		$InputError = 1;
		$msg[] = _('That file number has already been used for another patient. Please select another file number.');
	}

	if ($_SESSION['AutoDebtorNo'] == 0 and mb_strlen($_POST['FileNumber']) == 0) {
		$InputError = 1;
		$msg[] = _('You must input a file number');
	}

	if (mb_strlen($_POST['Name']) == 0) {
		$InputError = 1;
		$msg[] = _('You must input the name of the patient you are registering');
	}

	if (mb_strlen($_POST['DateOfBirth']) == 0) {
		$InputError = 1;
		$msg[] = _('You must input the date of birth of the patient');
	}

	if (mb_strlen($_POST['SalesType']) == 0) {
		$InputError = 1;
		$msg[] = _('Please select a price list ');
	}

	if (mb_strlen($_POST['Sex']) == 0) {
		$InputError = 1;
		$msg[] = _('Please select the gender of the patient');
	}

	if ($InputError == 1) {
		foreach ($msg as $message) {
			prnMsg($message, 'error');
		}
	} else {

		$SalesAreaSQL = "SELECT areacode FROM areas";
		$SalesAreaResult = DB_query($SalesAreaSQL);
		$SalesAreaRow = DB_fetch_array($SalesAreaResult);

		$SalesManSQL = "SELECT salesmancode FROM salesman";
		$SalesManResult = DB_query($SalesManSQL);
		$SalesManRow = DB_fetch_array($SalesManResult);

		if ($_SESSION['AutoDebtorNo'] > 0) {
			/* system assigned, sequential, numeric */
			if ($_SESSION['AutoDebtorNo'] == 1) {
				$_POST['FileNumber'] = GetNextTransNo(500);
			}
		}

		$SQL = "INSERT INTO debtorsmaster (debtorno,
										name,
										address1,
										address2,
										address3,
										address4,
										address5,
										address6,
										currcode,
										salestype,
										clientsince,
										gender,
										paymentterms)
									VALUES (
										'" . $_POST['FileNumber'] . "',
										'" . $_POST['Name'] . "',
										'" . $_POST['Address1'] . "',
										'" . $_POST['Address2'] . "',
										'" . $_POST['Address3'] . "',
										'" . $_POST['Address4'] . "',
										'" . $_POST['Address5'] . "',
										'" . $_POST['Address6'] . "',
										'" . $_SESSION['CompanyRecord']['currencydefault'] . "',
										'" . $_POST['SalesType'] . "',
										'" . FormatDateForSQL($_POST['DateOfBirth']) . "',
										'" . $_POST['Sex'] . "',
										'20'
									)";

		$Result = DB_query($SQL);

		$SQL = "INSERT INTO custbranch (branchcode,
										debtorno,
										brname,
										area,
										salesman,
										phoneno,
										defaultlocation,
										taxgroupid)
									VALUES (
										'CASH',
										'" . $_POST['FileNumber'] . "',
										'CASH',
										'" . $SalesAreaRow['areacode'] . "',
										'" . $SalesManRow['salesmancode'] . "',
										'" . $_POST['Telephone'] . "',
										'" . $_SESSION['DefaultFactoryLocation'] . "',
										'1'
									)";
		$Result = DB_query($SQL);

		if ($_POST['Insurance'] != '') {
			$SQL = "INSERT INTO custbranch (branchcode,
											debtorno,
											brname,
											area,
											salesman,
											phoneno,
											defaultlocation,
											taxgroupid)
										VALUES (
											'" . $_POST['Insurance'] . "',
											'" . $_POST['FileNumber'] . "',
											'" . $_POST['Insurance'] . "',
											'" . $SalesAreaRow['areacode'] . "',
											'" . $SalesManRow['salesmancode'] . "',
											'" . $_POST['Telephone'] . "',
											'" . $_SESSION['DefaultFactoryLocation'] . "',
											'1'
										)";
			$Result = DB_query($SQL);

		}
		prnMsg(_('The patient') . ' ' . $_POST['FileNumber'] . ' ' . _('has been successfully registered'), 'success');

		unset($_POST['FileNumber']);
		unset($_POST['Name']);
		unset($_POST['Address1']);
		unset($_POST['Address2']);
		unset($_POST['Address3']);
		unset($_POST['Address4']);
		unset($_POST['Address5']);
		unset($_POST['Address6']);
		unset($_POST['SalesType']);
		unset($_POST['DateOfBirth']);
		unset($_POST['Sex']);
	}
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table colspan="4">';

echo '<tr><th colspan="2">' . _('New Patient Details') . '</th></tr>';

if ($_SESSION['AutoDebtorNo'] == 0) {
	echo '<tr><td>' . _('File Number') . ':</td>';
	if (isset($_POST['FileNumber'])) {
		echo '<td><input type="text" size="10" name="FileNumber" value="' . $_POST['FileNumber'] . '" /></td></tr>';
	} else {
		echo '<td><input type="text" size="10" name="FileNumber" value="" /></td></tr>';
	}
}

echo '<tr><td>' . _('Name') . ':</td>';
if (isset($_POST['Name'])) {
	echo '<td><input type="text" size="20" name="Name" value="' . $_POST['Name'] . '" /></td></tr>';
} else {
	echo '<td><input type="text" size="20" name="Name" value="" /></td></tr>';
}

echo '<tr><td>' . _('Address') . ':</td>';
if (isset($_POST['Address1'])) {
	echo '<td><input type="text" size="20" name="Address1" value="' . $_POST['Address1'] . '" /></td></tr>';
} else {
	echo '<td><input type="text" size="20" name="Address1" value="" /></td></tr>';
}
if (isset($_POST['Address2'])) {
	echo '<td></td><td><input type="text" size="20" name="Address2" value="' . $_POST['Address2'] . '" /></td></tr>';
} else {
	echo '<td></td><td><input type="text" size="20" name="Address2" value="" /></td></tr>';
}
if (isset($_POST['Address3'])) {
	echo '<td></td><td><input type="text" size="20" name="Address3" value="' . $_POST['Address3'] . '" /></td></tr>';
} else {
	echo '<td></td><td><input type="text" size="20" name="Address3" value="" /></td></tr>';
}
if (isset($_POST['Address4'])) {
	echo '<td></td><td><input type="text" size="20" name="Address4" value="' . $_POST['Address4'] . '" /></td></tr>';
} else {
	echo '<td></td><td><input type="text" size="20" name="Address4" value="" /></td></tr>';
}
if (isset($_POST['Address5'])) {
	echo '<td></td><td><input type="text" size="20" name="Address5" value="' . $_POST['Address5'] . '" /></td></tr>';
} else {
	echo '<td></td><td><input type="text" size="20" name="Address5" value="" /></td></tr>';
}
if (isset($_POST['Address6'])) {
	echo '<td></td><td><input type="text" size="20" name="Address6" value="' . $_POST['Address6'] . '" /></td></tr>';
} else {
	echo '<td></td><td><input type="text" size="20" name="Address6" value="" /></td></tr>';
}

echo '<tr><td>' . _('Telephone Number') . ':</td>';
if (isset($_POST['Telephone'])) {
	echo '<td><input type="text" size="12" name="Telephone" value="' . $_POST['Telephone'] . '" /></td></tr>';
} else {
	echo '<td><input type="text" size="12" name="Telephone" value="" /></td></tr>';
}

echo '<tr><td>' . _('Date Of Birth') . ':</td>';
if (isset($_POST['DateOfBirth'])) {
	echo '<td><input type="text" placeholder="' . $_SESSION['DefaultDateFormat'] . '" name="DateOfBirth" maxlength="10" size="10" value="' . $_POST['DateOfBirth'] . '" /></td></tr>';
} else {
	echo '<td><input type="text" placeholder="' . $_SESSION['DefaultDateFormat'] . '" name="DateOfBirth" maxlength="10" size="10" value="" /></td></tr>';
}

$Result = DB_query("SELECT typeabbrev, sales_type FROM salestypes");
if (DB_num_rows($Result) == 0) {
	$DataError = 1;
	echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
	echo '<tr>
			<td colspan=2>' . prnMsg(_('No sales types/price lists defined'), 'error') . '</td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('Price List') . ':</td>
			<td><select tabindex=9 name="SalesType">';
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SalesType']) and $_POST['SalesType'] == $MyRow['typeabbrev']) {
			echo '<option selected="selected" value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
		}
	} //end while loopre
	echo '</select>
				</td>
			</tr>';
}

$Gender['m'] = _('Male');
$Gender['f'] = _('Female');
echo '<tr>
		<td>' . _('Sex') . ':</td>
		<td><select name="Sex">';
echo '<option value=""></option>';
foreach ($Gender as $Code=>$Name) {
	if (isset($_POST['Sex']) and $_POST['Sex'] == $Code) {
		echo '<option selected="selected" value="' . $Code . '">' . $Name . '</option>';
	} else {
		echo '<option value="' . $Code . '">' . $Name . '</option>';
	}
}
echo '</select>
			</td>
		</tr>';

$SQL = "SELECT debtorno,
				name
			FROM debtorsmaster
			INNER JOIN debtortype
				ON debtorsmaster.typeid=debtortype.typeid
			WHERE debtortype.typename like '%Insurance%'";
$Result = DB_query($SQL);

echo '<tr>
		<td>' . _('Insurance Company') . ':</td>
		<td><select name="Insurance">';
echo '<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Insurance']) and $_POST['Insurance'] == $MyRow['debtorno']) {
		echo '<option selected="selected" value="' . $MyRow['debtorno'] . '">' . $MyRow['name'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['debtorno'] . '">' . $MyRow['name'] . '</option>';
	}
}
echo '</select>
			</td>
		</tr>';

if (isset($_POST['Insurance']) and $_POST['Insurance'] != '') {
	$SQL = "SELECT salesmancode,
					salesmanname,
					smantel,
					smanfax
				FROM salesman";
	$Result = DB_query($SQL);

	echo '<tr>
			<td>' . _('Employer Company') . ':</td>
			<td><select name="Employer">';
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
	}
	echo '</select>
				</td>
			</tr>';
}

echo '</table>';
echo '<div class="centre">
		<input type="submit" name="Create" value="' . ('Register the patient') . '" />
	</div>';
echo '</form>';

include('includes/footer.php');
?>