<?php

function GetJobCards($db) {
	// No option selected yet, so show Customer Type drop down list
	$sql         = 'SELECT *
					FROM jobcards
					WHERE debtorno="' . $_SESSION['CustomerID'] . '"';
	$ErrMsg      = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($sql, $db, $ErrMsg);

	// Error if no customer types setup
	if (DB_num_rows($jobc_result) == 0) {
		echo '<tr>';
		echo '<td colspan="5">';
		echo _('No Job Cards found');
		echo '</td>';
		echo '</tr>';
	} //DB_num_rows($jobc_result) == 0
	else {
		echo '<tr>';
		echo '<td colspan="5">';
		// No option selected yet, so show Customer Type drop down list
		$sql = 'select id,Invoice,DATE_FORMAT(CreateDate, "%d/%m/%Y") as CreateDate,
						DATE_FORMAT(CompleteDate, "%d/%m/%Y") as CompleteDate,
						description from jobcards where debtorno="' . $_SESSION['CustomerID'] . '"';

		$ErrMsg      = _('The job cards can not be retrieved!');
		$jobc_result = DB_query($sql, $db, $ErrMsg);
		$myrow       = DB_fetch_array($jobc_result);

		$c = 0;

		// Error if no customer types setup
		while ($c < DB_num_rows($jobc_result)) {
			echo '<tr>';
			echo '<td width=10%>';
			echo '<center><a href="JobCards.php?DebtorNo=' . $_SESSION['CustomerID'] . '&JobCardNo=' . $myrow[$c]['id'] . '&UpdateJob=1">' . $myrow[$c]['id'] . '</a></center>';
			echo '</td>';
			echo '<td width=10%>';
			echo $myrow[$c]['Invoice'];
			echo '</td>';
			echo '<td width=10%>';
			echo $myrow[$c]['CreateDate'];
			echo '</td>';
			echo '<td width=10%>';
			echo $myrow[$c]['CompleteDate'];
			echo '</td>';
			echo '<td width=50%>';
			echo $myrow[$c]['description'];
			echo '</td>';
			echo '<td width=10%>';
			echo '<a href="#" onclick=window.open("JobCards.php?DebtorNo=HMT01&JobCardNo=2&UpdateJob=1&JobCPrint=1","Test")><center>Print</center></a>';
			echo '</td>';
			echo '</tr>';

			$c++;
		} //$c < DB_num_rows($jobc_result)
		echo '</td>';
		echo '</tr>';
	}

}

function GetDebtorInfo($db, $printbk) {
	// No option selected yet, so show Customer Type drop down list
	$sql = 'select debtorsmaster.debtorno, debtorsmaster.name, debtorsmaster.address1,
	debtorsmaster.address2, debtorsmaster.address3, debtorsmaster.address4,
	debtorsmaster.address5, custbranch.branchcode, custbranch.brname, custbranch.braddress1,
	custbranch.braddress2, custbranch.braddress3, custbranch.braddress4, custbranch.braddress5,
	custbranch.phoneno, custbranch.faxno, custbranch.contactname from debtorsmaster,
	custbranch where custbranch.branchcode="' . $_SESSION['CustomerID'] . '" and
	debtorsmaster.debtorno = custbranch.debtorno';

	$ErrMsg      = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($sql, $db, $ErrMsg);
	$myrow       = DB_fetch_row($jobc_result);

	// Error if no customer types setup
	if (DB_num_rows($jobc_result) != 0) {
		echo '<tr>';
		echo '<td style="' . $printbk . '">';
		echo '<b>Name:</b> ' . $myrow[1] . '<br>';
		echo '<b>Street Address:</b> <br>';
		echo '<b>P. O. Box:</b> ' . $myrow[2] . '<br>';
		echo '<b>City:</b> ' . $myrow[3] . '<br>';
		echo '</td>';
		echo '<td style="' . $printbk . '">';
		echo '<b>Contact:</b> ' . $myrow[16] . '<br>';
		echo '<b>Cellphone:</b> ' . $myrow[14] . '<br>';
		echo '<b>E-Mail:</b> <br>';
		echo '<b>Fax:</b> ' . $myrow[15] . '<br>';
		echo '</td>';
		echo '</tr>';
	} //DB_num_rows($jobc_result) != 0

}

function GetJobCardNO($db) {
	$sql         = 'select max(id) from jobcards';
	$ErrMsg      = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($sql, $db, $ErrMsg);
	$myrow       = DB_fetch_row($jobc_result);
	$ret         = $myrow[0] + 1;
	return $ret;

}


?>