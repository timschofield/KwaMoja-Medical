<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');
$Title = _('View Payroll Deductions');

include('includes/header.inc');



if (isset($_GET['Counter'])) {
	$Counter = $_GET['Counter'];
} elseif (isset($_POST['Counter'])) {
	$Counter = $_POST['Counter'];
} else {
	unset($Counter);
}



if (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts
	if ($CancelDelete == 0) {
		$sql = "DELETE FROM prlloandeduction WHERE counterindex='$Counter'";
		$result = DB_query($sql);
		prnMsg(_('Deduction record for') . ' ' . $Counter . ' ' . _('has been deleted'), 'success');
		unset($Counter);
		unset($_SESSION['Counter']);
	} //end if Delete paypayperiod
}


if (!isset($Counter)) {
	$sql = "SELECT  	prlloandeduction.counterindex,
						prlloandeduction.payrollid,
						prlloandeduction.employeeid,
						prlloandeduction.loantableid,
						prlloandeduction.amount,
						prlloantable.loantableid,
						prlloantable.loantabledesc,
						prlemployeemaster.employeeid,
						prlemployeemaster.lastname,
						prlemployeemaster.firstname

		FROM prlloandeduction,prlloantable,prlemployeemaster
		WHERE prlloandeduction.loantableid = prlloantable.loantableid
		AND prlloandeduction.employeeid = prlemployeemaster.employeeid
		ORDER BY counterindex";
	$ErrMsg = _('The ot could not be retrieved because');
	$result = DB_query($sql, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Index') . "</td>
		<th>" . _('Pay ID ') . "</td>
		<th>" . _('Employee Name') . "</td>
		<th>" . _('Loan Type') . "</td>
		<th>" . _('Amount') . "</td>
	</tr>";
	$k = 0;
	while ($myrow = DB_fetch_array($result)) {

		//alternateTableRowColor($k);
		if ($k == 1) {
			echo "<tr bgcolor='#CCCCCC'>";
			$k = 0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
		echo "<td>" . $myrow["counterindex"] . "</td>
				<td>" . $myrow["payrollid"] . "</td>
				<td>" . $myrow["employeeid"] . " - " . $myrow["lastname"] . ", " . $myrow["firstname"] . "</td>
				<td>" . $myrow["loantableid"] . " - " . $myrow["loantabledesc"] . "</td>
    			<td>" . $myrow["amount"] . "</td></tr>";

	} //END WHILE LIST LOOP
}
echo '</table>';
//end of ifs and buts!

include('includes/footer.inc');
?>