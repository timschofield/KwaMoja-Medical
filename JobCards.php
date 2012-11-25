<?php
$PageSecurity=1;
if (!isset($_REQUEST['DebtorNo'])) {
	// Show Message Box
	echo "<script type='text/javascript'>";
	echo "location.href = 'index.php'";
	echo "</script>";
} //!isset($_REQUEST['DebtorNo'])

include('includes/session.inc');
$title = _('Customer Job Cards');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/JobCards.inc');

if (!isset($_REQUEST['JobCPrint'])) {
	echo '<p class="page_title_text">';
	echo '<img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Job Cards for Customer') . '" alt="" />' . ' ' . _('Job Cards for Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName;
	echo '</p>';
	$printbk = 'background:white;';
} //!isset($_REQUEST['JobCPrint'])
else {
	$printbk = 'background:white;';
	echo '<br>';
}

if (!isset($_REQUEST['SaveUpdateJob'])) {
	if ((!isset($_REQUEST['AddJob'])) and (!isset($_REQUEST['UpdateJob']))) {
		echo '<div class="page_help_text">' . _('To view or update a Job Card, click on the Job Card #.') . '</div><br />';

		echo '<table border="0"  width=81% style="BORDER: #a52a2a 1px solid;">';
		echo '<tr>';
		echo '<th width=10%>' . _('Job Card #') . '</th>
			<th width=10%>' . _('Invoice #') . '</th>
			<th width=10%>' . _('Creation Date') . '</th>
			<th width=10%>' . _('Completion Date') . '</th>
			<th width=50%>' . _('Job Card Description') . '</th>
			<th width=10%>' . _('Print') . '</th>';
		echo '</tr>';
		echo GetJobCards($db, $rootpath);
		echo '</table>';
		echo '<form action="' . $_SERVER['PHP_SELF'] . '?DebtorNo=' . $_SESSION['CustomerID'] . '&AddJob=1" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<div class="centre">';
		echo '<br><input type=submit name="AddJob" value="' . _('Add Job Card') . '">';
		echo '</div>';
		echo '</form>';

	} //(!isset($_REQUEST['AddJob'])) and (!isset($_REQUEST['UpdateJob']))
	else {
		if ((isset($_REQUEST["SaveJob"])) and (!isset($_REQUEST['UpdateJob']))) {
			// Update new Account in DB
			$dbvalues = '"' . $_SESSION['CustomerID'] . '","' . $_REQUEST["AddJobDescription"] . '","';
			$dbvalues = $dbvalues . $_REQUEST["AddJobTask0"] . '","' . $_REQUEST["AddJobTask1"] . '","';
			$dbvalues = $dbvalues . $_REQUEST["AddJobTask2"] . '","' . $_REQUEST["AddJobTask3"] . '","';
			$dbvalues = $dbvalues . $_REQUEST["AddJobTask4"] . '","' . $_REQUEST["AddJobTask5"] . '","';
			$dbvalues = $dbvalues . date('Y/m/d') . '"';

			$query  = "Insert into jobcards (debtorno,description,task1,task2,task3,task4,task5,task6,CreateDate) values (" . $dbvalues . ");";
			$result = DB_query($query, $db, $ErrMsg);

			echo '<div class="page_help_text">' . _('Record has been added.<br>Please wait the browser will redirect you automatically ...') . '.</div><br />';

			// Show Message Box
			echo "<script type='text/javascript'>";
			echo "location.href = 'JobCards.php?DebtorNo=" . $_SESSION['CustomerID'] . "'";
			echo "</script>";
		} //(isset($_REQUEST["SaveJob"])) and (!isset($_REQUEST['UpdateJob']))
		else {
			if (isset($_REQUEST['UpdateJob'])) {
				echo '<form action="' . $_SERVER['PHP_SELF'] . '?DebtorNo=' . $_SESSION['CustomerID'] . '&JobCardNo=' . $_REQUEST['JobCardNo'] . '&SaveUpdateJob=1" method="post">';
			} //isset($_REQUEST['UpdateJob'])
			else {
				echo '<form action="' . $_SERVER['PHP_SELF'] . '?DebtorNo=' . $_SESSION['CustomerID'] . '&AddJob=1&SaveJob=1" method="post">';
			}
			echo '<table cellpadding=4 width=99% style ="outline-style:solid;outline-width:1px;' . $printbk . '">';
			echo '<tr>';
			if (isset($_REQUEST['UpdateJob'])) {
				$jobno = $_REQUEST['JobCardNo'];
			} //isset($_REQUEST['UpdateJob'])
			else {
				$jobno = GetJobCardNO($db);
			}
			echo '<th width=50% colspan="2" style="font-size:16px;">' . _('Job Card # ' . $jobno) . '</th>';
			echo '</tr>';
			echo '<tr>';
			echo '<td colspan="2">';
			echo '<table border="0" cellpadding=0 width=100%  style ="' . $printbk . '">';
			echo '<tr>';
			echo '<td style="' . $printbk . '">';
			echo '<b>Creation Date:</b> <input type=text name="crdate" id="crdate" value="' . date('d/m/Y') . '" style="border:0;width:50%;background:none;" disabled>';
			echo '</td>';
			echo '<td style="' . $printbk . '">';
			echo '<b>Completion Date:</b> <input type=text name="codate" id="codate" value="" style="border:0;width:50%;background:none;">';
			echo '</td>';
			echo '<td style="' . $printbk . '">';
			echo '<b>Invoice #:</b> <input type=text name="inno" id="inno" value="" style="border:0;width:50%;background:none;">';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
			echo '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th width=50% style="text-align:left;font-size:12px;">' . _('Customer Address') . '</th>
			<th width=50%  style="text-align:left;font-size:12px;">' . _('Customer Contact') . '</th>';
			echo '</tr>';
			echo GetDebtorInfo($db, $printbk);
			echo '<tr>';
			echo '<th width=100% colspan="2" style="font-size:14px;">' . _('Job Description') . '</th>';
			echo '</tr>';
			echo '<tr>';
			echo '<th width=100% colspan="2" style="font-size:14px;' . $printbk . '"><input type=text name="AddJobDescription" id="JobDescription" value="' . _('Job Description') . '" style="width:100%;border:1px dotted #7f7f7f;background:none;"></th>';
			echo '</tr>';
			echo '<tr>';
			echo '<th width=50% style="text-align:left;font-size:12px;">' . _('Task') . '</th>
			<th width=50%  style="text-align:left;font-size:12px;">' . _('Action taken') . '</th>';
			echo '</tr>';
			$c = 0;
			while ($c < 6) {
				echo '<tr>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 0px 1px 0px;"><input type=text id="task' . $c . '" name="AddJobTask' . $c . '" value="" style="height:20px;width:100%;border:1px dotted #7f7f7f;background:none;"></td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 0px 1px 0px;">&nbsp;</td>';
				echo '</tr>';
				$c++;
			} //$c < 6
			echo '<tr>';
			echo '<th width=90% colspan="2" style="font-size:14px;">' . _('Items Used') . '</th>';
			echo '</tr>';
			echo '<tr>';
			echo '<td colspan="2">';
			echo '<table cellpadding=0 cellspacing=0 width=100%>';
			echo '<tr>';
			echo '<th width=50%  style="text-align:left;font-size:12px;">' . _('Description') . '</th>
						<th width=10% style="text-align:left;font-size:12px;">' . _('Doc #') . '</th>
						<th width=10% style="text-align:left;font-size:12px;">' . _('QTY') . '</th>
						<th width=15% style="text-align:left;font-size:12px;">' . _('Unit Price N$') . '</th>
						<th width=15%  style="text-align:left;font-size:12px;">' . _('Amount N$') . '</th>';
			echo '</tr>';
			echo '<tr>';
			echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '</tr>';
			$c = 0;
			while ($c < 14) {
				echo '<tr>';
				echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '</tr>';
				$c++;
			} //$c < 14
			echo '</table>';
			echo '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th width=100% colspan="2" style="font-size:14px;">' . _('Labour') . '</th>';
			echo '</tr>';
			echo '<tr>';
			echo '<td colspan="2">';
			echo '<table cellpadding=0 cellspacing=0 width=100%>';
			echo '<tr>';
			echo '<th width=30%  style="text-align:left;font-size:12px;">' . _('Technician') . '</th>
						<th width=10% style="text-align:left;font-size:12px;">' . _('Date') . '</th>
						<th width=10% style="text-align:left;font-size:12px;">' . _('Hours') . '</th>
						<th width=10% style="text-align:left;font-size:12px;">' . _('Hour Rate') . '</th>
						<th width=10% style="text-align:left;font-size:12px;">' . _('Travel') . '</th>
						<th width=15% style="text-align:left;font-size:12px;">' . _('Travel Rate') . '</th>
						<th width=15% style="text-align:left;font-size:12px;">' . _('Total Amount N$') . '</th>';
			echo '</tr>';
			echo '<tr>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 1px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '</tr>';
			$c = 0;
			while ($c < 4) {
				echo '<tr>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 1px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '</tr>';
				$c++;
			} //$c < 4
			echo '</table>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';

			if (isset($_REQUEST['UpdateJob'])) {
				//Get Values
				$sql        = 'SELECT id,debtorno,description,task1,task2,task3,task4,task5,task6, DATE_FORMAT(CreateDate, "%d/%m/%Y") as CreateDate,
						DATE_FORMAT(CompleteDate, "%d/%m/%Y") as CompleteDate, Invoice from jobcards where debtorno="' . $_SESSION['CustomerID'] . '" and id=' . $_REQUEST['JobCardNo'];
				$ErrMsg     = _('The job cards can not be retrieved!');
				$job_update = DB_query($sql, $db, $ErrMsg);
				$myrow      = DB_fetch_row($job_update);

				//Set Values
				echo "<script type='text/javascript'>";
				echo "document.getElementById('crdate').value ='" . $myrow[9] . "';";
				echo "document.getElementById('codate').value = '" . $myrow[10] . "';";
				echo "document.getElementById('inno').value = '" . $myrow[11] . "';";
				echo "document.getElementById('JobDescription').value = '" . $myrow[2] . "';";
				echo "document.getElementById('task0').value = '" . $myrow[3] . "';";
				echo "document.getElementById('task1').value = '" . $myrow[4] . "';";
				echo "document.getElementById('task2').value = '" . $myrow[5] . "';";
				echo "document.getElementById('task3').value = '" . $myrow[6] . "';";
				echo "document.getElementById('task4').value = '" . $myrow[7] . "';";
				echo "document.getElementById('task5').value = '" . $myrow[8] . "';";
				echo "</script>";

				if (!isset($_REQUEST['JobCPrint'])) {
					echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
					echo '<br><div class="centre"><input type=submit name="UpdateJob" value="' . _('Update Job Card') . '">';
					echo '</form>';
					echo '<a href="JobCards.php?DebtorNo=' . $_SESSION['CustomerID'] . '"><input type=button value="Close"></a></div>';
				} //!isset($_REQUEST['JobCPrint'])
				else {
					echo '<br>';
				}

			} //isset($_REQUEST['UpdateJob'])
			else {
				echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
				echo '<br><div class="centre"><input type=submit name="SaveJob" value="' . _('Save Job Card') . '">';
				echo '</form>';
				echo '<a href="JobCards.php?DebtorNo=' . $_SESSION['CustomerID'] . '"><input type=button value="Close"></a></div>';
			}
		}
	}
} //!isset($_REQUEST['SaveUpdateJob'])
else {
	// Insert new Account in DB
	$dbvalues = 'Description="' . $_REQUEST["AddJobDescription"] . '",';
	$dbvalues = $dbvalues . 'CompleteDate=DATE_FORMAT(STR_TO_DATE("' . $_REQUEST["codate"] . '", "%d/%m/%Y"), "%Y/%m/%d"),';
	$dbvalues = $dbvalues . 'Invoice="' . $_REQUEST["inno"] . '",';
	$dbvalues = $dbvalues . 'task1="' . $_REQUEST["AddJobTask0"] . '",task2="' . $_REQUEST["AddJobTask1"] . '",';
	$dbvalues = $dbvalues . 'task3="' . $_REQUEST["AddJobTask2"] . '",task4="' . $_REQUEST["AddJobTask3"] . '",';
	$dbvalues = $dbvalues . 'task5="' . $_REQUEST["AddJobTask4"] . '",task6="' . $_REQUEST["AddJobTask5"] . '"';

	$query  = "Update jobcards SET " . $dbvalues . " where id=" . $_REQUEST['JobCardNo'];
	$result = DB_query($query, $db, $ErrMsg);

	echo '<div class="page_help_text">' . _('Record has been updated.<br>Please wait the browser will redirect you automatically ...') . '.</div><br />';

	// Show Message Box
	echo "<script type='text/javascript'>";
	echo "location.href = 'JobCards.php?DebtorNo=" . $_SESSION['CustomerID'] . "'";
	echo "</script>";
}
include('includes/footer.inc');

?>