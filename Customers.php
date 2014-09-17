<?php

include('includes/session.inc');

$Title = _('Customer Maintenance');
/* Manual links before header.inc */
if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'AmendCustomer';
} else {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'NewCustomer';
}
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customer Maintenance') . '
	</p>';

if (isset($Errors)) {
	unset($Errors);
} //isset($Errors)
$Errors = array();

if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i = 1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['DebtorNo'] = mb_strtoupper($_POST['DebtorNo']);

	$SQL = "SELECT COUNT(debtorno) FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0 and isset($_POST['New'])) {
		$InputError = 1;
		prnMsg(_('The customer number already exists in the database'), 'error');
		$Errors[$i] = 'DebtorNo';
		++$i;
	} //$MyRow[0] > 0 and isset($_POST['New'])
	elseif (mb_strlen($_POST['CustName']) > 40 or mb_strlen($_POST['CustName']) == 0) {
		$InputError = 1;
		prnMsg(_('The customer name must be entered and be forty characters or less long'), 'error');
		$Errors[$i] = 'CustName';
		++$i;
	} //mb_strlen($_POST['CustName']) > 40 or mb_strlen($_POST['CustName']) == 0
		elseif ($_SESSION['AutoDebtorNo'] == 0 and mb_strlen($_POST['DebtorNo']) == 0) {
		$InputError = 1;
		prnMsg(_('The debtor code cannot be empty'), 'error');
		$Errors[$i] = 'DebtorNo';
		++$i;
	} //$_SESSION['AutoDebtorNo'] == 0 and (ContainsIllegalCharacters($_POST['DebtorNo']) or mb_strpos($_POST['DebtorNo'], ' '))
		elseif (mb_strlen($_POST['Address1']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'Address1';
		++$i;
	} //mb_strlen($_POST['Address1']) > 40
		elseif (mb_strlen($_POST['Address2']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'Address2';
		++$i;
	} //mb_strlen($_POST['Address2']) > 40
		elseif (mb_strlen($_POST['Address3']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'Address3';
		++$i;
	} //mb_strlen($_POST['Address3']) > 40
		elseif (mb_strlen($_POST['Address4']) > 50) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be fifty characters or less long'), 'error');
		$Errors[$i] = 'Address4';
		++$i;
	} //mb_strlen($_POST['Address4']) > 50
		elseif (mb_strlen($_POST['Address5']) > 20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'), 'error');
		$Errors[$i] = 'Address5';
		++$i;
	} //mb_strlen($_POST['Address5']) > 20
		elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
		$InputError = 1;
		prnMsg(_('The credit limit must be numeric'), 'error');
		$Errors[$i] = 'CreditLimit';
		++$i;
	} //!is_numeric(filter_number_format($_POST['CreditLimit']))
		elseif (!is_numeric(filter_number_format($_POST['PymtDiscount']))) {
		$InputError = 1;
		prnMsg(_('The payment discount must be numeric'), 'error');
		$Errors[$i] = 'PymtDiscount';
		++$i;
	} //!is_numeric(filter_number_format($_POST['PymtDiscount']))
		elseif (!Is_Date($_POST['ClientSince'])) {
		$InputError = 1;
		prnMsg(_('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		$Errors[$i] = 'ClientSince';
		++$i;
	} //!Is_Date($_POST['ClientSince'])
		elseif (!is_numeric(filter_number_format($_POST['Discount']))) {
		$InputError = 1;
		prnMsg(_('The discount percentage must be numeric'), 'error');
		$Errors[$i] = 'Discount';
		++$i;
	} //!is_numeric(filter_number_format($_POST['Discount']))
		elseif (filter_number_format($_POST['CreditLimit']) < 0) {
		$InputError = 1;
		prnMsg(_('The credit limit must be a positive number'), 'error');
		$Errors[$i] = 'CreditLimit';
		++$i;
	} //filter_number_format($_POST['CreditLimit']) < 0
		elseif ((filter_number_format($_POST['PymtDiscount']) > 10) or (filter_number_format($_POST['PymtDiscount']) < 0)) {
		$InputError = 1;
		prnMsg(_('The payment discount is expected to be less than 10% and greater than or equal to 0'), 'error');
		$Errors[$i] = 'PymtDiscount';
		++$i;
	} //(filter_number_format($_POST['PymtDiscount']) > 10) or (filter_number_format($_POST['PymtDiscount']) < 0)
		elseif ((filter_number_format($_POST['Discount']) > 100) or (filter_number_format($_POST['Discount']) < 0)) {
		$InputError = 1;
		prnMsg(_('The discount is expected to be less than 100% and greater than or equal to 0'), 'error');
		$Errors[$i] = 'Discount';
		++$i;
	} //(filter_number_format($_POST['Discount']) > 100) or (filter_number_format($_POST['Discount']) < 0)

	if ($InputError != 1) {
		$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

		if (!isset($_POST['New'])) {
			$SQL = "SELECT count(id)
					  FROM debtortrans
					where debtorno = '" . $_POST['DebtorNo'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			if ($MyRow[0] == 0) {
				$SQL = "UPDATE debtorsmaster SET	name='" . $_POST['CustName'] . "',
												address1='" . $_POST['Address1'] . "',
												address2='" . $_POST['Address2'] . "',
												address3='" . $_POST['Address3'] . "',
												address4='" . $_POST['Address4'] . "',
												address5='" . $_POST['Address5'] . "',
												address6='" . $_POST['Address6'] . "',
												currcode='" . $_POST['CurrCode'] . "',
												clientsince='" . $SQL_ClientSince . "',
												holdreason='" . $_POST['HoldReason'] . "',
												paymentterms='" . $_POST['PaymentTerms'] . "',
												discount='" . filter_number_format($_POST['Discount']) / 100 . "',
												discountcode='" . $_POST['DiscountCode'] . "',
												pymtdiscount='" . filter_number_format($_POST['PymtDiscount']) / 100 . "',
												creditlimit='" . filter_number_format($_POST['CreditLimit']) . "',
												salestype = '" . $_POST['SalesType'] . "',
												invaddrbranch='" . $_POST['AddrInvBranch'] . "',
												taxref='" . $_POST['TaxRef'] . "',
												customerpoline='" . $_POST['CustomerPOLine'] . "',
												typeid='" . $_POST['typeid'] . "',
												language_id='" . $_POST['LanguageID'] . "'
											WHERE debtorno = '" . $_POST['DebtorNo'] . "'";
			} //$MyRow[0] == 0
			else {
				$CurrSQL = "SELECT currcode
					  		FROM debtorsmaster
							where debtorno = '" . $_POST['DebtorNo'] . "'";
				$CurrResult = DB_query($CurrSQL);
				$CurrRow = DB_fetch_array($CurrResult);
				$OldCurrency = $CurrRow[0];

				$SQL = "UPDATE debtorsmaster SET	name='" . $_POST['CustName'] . "',
												address1='" . $_POST['Address1'] . "',
												address2='" . $_POST['Address2'] . "',
												address3='" . $_POST['Address3'] . "',
												address4='" . $_POST['Address4'] . "',
												address5='" . $_POST['Address5'] . "',
												address6='" . $_POST['Address6'] . "',
												clientsince='" . $SQL_ClientSince . "',
												holdreason='" . $_POST['HoldReason'] . "',
												paymentterms='" . $_POST['PaymentTerms'] . "',
												discount='" . filter_number_format($_POST['Discount']) / 100 . "',
												discountcode='" . $_POST['DiscountCode'] . "',
												pymtdiscount='" . filter_number_format($_POST['PymtDiscount']) / 100 . "',
												creditlimit='" . filter_number_format($_POST['CreditLimit']) . "',
												salestype = '" . $_POST['SalesType'] . "',
												invaddrbranch='" . $_POST['AddrInvBranch'] . "',
												taxref='" . $_POST['TaxRef'] . "',
												customerpoline='" . $_POST['CustomerPOLine'] . "',
												typeid='" . $_POST['typeid'] . "',
												language_id='" . $_POST['LanguageID'] . "'
											WHERE debtorno = '" . $_POST['DebtorNo'] . "'";

				if ($OldCurrency != $_POST['CurrCode']) {
					prnMsg(_('The currency code cannot be updated as there are already transactions for this customer'), 'info');
				} //$OldCurrency != $_POST['CurrCode']
			}

			$ErrMsg = _('The customer could not be updated because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(_('Customer updated'), 'success');
			echo '<br />';

		} //!isset($_POST['New'])
		else { //it is a new customer

			/* set the DebtorNo if $AutoDebtorNo in config.php has been set to
			something greater 0 */
			if ($_SESSION['AutoDebtorNo'] > 0) {
				/* system assigned, sequential, numeric */
				if ($_SESSION['AutoDebtorNo'] == 1) {
					$_POST['DebtorNo'] = GetNextTransNo(500);
				} //$_SESSION['AutoDebtorNo'] == 1
			} //$_SESSION['AutoDebtorNo'] > 0

			$SQL = "INSERT INTO debtorsmaster (
							debtorno,
							name,
							address1,
							address2,
							address3,
							address4,
							address5,
							address6,
							currcode,
							clientsince,
							holdreason,
							paymentterms,
							discount,
							discountcode,
							pymtdiscount,
							creditlimit,
							salestype,
							invaddrbranch,
							taxref,
							customerpoline,
							typeid,
							language_id)
				VALUES ('" . $_POST['DebtorNo'] . "',
					'" . $_POST['CustName'] . "',
					'" . $_POST['Address1'] . "',
					'" . $_POST['Address2'] . "',
					'" . $_POST['Address3'] . "',
					'" . $_POST['Address4'] . "',
					'" . $_POST['Address5'] . "',
					'" . $_POST['Address6'] . "',
					'" . $_POST['CurrCode'] . "',
					'" . $SQL_ClientSince . "',
					'" . $_POST['HoldReason'] . "',
					'" . $_POST['PaymentTerms'] . "',
					'" . filter_number_format($_POST['Discount']) / 100 . "',
					'" . $_POST['DiscountCode'] . "',
					'" . filter_number_format($_POST['PymtDiscount']) / 100 . "',
					'" . filter_number_format($_POST['CreditLimit']) . "',
					'" . $_POST['SalesType'] . "',
					'" . $_POST['AddrInvBranch'] . "',
					'" . $_POST['TaxRef'] . "',
					'" . $_POST['CustomerPOLine'] . "',
					'" . $_POST['typeid'] . "',
					'" . $_POST['LanguageID'] . "'
					)";

			$ErrMsg = _('This customer could not be added because');
			$Result = DB_query($SQL, $ErrMsg);

			$BranchCode = mb_substr($_POST['DebtorNo'], 0, 4);

			echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/CustomerBranches.php?DebtorNo=' . urlencode($_POST['DebtorNo']) . '">';

			echo '<div class="centre">' . _('You should automatically be forwarded to the entry of a new Customer Branch page') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/CustomerBranches.php?DebtorNo=' . urlencode($_POST['DebtorNo']) . '"></a></div>';

			include('includes/footer.inc');
			exit;
		}
	} //$InputError != 1
	else {
		prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
	}

} //isset($_POST['submit'])
elseif (isset($_POST['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$SQL = "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['DebtorNo'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('This customer cannot be deleted because there are transactions that refer to it'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('transactions against this customer');

	} //$MyRow[0] > 0
	else {
		$SQL = "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete the customer record because orders have been created against it'), 'warn');
			echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('orders against this customer');
		} //$MyRow[0] > 0
		else {
			$SQL = "SELECT COUNT(*) FROM salesanalysis WHERE cust='" . $_POST['DebtorNo'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this customer record because sales analysis records exist for it'), 'warn');
				echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('sales analysis records against this customer');
			} else {
				// Check if there are any users that refer to this CUSTOMER code
				$SQL = "SELECT COUNT(*) FROM www_users WHERE www_users.customerid = '" . $_POST['DebtorNo'] . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0) {
					prnMsg(_('Cannot delete this customer because users exist that refer to it') . '. ' . _('Purge old users first'), 'warn');
					echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('users referring to this Branch/customer');
				} else {
						// Check if there are any contract that refer to this branch code
					$SQL = "SELECT COUNT(*) FROM contracts WHERE contracts.debtorno = '" . $_POST['DebtorNo'] . "'";

					$Result = DB_query($SQL);
					$MyRow = DB_fetch_row($Result);

					if ($MyRow[0] > 0) {
						prnMsg(_('Cannot delete this customer because contracts have been created that refer to it') . '. ' . _('Purge old contracts first'), 'warn');
						echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('contracts referring to this customer');
					}
				} //$MyRow[0] > 0
			}
		}

	}
	if ($CancelDelete == 0) { //ie not cancelled the delete as a result of above tests
		$SQL = "DELETE FROM custbranch WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL, $ErrMsg);
		$SQL = "DELETE FROM custcontacts WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL);
		$SQL = "DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL);
		prnMsg( _('Customer') . ' ' . $_POST['DebtorNo'] . ' ' . _('has been deleted - together with all the associated branches and contacts'),'success');
		include('includes/footer.inc');
		unset($_SESSION['CustomerID']);
		exit;
	} //end if Delete Customer
} //isset($_POST['delete'])

