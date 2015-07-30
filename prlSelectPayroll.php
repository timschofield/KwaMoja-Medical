<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');
$Title = _('Payroll Master Maintenance');

include('includes/header.inc');

echo '<div class="toplink"><a href="' . $RootPath . '/prlEditPayroll.php?SelectedAccountr=">' . _('Create Payroll Period') . '</a></div>';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/payrol.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
}


if (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$sql = "DELETE FROM prlemployeemaster WHERE employeeid " . LIKE . "'" . DB_escape_string($SelectedEmployeeID) . "'";
	$result = DB_query($sql);
	prnMsg('employee id has been deleted' . '!', 'success');
	//}
	//end if account group used in GL accounts
	unset($PayrollID);
	unset($_GET['PayrollID']);
	unset($_GET['select']);
	unset($_POST['PayrollID']);

}

if (!isset($PayrollID)) {
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of ChartMaster will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$sql = "SELECT payrollid,
					payrolldesc,
					fsmonth,
					fsyear,
					startdate,
					enddate,
					payperiodid
				FROM prlpayrollperiod
				ORDER BY payrollid";
	$ErrMsg = _('The payroll record could not be retrieved because');
	$result = DB_query($sql, $ErrMsg);

	echo '<table class="selection">
			<tr>
				<th>' . _('Payroll ID') . '</th>
				<th>' . _('Desciption') . '</th>
				<th>' . _('FS Month') . '</th>
				<th>' . _('FS Year') . '</th>
				<th>' . _('Start Date') . '</th>
				<th>' . _('End Date') . '</th>
				<th>' . _('Pay Period ') . '</th>
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

		echo '<td>' . $myrow['payrollid'] . '</td>
			<td>' . $myrow['payrolldesc'] . '</td>
			<td>' . $myrow['fsmonth'] . '</td>
			<td>' . $myrow['fsyear'] . '</td>
			<td>' . ConvertSQLDate($myrow['startdate']) . '</td>
			<td>' . ConvertSQLDate($myrow['enddate']) . '</td>
			<td>' . $myrow['payperiodid'] . '</td>
			<td><a href="' . $RootPath . '/prlCreatePayroll.php?PayrollID=' . $myrow['payrollid'] . '">' . _('Select') . '</a></td>
		</tr>';

	} //END WHILE LIST LOOP

	//END WHILE LIST LOOP
} //END IF selected="selected" ACCOUNT

echo '</table>';
//end of ifs and buts!

include('includes/footer.inc');
?>