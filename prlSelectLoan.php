<?php
include('includes/session.inc');
$Title = _('View Employee Loan Master File');

include('includes/header.inc');

if (isset($_GET['SelectedID'])) {
	$SelectedID = $_GET['SelectedID'];
} elseif (isset($_POST['SelectedID'])) {
	$SelectedID = $_POST['SelectedID'];
}

if (!isset($SelectedID)) {
	$sql = "SELECT prlloanfile.counterindex,
			prlloanfile.loanfileid,
			prlloanfile.loanfiledesc,
			prlloanfile.employeeid,
			prlloanfile.loantableid,
			prlloanfile.loanamount,
			prlloanfile.amortization,
			prlloanfile.nextdeduction,
			prlloantable.loantableid,
			prlloantable.loantabledesc,
			prlemployeemaster.employeeid,
			prlemployeemaster.lastname,
			prlemployeemaster.firstname
		FROM prlloanfile,prlloantable,prlemployeemaster
		WHERE prlloanfile.loantableid = prlloantable.loantableid
		AND prlloanfile.employeeid = prlemployeemaster.employeeid
		ORDER BY counterindex";
	$ErrMsg = _('The employee record could not be retrieved because');
	$result = DB_query($sql, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Index ') . "</td>
		<th>" . _('Ref ID') . "</td>
		<th>" . _('Loan Description ') . "</td>
		<th>" . _('Start of Deduction') . "</td>
		<th>" . _('Employee Name') . "</td>
		<th>" . _('Loan Type') . "</td>
		<th>" . _('Loan Amount') . "</td>
		<th>" . _('Amortization') . "</td>
	</tr>";
	$k = 0;
	while ($myrow = DB_fetch_array($result)) {

		//alternateTableRowColor($k);
		if ($k == 1) {
			echo "<TR bgcolor='#CCCCCC'>";
			$k = 0;
		} else {
			echo "<TR bgcolor='#EEEEEE'>";
			$k++;
		}
		echo "<td>" . $myrow["counterindex"] . "</td>
				<td>" . $myrow["loanfileid"] . "</td>
    			<td>" . $myrow["loanfiledesc"] . "</td>
				<td>" . $myrow["nextdeduction"] . "</td>
				<td>" . $myrow["employeeid"] . " - " . $myrow["lastname"] . ", " . $myrow["firstname"] . "</td>
				<td>" . $myrow["loantabledesc"] . "</td>
    			<td>" . $myrow["loanamount"] . "</td>
				<td>" . $myrow["amortization"] . "</td>
				<td><a href=" . $RootPath . '/prlLoanFile.php?&SelectedID=' . $myrow[0] . '>' . _('Edit') . "</td>
				<td><a href=" . $RootPath . '/prlLoanFile.php??&SelectedID=' . $myrow[0] . '"&delete=1">' . _('Delete') . "</td></tr>";
	} //END WHILE LIST LOOP
}

echo '</table>';
//end of ifs and buts!

include('includes/footer.inc');
?>