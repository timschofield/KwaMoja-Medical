<?php

include('includes/session.inc');

$Title = _('Payroll Records Maintenance');

include('includes/header.inc');
include('includes/prlFunctions.php');

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/payrol.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}

if (isset($_POST['Generate']) and ($_POST['Generate']) == _('Generate Payroll Data')) {
	include('includes/prlGenerateData.php');
	include('includes/prlComputeBasic.php');
	include('includes/prlComputeOthIncome.php');
	include('includes/prlComputeTD.php');
	include('includes/prlComputeOT.php');
	include('includes/prlComputeGross.php');
	include('includes/prlComputeLoan.php');
	include('includes/prlComputeSSS.php');
	include('includes/prlComputeHDMF.php');
	include('includes/prlComputePH.php');
	include('includes/prlComputeTAX.php'); //annualized method
	include('includes/prlComputeTAX2.php'); //common method
	include('includes/prlComputeNet.php');
}

if (isset($_POST['Close']) and ($_POST['Close']) == _('Close Payroll Period')) {
	$Status = GetOpenCloseStr(GetPayrollRow($PayrollID, 11));
	if ($Status == 'Closed') {
		prnMsg(_('Payroll is already closed. Re-open first...'), 'error');
		include('includes/footer.inc');
		exit;
	} else {
		$sql = "UPDATE prlpayrollperiod SET payclosed=1
					 WHERE payrollid = '" . $PayrollID . "'";
		$ErrMsg = _('The payroll record could not be updated because');
		$DbgMsg = _('The SQL that was used to update the payroll failed was');
		$result = DB_query($sql, $ErrMsg, $DbgMsg);
		prnMsg(_('The payroll master record for') . ' ' . $PayrollID . ' ' . _('has been closed'), 'success');
		include('includes/footer.inc');
		exit;
	}
}

if (isset($_POST['Purge']) and ($_POST['Purge']) == _('Purge Payroll Period')) {
	prnMsg(_('Not implemented at this moment...'), 'info');
	include('includes/footer.inc');
	exit;
}

if (isset($_POST['Reopen']) and ($_POST['Reopen']) == _('Re-open Payroll Period')) {
	$Status = GetOpenCloseStr(GetPayrollRow($PayrollID, 11));
	if ($Status == 'Open') {
		prnMsg(_('Payroll is already open...'), 'warn');
		include('includes/footer.inc');
		exit;
	} else {

		$sql = "UPDATE prlpayrollperiod SET payclosed=0
								WHERE payrollid = '" . $PayrollID . "'";
		$ErrMsg = _('The payroll record could not be updated because');
		$DbgMsg = _('The SQL that was used to update the payroll failed was');
		$result = DB_query($sql, $ErrMsg, $DbgMsg);
		prnMsg(_('The payroll master record for') . ' ' . $PayrollID . ' ' . _('has been opened'), 'success');
		include('includes/footer.inc');
		exit;
	}
}

if (isset($PayrollID)) {
	//PayrollID exists - either passed when calling the form or from the form itself
	echo '<form onSubmit="VerifyForm(this)" method="post" class="noPrint" id="CreatePayroll" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	if (!isset($_POST["New"])) {
		$sql = "SELECT payrollid,
						payrolldesc,
						payperiodid,
						startdate,
						enddate,
						fsmonth,
						fsyear,
						payclosed
					FROM prlpayrollperiod
					WHERE payrollid = '" . $PayrollID . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);
		$Description = $myrow['payrolldesc'];
		$PayPeriodID = GetPayPeriodDesc($myrow['payperiodid']);
		$StartDate = ConvertSQLDate($myrow['startdate']);
		$EndDate = ConvertSQLDate($myrow['enddate']);
		$FSMonth = GetMonthStr($myrow['fsmonth']);
		$FSYear = $myrow['fsyear'];
		$SSS = GetYesNoStr(isset($myrow['deductsss']));
		$HDMF = GetYesNoStr(isset($myrow['deducthdmf']));
		$basicpay = GetYesNoStr(isset($myrow['basicpay']));
		$Status = GetOpenCloseStr($myrow['payclosed']);
		echo '<input type="hidden" name="PayrollID" value="' . $PayrollID . '" />';
	} else {
		// its a new employee  being added
		echo '<input type="hidden" name="New" value="Yes" />';
		echo '<tr>
				<td>' . _('Payroll ID') . ':</td>
				<td><input type="text" name="PayrollID" value="' . $PayrollID . '" size="12" maxlength="10" /></td>
			</tr>';
	}

	echo '<tr>
			<td>' . _('Payroll ID') . '</td>
			<td>' . $PayrollID . '</td>
		</tr>
			<td>' . _('Description') . '</td>
			<td>' . $Description . '</td>
		</tr>
			<td>' . _('Pay Period') . '</td>
			<td>' . $PayPeriodID . '</td>
		</tr>
		<tr>
			<td>' . _('Start Date') . '</td>
			<td>' . $StartDate . '</td>
		</tr>
		<tr>
			<td>' . _('End Date') . '</td>
			<td>' . $EndDate . '</td>
		</tr>
		<tr>
			<td>' . _('FS Month') . '</td>
			<td>' . $FSMonth . ' ' . $FSYear . '</td>
		</tr>
		<tr>
			<td>' . _('Deduct SSS') . '</td>
			<td>' . $SSS . '</td>
		</tr>
		<tr>
		  <td>' . _('Deduct HDMF') . '</td>
		  <td>' . $HDMF . '</td>
		</tr>
		<tr>
			<td>' . _('Deduct basicpay') . '</td>
			<td>' . $basicpay . '</td>
		</tr>
		<tr>
			<td>' . _('Payroll Status') . '</td>
			<td>' . $Status . '</td>
		</tr>
	</table>';

	echo '<div class="centre">
		<input type="submit" name="Generate" value="' . _('Generate Payroll Data') . '" />
		<input type="submit" name="Reopen" value="' . _('Re-open Payroll Period') . '" />
	</div>
</form>';
} // end of main ifs

include('includes/footer.inc');
?>