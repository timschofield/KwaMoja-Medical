<?php
if (isset($_GET['PayrollID'])){
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])){
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}

$Status = GetOpenCloseStr(GetPayrollRow($PayrollID, $db,11));
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
   	$FromPeriod = GetPayrollRow($PayrollID, $db,3);
	$ToPeriod = GetPayrollRow($PayrollID, $db,4);

    //update/delete any previous posting for the same payrollid
	$sql = "SELECT counterindex,payrollid,refid,employeeid,amount
	        FROM prlloandeduction
			WHERE prlloandeduction.payrollid='" . $PayrollID . "'";
	$OthDetails = DB_query($sql,$db);
	if(DB_num_rows($OthDetails)>0)
	{
		while ($myrow = DB_fetch_array($OthDetails))
		{
		$sql = "SELECT counterindex,loanfileid,employeeid,amortization,loanbalance
					FROM prlloanfile
			        WHERE prlloanfile.counterindex='" . $myrow['refid'] . "'
					ORDER BY counterindex";
					$IncDetails = DB_query($sql,$db);
					if(DB_num_rows($IncDetails)>0)
					{
						while ($incrow = DB_fetch_array($IncDetails))
						{
							$LoanAmount=$myrow['amount'];
							if ($LoanAmount>0 or $LoanAmount<>null) {
								$sql = 'UPDATE prlloanfile SET ytddeduction=ytddeduction-'.$LoanAmount.', loanbalance=loanbalance+'.$LoanAmount.'
								WHERE counterindex = ' . $incrow['counterindex'];
								$PostLoanAmount = DB_query($sql,$db);
							}
						}
					}
		}

	$sql="DELETE FROM prlloandeduction WHERE payrollid ='" . $PayrollID . "'";
	$Postdelloan= DB_query($sql,$db);
	}




	$sql = "SELECT counterindex,payrollid,employeeid,loandeduction
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($sql,$db);
	if(DB_num_rows($PayDetails)>0)
	{
		while ($myrow = DB_fetch_array($PayDetails))
		{
			$sql = "SELECT counterindex,loanfileid,loantableid,employeeid,amortization,nextdeduction,loanbalance
					FROM prlloanfile
			        WHERE prlloanfile.employeeid='" . $myrow['employeeid'] . "'
					AND nextdeduction<='$ToPeriod'
					ORDER BY counterindex";
					$LoanDetails = DB_query($sql,$db);
					if(DB_num_rows($LoanDetails)>0)
					{
						while ($loanrow = DB_fetch_array($LoanDetails))
						{
							if ($loanrow['loanbalance']>0) {
								if ($loanrow['loanbalance']<=$loanrow['amortization']) {
							$sql = "INSERT INTO prlloandeduction(employeeid,refid,loantableid,amount)
										SELECT prlloanfile.employeeid,prlloanfile.counterindex,prlloanfile.loantableid,prlloanfile.loanbalance						FROM prlloanfile
						WHERE prlloanfile.counterindex='" . $loanrow['counterindex'] . "'";
									$ErrMsg = _('Inserting Loan File failed.');
									$InsLoanRecords = DB_query($sql,$db,$ErrMsg);

									//adjust balance
									$LoanAmount=$loanrow['loanbalance'];
									$sql = 'UPDATE prlloanfile SET ytddeduction=ytddeduction+'.$LoanAmount.', loanbalance=loanbalance-'.$LoanAmount.'
										WHERE counterindex = ' . $loanrow['counterindex'];
										$UpdateLoanAmount = DB_query($sql,$db);

								} else {

									$sql = "INSERT INTO prlloandeduction(employeeid,refid,loantableid,amount)
										SELECT employeeid,counterindex,loantableid,amortization
										FROM prlloanfile
										WHERE prlloanfile.counterindex='" . $loanrow['counterindex'] . "'";
									$ErrMsg = _('Inserting Loan File failed.');
									$InsLoanRecords = DB_query($sql,$db,$ErrMsg);

									//adjust balance
									$LoanAmount=$loanrow['amortization'];
									$sql = 'UPDATE prlloanfile SET ytddeduction=ytddeduction+'.$LoanAmount.', loanbalance=loanbalance-'.$LoanAmount.'
										WHERE counterindex = ' . $loanrow['counterindex'];
										$UpdateLoanAmount = DB_query($sql,$db);

								}
							}
						$sql = "UPDATE prlloandeduction SET payrollid='".$PayrollID."'
											WHERE prlloandeduction.payrollid = ''
											AND prlloandeduction.loantableid='" . $loanrow['loantableid'] . "'
											AND prlloandeduction.employeeid='" . $myrow['employeeid'] . "'";
									$PostLOAN = DB_query($sql,$db);
						}
					}

		}
	}


	$sql = "SELECT counterindex,payrollid,employeeid,loandeduction
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($sql,$db);
	if(DB_num_rows($PayDetails)>0)
	{
		while ($myrow = DB_fetch_array($PayDetails))
		{
			$sql = "SELECT sum(amount) AS loanded
				FROM prlloandeduction
				WHERE prlloandeduction.employeeid='" . $myrow['employeeid'] . "'
				AND payrollid='" . $myrow['payrollid'] . "'";
				$LoanDetails = DB_query($sql,$db);
				if(DB_num_rows($LoanDetails)>0)
				{
				//$otrow=DB_fetch_array($OTDetails);
				   //$OTPayment=$otrow['otpay'];
					//$sql = 'UPDATE prlpayrolltrans SET otpay='.$OTPayment.'
					//			WHERE counterindex = ' . $myrow['counterindex'];
					//$PostOTPay = DB_query($sql,$db);

				   $loanrow=DB_fetch_array($LoanDetails);
				   $LoanPayment=$loanrow['loanded'];
				   if ($LoanPayment>0 or $LoanPayment<>null) {
					$sql = 'UPDATE prlpayrolltrans SET loandeduction='.$LoanPayment.'
					     WHERE counterindex = ' . $myrow['counterindex'];
					$PostLoanPay = DB_query($sql,$db);
					}
				}
		}
	} else	{
		//echo "No Loan Deduction..";
	}

}
?>
