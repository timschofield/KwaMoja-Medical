<?php

include('includes/session.inc');
$Title = _('Bank Transactions Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'DailyBankTransactions';
include('includes/header.inc');

echo '<p class="page_title_text noPrint" > <img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '</p>';

if (!isset($_POST['Show'])) {
	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection" summary="' . _('Select criteria for inquiry') . '">';

	$SQL = "SELECT 	bankaccountname,
					bankaccounts.accountcode,
					bankaccounts.currcode
				FROM bankaccounts
				INNER JOIN chartmaster
					ON bankaccounts.accountcode=chartmaster.accountcode
				INNER JOIN bankaccountusers
					ON bankaccounts.accountcode=bankaccountusers.accountcode
				WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] ."'";

	$ErrMsg = _('The bank accounts could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank accounts was');
	$AccountsResults = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<tr>
			<td>' . _('Bank Account') . ':</td>
			<td><select minlength="0" name="BankAccount">';

	if (DB_num_rows($AccountsResults) == 0) {
		echo '</select></td>
				</tr></table>';
		prnMsg(_('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'), 'warn');
		include('includes/footer.inc');
		exit;
	} else {
		while ($MyRow = DB_fetch_array($AccountsResults)) {
			/*list the bank account names */
			if (!isset($_POST['BankAccount']) and $MyRow['currcode'] == $_SESSION['CompanyRecord']['currencydefault']) {
				$_POST['BankAccount'] = $MyRow['accountcode'];
			}
			if (isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']) {
				echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '<tr>
			<td>' . _('Transactions Dated From') . ':</td>
			<td><input type="text" name="FromTransDate" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" required="required" minlength="1" maxlength="10" size="11" onchange="isDate(this, this.value, ' . "'" . $_SESSION['DefaultDateFormat'] . "'" . ')" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>
		<tr>
			<td>' . _('Transactions Dated To') . ':</td>
			<td><input type="text" name="ToTransDate" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" required="required" minlength="1" maxlength="10" size="11" onchange="isDate(this, this.value, ' . "'" . $_SESSION['DefaultDateFormat'] . "'" . ')" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>
		<tr>
			<td>' . _('Show Transactions') . '</td>
			<td>
				<select minlength="0" name="ShowType">
					<option value="All">' . _('All') . '</option>
					<option value="Unmatched">' . _('Unmatched') . '</option>
					<option value="Matched">' . _('Matched') . '</option>
				</select>
			</td>
		</tr>
		</table>
		<div class="centre">
			<input type="submit" name="Show" value="' . _('Show transactions') . '" />
		</div>
		</form>';
} else {
	$SQL = "SELECT 	bankaccountname,
					bankaccounts.currcode,
					currencies.decimalplaces
			FROM bankaccounts
			INNER JOIN currencies
				ON bankaccounts.currcode = currencies.currabrev
			WHERE bankaccounts.accountcode='" . $_POST['BankAccount'] . "'";
	$BankResult = DB_query($SQL, _('Could not retrieve the bank account details'));


	$SQL = "SELECT 	banktrans.currcode,
					banktrans.amount,
					banktrans.amountcleared,
					banktrans.functionalexrate,
					banktrans.exrate,
					banktrans.banktranstype,
					banktrans.transdate,
					banktrans.transno,
					banktrans.ref,
					banktrans.chequeno,
					bankaccounts.bankaccountname,
					systypes.typename,
					systypes.typeid
				FROM banktrans
				INNER JOIN bankaccounts
				ON banktrans.bankact=bankaccounts.accountcode
				INNER JOIN systypes
				ON banktrans.type=systypes.typeid
				WHERE bankact='" . $_POST['BankAccount'] . "'
					AND transdate>='" . FormatDateForSQL($_POST['FromTransDate']) . "'
					AND transdate<='" . FormatDateForSQL($_POST['ToTransDate']) . "'
				ORDER BY banktrans.transdate";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	} else {
		$BankDetailRow = DB_fetch_array($BankResult);
		echo '<table class="selection" summary="' . _('Account Transactions For') . ' ' . $BankDetailRow['bankaccountname'] . ' ' . _('Between') . ' ' . $_POST['FromTransDate'] . ' ' . _('and') . ' ' . $_POST['ToTransDate'] . '">
				<tr>
					<th colspan="10">
						<h3>' . _('Account Transactions For') . ' ' . $BankDetailRow['bankaccountname'] . ' ' . _('Between') . ' ' . $_POST['FromTransDate'] . ' ' . _('and') . ' ' . $_POST['ToTransDate'] . '
							<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon noPrint" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
						</h3>
					</th>
				</tr>
				<tr>
					<th>' . ('Date') . '</th>
					<th>' . _('Transaction type') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Type') . '</th>
					<th>' . _('Reference') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Amount in') . ' ' . $BankDetailRow['currcode'] . '</th>
					<th>' . _('Running Total') . ' ' . $BankDetailRow['currcode'] . '</th>
					<th>' . _('Amount in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
					<th>' . _('Running Total') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
					<th>' . _('Matched') . '</th>
				</tr>';

		$AccountCurrTotal = 0;
		$LocalCurrTotal = 0;

		while ($MyRow = DB_fetch_array($Result)) {

			$AccountCurrTotal += $MyRow['amount'];
			$LocalCurrTotal += $MyRow['amount'] / $MyRow['functionalexrate'] / $MyRow['exrate'];

			if ($MyRow['amount'] == $MyRow['amountcleared']) {
				$Matched = _('Yes');
			} else {
				$Matched = _('No');
			}

			if ($_POST['ShowType'] == 'All' or ($_POST['ShowType'] == 'Unmatched' and $Matched == _('No')) or ($_POST['ShowType'] == 'Matched' and $Matched == _('Yes'))) {
				echo '<tr>
						<td>' . ConvertSQLDate($MyRow['transdate']) . '</td>
						<td>' . _($MyRow['typename']) . '</td>
						<td class="number"><a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['typeid'] . '&amp;TransNo=' . $MyRow['transno'] . '">' . $MyRow['transno'] . '</a></td>
						<td>' . $MyRow['banktranstype'] . '</td>
						<td>' . $MyRow['ref'] . '</td>
						<td>' . $MyRow['chequeno'] . '</td>
						<td class="number">' . locale_number_format($MyRow['amount'], $BankDetailRow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($AccountCurrTotal, $BankDetailRow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['amount'] / $MyRow['functionalexrate'] / $MyRow['exrate'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($LocalCurrTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . $Matched . '</td>
					</tr>';
			}
		}
		echo '</table>';
	} //end if no bank trans in the range to show

	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><div class="centre"><input type="submit" name="Return" value="' . _('Select Another Date') . '" /></div>';
	echo '</div>';
	echo '</form>';
}
include('includes/footer.inc');

?>