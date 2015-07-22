<?php
if (isset($_GET['PayrollID'])){
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])){
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}
$HowFrequent=1; // 1 -> every payday 2 -> once a month
$FSMonthRow=GetPayrollRow($PayrollID, &$db,5);
$FSYearRow=GetPayrollRow($PayrollID, &$db,6);
$DeductHDMF = GetYesNoStr(GetPayrollRow($PayrollID, &$db,8));
$Status = GetOpenCloseStr(GetPayrollRow($PayrollID, &$db,11));
if ($Status=='Closed') {
	prnMsg( _('Payroll is Closed. Re-open first...'), 'warn');
	include('includes/footer.inc');
	exit;
}
if (isset($_POST['submit'])) {
	prnMsg( _('Contact Administrator...'), 'error');
	include('includes/footer.inc');
	exit;
} else {
	$sql="DELETE FROM prlempgrosspayfile WHERE payrollid ='" . $PayrollID . "'";
	$Postdelhdmf= DB_query($sql,$db);

	$sql = "UPDATE prlpayrolltrans SET	hdmf=0
				WHERE payrollid ='" . $PayrollID . "'";
	$RePostHDMF= DB_query($sql,$db);

	if ($DeductHDMF=='Yes') {
		$sql = "SELECT counterindex,payrollid,employeeid,basicpay,othincome,absent,late,otpay,grosspay,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($sql,$db);
		if(DB_num_rows($PayDetails)>0) {
			if ($HowFrequent==2) {
				while ($myrow = DB_fetch_array($PayDetails)) {
					$sql = "SELECT sum(basicpay) AS Gross
					FROM prlpayrolltrans
					WHERE prlpayrolltrans.employeeid='" . $myrow['employeeid'] . "'
					AND prlpayrolltrans.fsmonth='" . $FSMonthRow . "'
					AND prlpayrolltrans.fsyear='" . $FSYearRow . "'";
					$HDMFDetails = DB_query($sql,$db);
					if(DB_num_rows($HDMFDetails)>0) {
						$hdmfrow=DB_fetch_array($HDMFDetails);
						$HDMFGP=$hdmfrow['Gross'];
						if ($HDMFGP>0 or $HDMFGP<>null) {
							$HFMFER=GetHDMFER($HDMFGP, &$db);
							$HFMFEE=GetHDMFEE($HDMFGP, &$db);
							$HDMFTOT=$HFMFEE+$HFMFER;
										$sql = "INSERT INTO prlempgrosspayfile (
												payrollid,
												employeeid,
												grosspay,
												employergrosspay,
												employeegrosspay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $myrow['employeeid'] . "',
													'$HDMFGP',
													'$HFMFER',
													'$HFMFEE',
													'$HDMFTOT',
													'" . $myrow['fsmonth'] . "',
													'" . $myrow['fsyear'] . "'
													)";
												$ErrMsg = _('Inserting Grosspay File failed.');
												$InsSSSRecords = DB_query($sql,$db,$ErrMsg);
						} //if sssgp>0
					} //dbnumross sssdetials>0
				}  //end of while
			} else {
				while ($myrow = DB_fetch_array($PayDetails)) {
						$HDMFGP=$myrow['basicpay'];
						if ($HDMFGP>0 or $HDMFGP<>null) {
							$HFMFER=GetHDMFER($HDMFGP, &$db);
							$HFMFEE=GetHDMFEE($HDMFGP, &$db);
							$HDMFTOT=$HFMFEE+$HFMFER;
										$sql = "INSERT INTO prlempgrosspayfile (
												payrollid,
												employeeid,
												grosspay,
												employergrosspay,
												employeegrosspay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $myrow['employeeid'] . "',
													'$HDMFGP',
													'$HFMFER',
													'$HFMFEE',
													'$HDMFTOT',
													'" . $myrow['fsmonth'] . "',
													'" . $myrow['fsyear'] . "'
													)";
												$ErrMsg = _('Inserting HDMF File failed.');
												$InsSSSRecords = DB_query($sql,$db,$ErrMsg);
						} //if sssgp>0
				}  //end of while
			}
		}  //dbnumrows paydetails > 0
	} //deduct sss=yes

	//posting to payroll trans for hdmf
	if ($DeductHDMF=='Yes') {
		$sql = "SELECT counterindex,payrollid,employeeid,basicpay,absent,late,otpay,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($sql,$db);
		if(DB_num_rows($PayDetails)>0)
		{
			while ($myrow = DB_fetch_array($PayDetails))
			{
			$sql = "SELECT employeegrosspay
					FROM prlempgrosspayfile
			        WHERE prlempgrosspayfile.employeeid='" . $myrow['employeeid'] . "'
					AND prlempgrosspayfile.payrollid='" . $PayrollID . "'";
					$HDMFDetails = DB_query($sql,$db);
					if(DB_num_rows($HDMFDetails)>0)
					{
					    $hdmfrow=DB_fetch_array($HDMFDetails);
						$HDMFPayment=$hdmfrow['employeegrosspay'];
						$sql = 'UPDATE prlpayrolltrans SET hdmf='.$HDMFPayment.'
					     WHERE counterindex = ' . $myrow['counterindex'];
					    $PostHDMFPay = DB_query($sql,$db);
					}
			}
		}
	}
} //isset post submit
?>
