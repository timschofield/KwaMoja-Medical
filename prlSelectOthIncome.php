<?php
include('includes/session.inc');
$Title = _('View Other Income Data');

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

	// PREVENT DELETES IF DEPENDENT RECORDS
	if ($CancelDelete == 0) {
		$sql = "DELETE FROM prlothincfile WHERE counterindex='$Counter'";
		$result = DB_query($sql);
		prnMsg(_('Other Income record for') . ' ' . $Counter . ' ' . _('has been deleted'), 'success');
		unset($Counter);
		unset($_SESSION['Counter']);
	} //end if Delete paypayperiod
}


if (!isset($Counter)) {
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<input type="hidden" name="New" value="Yes">';
	echo '<table>';

	$sql = "SELECT  	counterindex,
						employeeid,
						othdate,
						othincid,
						othincamount
		FROM prlothincfile
		ORDER BY counterindex";
	$ErrMsg = _('The ot could not be retrieved because');
	$result = DB_query($sql, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Index') . "</td>
		<th>" . _('Emp ID') . "</td>
		<th>" . _('Date') . "</td>
		<th>" . _('OthInc ID') . "</td>
		<th>" . _('Amount') . "</td>
	</tr>";

	$k = 0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {

		if ($k == 1) {
			echo "<tr bgcolor='#CCCCCC'>";
			$k = 0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		echo '<td>' . $myrow[0] . '</td>';
		echo '<td>' . $myrow[1] . '</td>';
		echo '<td>' . $myrow[2] . '</td>';
		echo '<td>' . $myrow[3] . '</td>';
		echo '<td>' . $myrow[4] . '</td>';
		//echo '<td>' . $myrow[5] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&Counter=' . $myrow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP

	//END WHILE LIST LOOP
} //END IF selected="selected" ACCOUNT


echo '</table>';
//end of ifs and buts!

include('includes/footer.inc');
?>