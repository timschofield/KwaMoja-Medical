<?php
if (isset($_GET['PayrollID'])){
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])){
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}
$HowFrequent=1; //1 -> every payday 2 -> once a month
$FSMonthRow=GetPayrollRow($PayrollID, &$db,5);
$FSYearRow=GetPayrollRow($PayrollID, &$db,6);
$DeductPH = GetYesNoStr(GetPayrollRow($PayrollID, &$db,9));
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
	$sql="DELETE FROM prlempbasicpayfile WHERE payrollid ='" . $PayrollID . "'";
	$Postdelph= DB_query($sql,$db);

	$sql = "UPDATE prlpayrolltrans SET	philhealth=0
				WHERE payrollid ='" . $PayrollID . "'";
	$RePostPH= DB_query($sql,$db);

	if ($DeductPH=='Yes') {
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
					$PHDetails = DB_query($sql,$db);
					if(DB_num_rows($PHDetails)>0)
					{
						$phrow=DB_fetch_array($PHDetails);
						$PHGP=$phrow['Gross'];
						if ($PHGP>30000) {
						 $PHGP=30000;
						}
						if ($PHGP>0 or $PHGP<>null) {
									 $myphrow = GetPHRow($PHGP, &$db);
										$sql = "INSERT INTO prlempbasicpayfile (
												payrollid,
												employeeid,
												grosspay,
												rangefrom,
												salarycredit,
												rangeto,
												employerbasicpay,
												employerec,
												employeebasicpay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $myrow['employeeid'] . "',
													'$PHGP',
													'". $myphrow['rangefrom'] ."',
													'". $myphrow['rangeto'] ."',
													'". $myphrow['salarycredit'] ."',
													'". $myphrow['employerbasicpay'] ."',
													'". $myphrow['employerec'] ."',
													'". $myphrow['employeebasicpay'] ."',
													'". $myphrow['total'] ."',
													'" . $myrow['fsmonth'] . "',
													'" . $myrow['fsyear'] . "'
													)";
												$ErrMsg = _('Inserting Basic Pay File failed.');
												$InsPHRecords = DB_query($sql,$db,$ErrMsg);
						} //if sssgp>0
					} //dbnumross sssdetials>0
				}  //end of while
			} else {
				while ($myrow = DB_fetch_array($PayDetails)) {
						$PHGP=$myrow['basicpay'];
						if ($PHGP>15000) {
						 $PHGP=15000;
						}
						if ($PHGP>0 or $PHGP<>null) {
									 $myphrow = GetPHRow($PHGP, &$db);
										$sql = "INSERT INTO prlempbasicpayfile (
												payrollid,
												employeeid,
												grosspay,
												rangefrom,
												rangeto,
												salarycredit,
												employerbasicpay,
												employerec,
												employeebasicpay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $myrow['employeeid'] . "',
													'$PHGP',
													'". $myphrow['rangefrom'] ."',
													'". $myphrow['rangeto'] ."',
													'".isset( $myphrow['salarycredit']) ."',
													'". $myphrow['employerbasicpay'] ."',
													'". $myphrow['employerec'] ."',
													'". $myphrow['employeebasicpay'] ."',
													'". $myphrow['total'] ."',
													'" . $myrow['fsmonth'] . "',
													'" . $myrow['fsyear'] . "'
													)";
												$ErrMsg = _('Inserting Basic Pay File failed.');
												$InsPHRecords = DB_query($sql,$db,$ErrMsg);
						} //if sssgp>0
				}  //end of while
			} //end of if ($HowFrequent==2) {
		}  //dbnumrows paydetails > 0
	} //deduct sss=yes

	//posting to payroll trans for sss
	if ($DeductPH=='Yes') {
		$sql = "SELECT counterindex,payrollid,employeeid,otpay,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($sql,$db);
		if(DB_num_rows($PayDetails)>0)
		{
			while ($myrow = DB_fetch_array($PayDetails))
			{
			$sql = "SELECT employeebasicpay
					FROM prlempbasicpayfile
			        WHERE prlempbasicpayfile.employeeid='" . $myrow['employeeid'] . "'
					AND prlempbasicpayfile.payrollid='" . $PayrollID . "'";
					$PHDetails = DB_query($sql,$db);
					if(DB_num_rows($PHDetails)>0)
					{
					    $phrow=DB_fetch_array($PHDetails);
						$PHPayment=$phrow['employeebasicpay'];
						$sql = 'UPDATE prlpayrolltrans SET philhealth='.$PHPayment.'
					     WHERE counterindex = ' . $myrow['counterindex'];
					    $PostPHPay = DB_query($sql,$db);
					}
			}
		}
	}
} //isset post submit
?>