if (isset($_POST['Reset'])) {
	unset($_POST['CustName']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['HoldReason']);
	unset($_POST['PaymentTerms']);
	unset($_POST['Discount']);
	unset($_POST['DiscountCode']);
	unset($_POST['PymtDiscount']);
	unset($_POST['CreditLimit']);
	// Leave Sales Type set so as to faciltate fast customer setup
	//	unset($_POST['SalesType']);
	unset($_POST['DebtorNo']);
	unset($_POST['InvAddrBranch']);
	unset($_POST['TaxRef']);
	unset($_POST['CustomerPOLine']);
	unset($_POST['LanguageID']);
	// Leave Type ID set so as to faciltate fast customer setup
	//	unset($_POST['typeid']);
} //isset($_POST['Reset'])

/*DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['DebtorNo'])) {
	$DebtorNo = stripslashes($_POST['DebtorNo']);
} //isset($_POST['DebtorNo'])
elseif (isset($_GET['DebtorNo'])) {
	$DebtorNo = stripslashes($_GET['DebtorNo']);
} //isset($_GET['DebtorNo'])
if (isset($_POST['ID'])) {
	$ID = $_POST['ID'];
} //isset($_POST['ID'])
elseif (isset($_GET['ID'])) {
	$ID = $_GET['ID'];
} //isset($_GET['ID'])
else {
	$ID = '';
}
if (isset($_POST['ws'])) {
	$ws = $_POST['ws'];
} //isset($_POST['ws'])
elseif (isset($_GET['ws'])) {
	$ws = $_GET['ws'];
} //isset($_GET['ws'])
if (isset($_POST['Edit'])) {
	$Edit = $_POST['Edit'];
} //isset($_POST['Edit'])
elseif (isset($_GET['Edit'])) {
	$Edit = $_GET['Edit'];
} //isset($_GET['Edit'])
else {
	$Edit = '';
}

if (isset($_POST['Add'])) {
	$Add = $_POST['Add'];
} //isset($_POST['Add'])
elseif (isset($_GET['Add'])) {
	$Add = $_GET['Add'];
} //isset($_GET['Add'])

if (isset($_POST['AddContact']) and (isset($_POST['AddContact']) != '')) {
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/AddCustomerContacts.php?DebtorNo=' . $DebtorNo . '">';
} //isset($_POST['AddContact']) and (isset($_POST['AddContact']) != '')

if (!isset($DebtorNo)) {
	/*If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field*/

	/* First check that all the necessary items have been setup */

	$SetupErrors = 0; //Count errors
	$SQL = "SELECT COUNT(typeabbrev)
				FROM salestypes";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] == 0) {
		prnMsg(_('In order to create a new customer you must first set up at least one sales type/price list') . '<br />' . _('Click') . ' ' . '<a target="_blank" href="' . $RootPath . '/SalesTypes.php">' . _('here') . ' ' . '</a>' . _('to set up your price lists'), 'warning') . '<br />';
		$SetupErrors += 1;
	} //$MyRow[0] == 0
	$SQL = "SELECT COUNT(typeid)
				FROM debtortype";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] == 0) {
		prnMsg(_('In order to create a new customer you must first set up at least one customer type') . '<br />' . _('Click') . ' ' . '<a target="_blank" href="' . $RootPath . '/CustomerTypes.php">' . _('here') . ' ' . '</a>' . _('to set up your customer types'), 'warning');
		$SetupErrors += 1;
	} //$MyRow[0] == 0

	if ($SetupErrors > 0) {
		echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" >' . _('Click here to continue') . '</a></div>';
		include('includes/footer.inc');
		exit;
	} //$SetupErrors > 0
	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="New" value="Yes" />';

	$DataError = 0;

	echo '<table class="selection" cellspacing="4">
			<tr><td valign="top"><table class="selection">';

	/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
	then provide an input box for the DebtorNo to manually assigned */
	if ($_SESSION['AutoDebtorNo'] == 0) {
		echo '<tr><td>' . _('Customer Code') . ':</td><td><input tabindex="1" type="text" name="DebtorNo" size="11" required="required" minlength="1" maxlength="10" /></td></tr>';
	} //$_SESSION['AutoDebtorNo'] == 0

	echo '<tr>
			<td>' . _('Customer Name') . ':</td>
			<td><input tabindex="2" type="text" name="CustName" size="42" required="required" minlength="1" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 1 (Street)') . ':</td>
			<td><input tabindex="3" type="text" name="Address1" size="42" minlength="0" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 2 (Street)') . ':</td>
			<td><input tabindex="4" type="text" name="Address2" size="42" minlength="0" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 3 (Suburb/City)') . ':</td>
			<td><input tabindex="5" type="text" name="Address3" size="42" minlength="0" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 4 (State/Province)') . ':</td>
			<td><input tabindex="6" type="text" name="Address4" size="42" minlength="0" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 5 (Postal Code)') . ':</td>
			<td><input tabindex="7" type="text" name="Address5" size="22" minlength="0" maxlength="20" /></td>
		</tr>';

	if (!isset($_POST['Address6'])) {
		$_POST['Address6'] = $CountriesArray[$_SESSION['CountryOfOperation']];
	}

	echo '<tr>
			<td>' . _('Country') . ':</td>
			<td><select minlength="0" name="Address6">';
	foreach ($CountriesArray as $CountryEntry => $CountryName) {
		if (isset($_POST['Address6']) and (strtoupper($_POST['Address6']) == strtoupper($CountryName))) {
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
		} //!isset($_POST['Address6']) and $CountryName == ""
		else {
			echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
		}
	} //$CountriesArray as $CountryEntry => $CountryName
	echo '</select></td>
		</tr>';

	// Show Sales Type drop down list
	$Result = DB_query("SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type");
	if (DB_num_rows($Result) == 0) {
		$DataError = 1;
		echo '<tr><td colspan="2">' . prnMsg(_('No sales types/price lists defined'), 'error') . '<br /><a href="SalesTypes.php" target="_parent">' . _('Setup Types') . '</a></td></tr>';
	} //DB_num_rows($Result) == 0
	else {
		echo '<tr><td>' . _('Sales Type') . '/' . _('Price List') . ':</td>
			   <td><select minlength="0" tabindex="9" name="SalesType">';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
		} //end while loopre
		DB_data_seek($Result, 0);
		echo '</select></td></tr>';
	}

	// Show Customer Type drop down list
	$Result = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	if (DB_num_rows($Result) == 0) {
		$DataError = 1;
		echo '<a href="SalesTypes.php" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr>
				<td colspan="2">' . prnMsg(_('No Customer types/price lists defined'), 'error') . '</td>
			</tr>';
	} //DB_num_rows($Result) == 0
	else {
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td><select minlength="0" tabindex="9" name="typeid">';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($Result, 0);
		echo '</select></td></tr>';
	}

	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr>
			<td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
			<td><input tabindex="10" type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ClientSince" value="' . $DateString . '" size="12" minlength="0" maxlength="10" /></td></tr>';

	echo '</table></td>
			<td><table class="selection">
				<tr>
					<td>' . _('Discount Percent') . ':</td>
					<td><input tabindex="11" type="text" class="number" name="Discount" value="0" size="5" minlength="0" maxlength="4" /></td>
				</tr>
				<tr>
					<td>' . _('Discount Code') . ':</td>
					<td><input tabindex="12" type="text" name="DiscountCode" size="3" minlength="0" maxlength="2" /></td>
				</tr>
				<tr>
					<td>' . _('Payment Discount Percent') . ':</td>
					<td><input tabindex="13" type="text" class ="number" name="PymtDiscount" value="0" size="5" minlength="0" maxlength="4" /></td>
				</tr>
				<tr>
					<td>' . _('Credit Limit') . ':</td>
					<td><input tabindex="14" type="text" class="number" name="CreditLimit" value="' . locale_number_format($_SESSION['DefaultCreditLimit'], 0) . '" size="16" minlength="0" maxlength="14" /></td>
				</tr>
				<tr>
					<td>' . _('Tax Reference') . ':</td>
					<td><input tabindex="15" type="text" name="TaxRef" size="22" minlength="0" maxlength="20" /></td>
				</tr>';

	$Result = DB_query("SELECT terms, termsindicator FROM paymentterms");
	if (DB_num_rows($Result) == 0) {
		$DataError = 1;
		echo '<tr><td colspan="2">' . prnMsg(_('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'), 'error') . '</td></tr>';
	} //DB_num_rows($Result) == 0
	else {
		echo '<tr>
				<td>' . _('Payment Terms') . ':</td>
				<td><select minlength="0" tabindex="15" name="PaymentTerms">';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="' . $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
		} //end while loop
		DB_data_seek($Result, 0);

		echo '</select></td></tr>';
	}
	echo '<tr>
			<td>' . _('Credit Status') . ':</td>
			<td><select minlength="0" tabindex="16" name="HoldReason">';

	$Result = DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
	if (DB_num_rows($Result) == 0) {
		$DataError = 1;
		echo '<tr>
				<td colspan="2">' . prnMsg(_('There are no credit statuses currently defined - go to the setup tab of the main menu and set at least one up first'), 'error') . '</td>
			</tr>';
	} //DB_num_rows($Result) == 0
	else {
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="' . $MyRow['reasoncode'] . '">' . $MyRow['reasondescription'] . '</option>';
		} //end while loop
		DB_data_seek($Result, 0);
		echo '</select></td></tr>';
	}

	$Result = DB_query("SELECT currency, currabrev FROM currencies");
	if (DB_num_rows($Result) == 0) {
		$DataError = 1;
		echo '<tr>
				<td colspan="2">' . prnMsg(_('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'), 'error') . '</td>
			</tr>';
	} //DB_num_rows($Result) == 0
	else {
		if (!isset($_POST['CurrCode'])) {
			$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
		} //!isset($_POST['CurrCode'])
		echo '<tr>
				<td>' . _('Customer Currency') . ':</td>
				<td><select minlength="0" tabindex="17" name="CurrCode">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['CurrCode'] == $MyRow['currabrev']) {
				echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			} //$_POST['CurrCode'] == $MyRow['currabrev']
			else {
				echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result, 0);

		echo '</select></td>
			</tr>';
	}

	echo '<tr>
			<td>' . _('Language') . ':</td>
			<td><select minlength="0" name="LanguageID">';

	if (!isset($_POST['LanguageID']) OR $_POST['LanguageID'] == '') {
		$_POST['LanguageID'] = $_SESSION['Language'];
	}

	foreach ($LanguagesArray as $LanguageCode => $LanguageName) {
		if ($_POST['LanguageID'] == $LanguageCode) {
			echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName'] . '</option>';
		} else {
			echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName'] . '</option>';
		}
	}
	echo '</select></td>
			</tr>';

	echo '<tr>
			<td>' . _('Customer PO Line on SO') . ':</td>
			<td><select minlength="0" tabindex="18" name="CustomerPOLine">
				<option selected="selected" value="0">' . _('No') . '</option>
				<option value="1">' . _('Yes') . '</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>' . _('Invoice Addressing') . ':</td>
			<td><select minlength="0" tabindex="19" name="AddrInvBranch">
				<option selected="selected" value="0">' . _('Address to HO') . '</option>
				<option value="1">' . _('Address to Branch') . '</option>
				</select>
			</td>
		</tr>
		</table></td>
		</tr>
		</table>';
	if ($DataError == 0) {
		echo '<br />
			<div class="centre">
				<input tabindex="20" type="submit" name="submit" value="' . _('Add New Customer') . '" />&nbsp;<input tabindex="21" type="submit" value="' . _('Reset') . '" />
			</div>';

	} //$DataError == 0
	echo '</form>';

} //!isset($DebtorNo)
else {
	//DebtorNo exists - either passed when calling the form or from the form itself

	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr><td valign="top">';

	if (!isset($_POST['New'])) {
		$SQL = "SELECT debtorno,
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
						holdreason,
						paymentterms,
						discount,
						discountcode,
						pymtdiscount,
						creditlimit,
						invaddrbranch,
						taxref,
						customerpoline,
						typeid,
						language_id
				FROM debtorsmaster
				WHERE debtorno = '" . $DebtorNo . "'";

		$ErrMsg = _('The customer details could not be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);

		$MyRow = DB_fetch_array($Result);
		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then display the DebtorNo */
		$_POST['CustName'] = $MyRow['name'];
		$_POST['Address1'] = $MyRow['address1'];
		$_POST['Address2'] = $MyRow['address2'];
		$_POST['Address3'] = $MyRow['address3'];
		$_POST['Address4'] = $MyRow['address4'];
		$_POST['Address5'] = $MyRow['address5'];
		$_POST['Address6'] = $MyRow['address6'];
		$_POST['SalesType'] = $MyRow['salestype'];
		$_POST['CurrCode'] = $MyRow['currcode'];
		$_POST['ClientSince'] = ConvertSQLDate($MyRow['clientsince']);
		$_POST['HoldReason'] = $MyRow['holdreason'];
		$_POST['PaymentTerms'] = $MyRow['paymentterms'];
		$_POST['Discount'] = locale_number_format($MyRow['discount'] * 100, 2);
		$_POST['DiscountCode'] = $MyRow['discountcode'];
		$_POST['PymtDiscount'] = locale_number_format($MyRow['pymtdiscount'] * 100, 2);
		$_POST['CreditLimit'] = locale_number_format($MyRow['creditlimit'], 0);
		$_POST['InvAddrBranch'] = $MyRow['invaddrbranch'];
		$_POST['TaxRef'] = $MyRow['taxref'];
		$_POST['CustomerPOLine'] = $MyRow['customerpoline'];
		$_POST['typeid'] = $MyRow['typeid'];
		$_POST['LanguageID'] = $MyRow['language_id'];

		echo '<input type="hidden" name="DebtorNo" value="' . $DebtorNo . '" />';
		echo '<table class="selection">';
		if ($_SESSION['AutoDebtorNo'] == 0) {
			echo '<tr>
					<td>' . _('Customer Code') . ':</td>
					<td>' . stripslashes($DebtorNo) . '</td>
				</tr>';
		} //$_SESSION['AutoDebtorNo'] == 0

	} //!isset($_POST['New'])
	else {
		// its a new customer being added
		echo '<input type="hidden" name="New" value="Yes" />';
		echo '<table class="selection">';

		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then provide an input box for the DebtorNo to manually assigned */
		if ($_SESSION['AutoDebtorNo'] == 0) {
			echo '<tr>
					<td>' . _('Customer Code') . ':</td>
					<td><input type="text" name="DebtorNo" value="' . $DebtorNo . '" size="12" required="required" minlength="1" maxlength="10" /></td></tr>';
		} //$_SESSION['AutoDebtorNo'] == 0
	}
	if (isset($_GET['Modify'])) {
		echo '<tr>
				<td>' . _('Customer Name') . ':</td>
				<td>' . $_POST['CustName'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 1') . ':</td>
				<td>' . $_POST['Address1'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 2 (Street)') . ':</td>
				<td>' . $_POST['Address2'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 3 (Suburb/City)') . ':</td>
				<td>' . $_POST['Address3'] . '</td>
			</tr>
			<tr>
				 <td>' . _('Address Line 4 (State/Province)') . ':</td>
				<td>' . $_POST['Address4'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 5 (Postal Code)') . ':</td>
				<td>' . $_POST['Address5'] . '</td>
			</tr>';
		echo '<tr>
				<td>' . _('Country') . ':</td>
				<td>' . $_POST['Address6'] . '</td>
			</tr>';
	} else {
		echo '<tr>
				<td>' . _('Customer Name') . ':</td>
				<td><input type="text" name="CustName" value="' . $_POST['CustName'] . '" size="42" required="required" minlength="1" maxlength="40" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 1 (Street)') . ':</td>
				<td><input type="text" name="Address1" size="42" minlength="0" maxlength="40" value="' . $_POST['Address1'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 2 (Street)') . ':</td>
				<td><input type="text" name="Address2" size="42" minlength="0" maxlength="40" value="' . $_POST['Address2'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 3 (Suburb/City)') . ':</td>
				<td><input type="text" name="Address3" size="42" minlength="0" maxlength="40" value="' . $_POST['Address3'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 4 (State/Province)') . ':</td>
				<td><input type="text" name="Address4" size="42" minlength="0" maxlength="40" value="' . $_POST['Address4'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 5 (Postal Code)') . ':</td>
				<td><input type="text" name="Address5" size="42" minlength="0" maxlength="40" value="' . $_POST['Address5'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Country') . ':</td>
				<td><select minlength="0" name="Address6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName) {
			if (isset($_POST['Address6']) and (strtoupper($_POST['Address6']) == strtoupper($CountryName))) {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} //isset($_POST['Address6']) and ($_POST['Address6'] == $CountryName)
			elseif (!isset($_POST['Address6']) and $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} //!isset($_POST['Address6']) and $CountryName == ""
			else {
				echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
			}
		} //$CountriesArray as $CountryEntry => $CountryName
		echo '</select></td>
			</tr>';

	}
	// Select sales types for drop down list
	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'");
		$MyRow = DB_fetch_array($Result);
		echo '<tr><td>' . _('Sales Type') . ':</td><td>' . $MyRow['sales_type'] . '</td></tr>';
	} else {
		$Result = DB_query("SELECT typeabbrev, sales_type FROM salestypes");
		echo '<tr><td>' . _('Sales Type') . '/' . _('Price List') . ':</td>
			<td><select minlength="0" name="SalesType">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['SalesType'] == $MyRow['typeabbrev']) {
				echo '<option selected="selected" value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
			} //$_POST['SalesType'] == $MyRow['typeabbrev']
			else {
				echo '<option value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result, 0);
		echo '</select></td></tr>';
	}

	// Select Customer types for drop down list for SELECT/UPDATE
	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT typename FROM debtortype WHERE typeid='" . $_POST['typeid'] . "'");
		$MyRow = DB_fetch_array($Result);
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td>' . $MyRow['typename'] . '</td>
			</tr>';
	} else {
		$Result = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td><select minlength="0" name="typeid">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['typeid'] == $MyRow['typeid']) {
				echo '<option selected="selected" value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
			} //$_POST['typeid'] == $MyRow['typeid']
			else {
				echo '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result, 0);
	}

	if (isset($_GET['Modify'])) {
		echo '</select></td></tr>
			<tr><td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
				<td>' . $_POST['ClientSince'] . '</td></tr>';

		echo '</table></td>
				<td><table class="selection">';

		echo '<tr>
				<td>' . _('Discount Percent') . ':</td>
				<td>' . $_POST['Discount'] . '</td>
			</tr>
			<tr>
				<td>' . _('Discount Code') . ':</td>
				<td>' . $_POST['DiscountCode'] . '</td>
			</tr>
			<tr>
				<td>' . _('Payment Discount Percent') . ':</td>
				<td>' . $_POST['PymtDiscount'] . '</td>
			</tr>
			<tr>
				<td>' . _('Credit Limit') . ':</td>
				<td>' . $_POST['CreditLimit'] . '</td>
			</tr>
			<tr>
				<td>' . _('Tax Reference') . ':</td>
				<td>' . $_POST['TaxRef'] . '</td>
			</tr>';
	} //isset($_GET['Modify'])
	else {
		echo '</select></td>
			</tr>
			<tr>
				<td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
				<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ClientSince" size="11" minlength="0" maxlength="10" value="' . $_POST['ClientSince'] . '" /></td>
			</tr>
			</table></td>
			<td><table class="selection">';

		echo '<tr>
				<td>' . _('Discount Percent') . ':</td>
				<td><input type="text" name="Discount" class="number" size="5" minlength="0" maxlength="4" value="' . $_POST['Discount'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Discount Code') . ':</td>
				<td><input type="text" name="DiscountCode" size="3" minlength="0" maxlength="2" value="' . $_POST['DiscountCode'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Payment Discount Percent') . ':</td>
				<td><input type="text" class="number" name="PymtDiscount" size="5" minlength="0" maxlength="4" value="' . $_POST['PymtDiscount'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Credit Limit') . ':</td>
				<td><input type="text" class="number" name="CreditLimit" size="16" minlength="0" maxlength="14" value="' . $_POST['CreditLimit'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Tax Reference') . ':</td>
				<td><input type="text" name="TaxRef" size="22" minlength="0" maxlength="20"  value="' . $_POST['TaxRef'] . '" /></td>
			</tr>';
	}

	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT terms FROM paymentterms WHERE termsindicator='" . $_POST['PaymentTerms'] . "'");
		$MyRow = DB_fetch_array($Result);
		echo '<tr>
				<td>' . _('Payment Terms') . ':</td>
				<td>' . $MyRow['terms'] . '</td>
			</tr>';
	} //isset($_GET['Modify'])
	else {
		$Result = DB_query("SELECT terms, termsindicator FROM paymentterms");
		echo '<tr>
				<td>' . _('Payment Terms') . ':</td>
				<td><select minlength="0" name="PaymentTerms">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['PaymentTerms'] == $MyRow['termsindicator']) {
				echo '<option selected="selected" value="' . $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
			} //$_POST['PaymentTerms'] == $MyRow['termsindicator']
			else {
				echo '<option value="' . $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result, 0);
		echo '</select></td>
			</tr>';
	}

	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT reasondescription FROM holdreasons WHERE reasoncode='" . $_POST['HoldReason'] . "'");
		$MyRow = DB_fetch_array($Result);
		echo '<tr>
				<td>' . _('Credit Status') . ':</td>
				<td>' . $MyRow['reasondescription'] . '</td>
			</tr>';
	} //isset($_GET['Modify'])
	else {
		$Result = DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
		echo '<tr>
				<td>' . _('Credit Status') . ':</td>
				<td><select minlength="0" name="HoldReason">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['HoldReason'] == $MyRow['reasoncode']) {
				echo '<option selected="selected" value="' . $MyRow['reasoncode'] . '">' . $MyRow['reasondescription'] . '</option>';
			} //$_POST['HoldReason'] == $MyRow['reasoncode']
			else {
				echo '<option value="' . $MyRow['reasoncode'] . '">' . $MyRow['reasondescription'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result, 0);
		echo '</select></td>
			</tr>';
	}

	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT currency FROM currencies WHERE currabrev='" . $_POST['CurrCode'] . "'");
		$MyRow = DB_fetch_array($Result);
		echo '<tr>
				<td>' . _('Customer Currency') . ':</td>
				<td>' . _($MyRow['currency']) . '</td></tr>';
	} else {
		$Result = DB_query("SELECT currency, currabrev FROM currencies");
		echo '<tr>
				<td>' . _('Customer Currency') . ':</td>
				<td><select minlength="0" name="CurrCode">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['CurrCode'] == $MyRow['currabrev']) {
				echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			} //$_POST['CurrCode'] == $MyRow['currabrev']
			else {
				echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result, 0);
		echo '</select></td>
			</tr>';
	}

	if (!isset($_POST['LanguageID']) or $_POST['LanguageID'] == ''){
		$_POST['LanguageID'] = $_SESSION['Language'];
	}

	if (isset($_GET['Modify'])) {
		echo '<tr>
				<td>' . _('Language') . ':</td>';
		foreach ($LanguagesArray as $LanguageCode => $LanguageName){
			if ($_POST['LanguageID'] == $LanguageCode){
				echo '<td>' . $LanguageName['LanguageName'];
			}
		}
		echo '</td>
		</tr>';
	} else {
		echo '<tr>
				<td>' . _('Language') . ':</td>
				<td><select name="LanguageID" required="required">';
		foreach ($LanguagesArray as $LanguageCode => $LanguageName){
			if ($_POST['LanguageID'] == $LanguageCode){
				echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			} else {
				echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			}
		}
		echo '</select></td>
		</tr>';
	}
	echo '<tr>
			<td>' . _('Require Customer PO Line on SO') . ':</td>';
	if (isset($_GET['Modify'])) {
		if ($_POST['CustomerPOLine'] == 0) {
			echo '<td>' . _('No') . '</td>';
		} //$_POST['CustomerPOLine'] == 0
		else {
			echo '<td>' . _('Yes') . '</td>';
		}
	} else {
		echo '<td><select minlength="0" name="CustomerPOLine">';
		if ($_POST['CustomerPOLine'] == 0) {
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
			echo '<option value="1">' . _('Yes') . '</option>';
		} //$_POST['CustomerPOLine'] == 0
		else {
			echo '<option value="0">' . _('No') . '</option>';
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		}
		echo '</select></td>';
	}
	echo '</tr>';

	if (isset($_GET['Modify'])) {
		if ($_POST['InvAddrBranch'] == 0) {
			echo '<tr>
					<td>' . _('Invoice Addressing') . ':</td>
					<td>' . _('Address to HO') . '</td>
				</tr>';
		} //$_POST['CustomerPOLine'] == 0
		else {
			echo '<tr>
					<td>' . _('Invoice Addressing') . ':</td>
					<td>' . _('Address to Branch') . '</td>
				</tr>';
		}
	} //isset($_GET['Modify'])
	else {
		echo '<tr>
				<td>' . _('Invoice Addressing') . ':</td>
				<td><select minlength="0" name="AddrInvBranch">';
		if (isset($_POST['InvAddrBranch']) and $_POST['InvAddrBranch'] == 0) {
			echo '<option selected="selected" value="0">' . _('Address to HO') . '</option>';
			echo '<option value="1">' . _('Address to Branch') . '</option>';
		} //$_POST['InvAddrBranch'] == 0
		else {
			echo '<option value="0">' . _('Address to HO') . '</option>';
			echo '<option selected="selected" value="1">' . _('Address to Branch') . '</option>';
		}
	}

	echo '</select></td>
		</tr>
		</table></td>
		</tr>
		<tr><td colspan="2">';

	if (isset($_GET['delete'])) { //User hit delete link on customer contacts

		/*Process this first before showing remaining contacts */
		$Resultupcc = DB_query("DELETE FROM custcontacts
								WHERE debtorno='" . $DebtorNo . "'
								AND contid='" . $ID . "'");
		prnMsg(_('Contact Deleted'), 'success');
	} //isset($_GET['delete'])

	$SQL = "SELECT contid,
					debtorno,
					contactname,
					role,
					phoneno,
					notes,
					email
			FROM custcontacts
			WHERE debtorno='" . $DebtorNo . "'
			ORDER BY contid";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	if (isset($_GET['Modify'])) {
		echo '<tr>
				<th>' . _('Name') . '</th>
				<th>' . _('Role') . '</th>
				<th>' . _('Phone Number') . '</th>
				<th>' . _('Email') . '</th>
				<th>' . _('Notes') . '</th>
			</tr>';
	} //isset($_GET['Modify'])
	else {
		echo '<tr>
				<th>' . _('Name') . '</th>
				<th>' . _('Role') . '</th>
				<th>' . _('Phone Number') . '</th>
				<th>' . _('Email') . '</th>
				<th>' . _('Notes') . '</th>
				<th>' . _('Edit') . '</th>
				<th colspan="2"><input type="submit" name="AddContact" value="' . _('Add Contact') . '" /></th>
			</tr>';
	}
	$k = 0; //row colour counter

	while ($MyRow = DB_fetch_array($Result)) {
		if ($k == 1) {
			echo '<tr class="OddTableRows">';
			$k = 0;
		} //$k == 1
		else {
			echo '<tr class="EvenTableRows">';
			$k = 1;
		}

		if (isset($_GET['Modify'])) {
			echo '<td>' . $MyRow['contactname'] . '</td>
					<td>' . $MyRow['role'] . '</td>
					<td>' . $MyRow['phoneno'] . '</td>
					<td><a href="mailto:' . $MyRow['email'] . '">' . $MyRow['email'] . '</a></td>
					<td>' . $MyRow['notes'] . '</td>
				</tr>';
		} //isset($_GET['Modify'])
		else {
			echo '<td>' . $MyRow['contactname'] . '</td>
					<td>' . $MyRow['role'] . '</td>
					<td>' . $MyRow['phoneno'] . '</td>
					<td><a href="mailto:' . $MyRow['email'] . '">' . $MyRow['email'] . '</a></td>
					<td>' . $MyRow['notes'] . '</td>
					<td><a href="AddCustomerContacts.php?Id=' . urlencode($MyRow['contid']) . '&amp;DebtorNo=' . urlencode($MyRow['debtorno']) . '">' . _('Edit') . '</a></td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?ID=' . urlencode($MyRow['contid']) . '&amp;DebtorNo=' . urlencode($MyRow['debtorno']) . '&amp;delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this customer contact?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
				</tr>';
		}
	} //END WHILE LIST LOOP
	echo '</table>';

	echo '</td></tr></table>';

	if (isset($_POST['New']) and $_POST['New']) {
		echo '<div class="centre">
				<input type="submit" name="submit" value="' . _('Add New Customer') . '" />&nbsp;
				<input type="submit" name="Reset" value="' . _('Reset') . '" />
			</div>';
	} //isset($_POST['New']) and $_POST['New']
	elseif (!isset($_GET['Modify'])) {
		echo '<div class="centre">
				<input type="submit" name="submit" value="' . _('Update Customer') . '" />&nbsp;
				<input type="submit" name="delete" value="' . _('Delete Customer') . '" onclick="return MakeConfirm(\'' . _('Are You Sure?') . '\');" />
			</div>';
	} //!isset($_GET['Modify'])

	echo '</form>';
} // end of main ifs

include('includes/footer.inc');
?>