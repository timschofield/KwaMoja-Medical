<?php

include('includes/session.inc');

$Title = _('Employment Status Section');

include('includes/header.inc');

if (isset($_GET['SelectedStatusID'])) {
	$SelectedStatusID = $_GET['SelectedStatusID'];
} elseif (isset($_POST['SelectedStatusID'])) {
	$SelectedStatusID = $_POST['SelectedStatusID'];
}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (trim($_POST['EmploymentName']) == '') {
		$InputError = 1;
		prnMsg(_('The employment description may not be empty'), 'error');
	}

	if ($InputError == 0) {
		if (isset($_POST['insert'])) {
			$sql = "INSERT INTO prlemploymentstatus VALUES (NULL,
															'" . $_POST['EmploymentName'] . "'
															)";
			$ErrMsg = _('An error occurred in inserting the employment status');
			$DbgMsg = _('The SQL that was used to insert the employment status was');
		} else if (isset($_POST['update'])) {
			$sql = "UPDATE prlemploymentstatus SET employmentdesc='" . $_POST['EmploymentName'] . "'
												WHERE employmentid='" . $SelectedStatusID . "'";
			$ErrMsg = _('An error occurred in updating the employment status');
			$DbgMsg = _('The SQL that was used to update the employment status was');
		}
		$result = DB_query($sql, $ErrMsg, $DbgMsg);
	}
	unset($SelectedStatusID);
	unset($_POST['SelectedStatusID']);
	unset($_POST['EmploymentName']);

} elseif (isset($_GET['delete'])) {
	$TestSQL = "SELECT employeeid
					FROM prlemployeemaster
					WHERE employmentid = '" . $SelectedStatusID . "'";
	$TestResult = DB_query($TestSQL);
	if (DB_num_rows($TestResult) > 0) {
		prnMsg(_('Cannot delete this employment status because employees have been created using this employment status'), 'warn');
		echo '<br>' . _('There are') . ' ' . DB_num_rows($TestResult) . ' ' . _('employees that refer to this employment status') . '</FONT>';
	} else {
		$sql = "DELETE FROM prlemploymentstatus WHERE employmentid='" . $SelectedStatusID . "'";
		$result = DB_query($sql);
		prnMsg($SelectedStatusID . ' ' . _('employement status has been deleted') . '!', 'success');
	}
	unset($SelectedStatusID);
	unset($_GET['SelectedStatusID']);
	unset($_GET['delete']);
	unset($_POST['SelectedStatusID']);
	unset($_POST['StatusID']);
	unset($_POST['EmploymentName']);
}

if (!isset($SelectedStatusID)) {

	/* An employment status could be posted when one has been edited and is being updated
	or GOT when selected for modification
	SelectedStatusID will exist because it was sent with the page in a GET .
	If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of account groups will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$sql = "SELECT employmentid,
					employmentdesc
			FROM prlemploymentstatus
			ORDER BY employmentid";

	$ErrMsg = _('Could not get employment status because');
	$result = DB_query($sql, $ErrMsg);

	if (DB_num_rows($result) > 0) {
		echo '<table>
				<tr>
					<th>' . _('Employment Status') . '</td>
				</tr>';

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {

			if ($k == 1) {
				echo '<tr bgcolor="#CCCCCC">';
				$k = 0;
			} else {
				echo '<tr bgcolor="#EEEEEE">';
				$k++;
			}

			echo '<td>' . $myrow['employmentdesc'] . '</td>
					<td><a href="' . $_SERVER['PHP_SELF'] . '?&SelectedStatusID=' . $myrow['employmentid'] . '">' . _('Edit') . '</a></td>
					<td><a href="' . $_SERVER['PHP_SELF'] . '?&SelectedStatusID=' . $myrow['employmentid'] . '&delete=1">' . _('Delete') . '</a></td>
				</tr>';

		} //END WHILE LIST LOOP
		echo '</table>';
	} //end of ifs and buts!
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedStatusID)) {
		//editing an existing section

		$sql = "SELECT employmentid,
						employmentdesc
					FROM prlemploymentstatus
					WHERE employmentid='" . $SelectedStatusID . "'";

		$result = DB_query($sql);
		if (DB_num_rows($result) == 0) {
			prnMsg(_('Could not retrieve the requested employment status, please try again.'), 'warn');
			unset($SelectedStatusID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['StatusID'] = $myrow['employmentid'];
			$_POST['EmploymentName'] = $myrow['employmentdesc'];

			echo '<input type="hidden" name="SelectedStatusID" value="' . $_POST['StatusID'] . '" />';
			echo '<table>';
		}

	} else {
		$_POST['EmploymentName'] = '';
		echo "<table>";
	}
	echo '<tr>
			<td>' . _('Employment Status') . ':</td>
			<td><input type="text" name="EmploymentName" size="30" maxlength="30" value="' . $_POST['EmploymentName'] . '"></td>
		</tr>
	</table>';
	if (isset($SelectedStatusID)) {
		echo '<input type="submit" name="update" value="' . _('Update Status') . '" />';
	} else {
		echo '<input type="submit" name="insert" value="' . _('Insert New Status') . '" />';
	}
	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>