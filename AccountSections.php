<?php

include('includes/session.inc');

$Title = _('Account Sections');

$ViewTopic = 'GeneralLedger';
$BookMark = 'AccountSections';
include('includes/header.inc');

// SOME TEST TO ENSURE THAT AT LEAST INCOME AND COST OF SALES ARE THERE
$SQL = "SELECT sectionid FROM accountsection WHERE sectionid=1";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	$SQL = "INSERT INTO accountsection (sectionid,
										sectionname)
									VALUES (1,
											'Income')";
	$Result = DB_query($SQL);
}

$SQL = "SELECT sectionid FROM accountsection WHERE sectionid=2";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	$SQL = "INSERT INTO accountsection (sectionid,
										sectionname)
									VALUES (2,
											'Cost Of Sales')";
	$Result = DB_query($SQL);
}
// DONE WITH MINIMUM TESTS


if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (isset($_POST['SectionID'])) {
		$SQL = "SELECT sectionid
					FROM accountsection
					WHERE sectionid='" . $_POST['SectionID'] . "'";
		$Result = DB_query($SQL);

		if ((DB_num_rows($Result) != 0 and !isset($_POST['SelectedSectionID']))) {
			$InputError = 1;
			prnMsg(_('The account section already exists in the database'), 'error');
		}
	}
	if (mb_strlen($_POST['SectionName']) == 0) {
		$InputError = 1;
		prnMsg(_('The account section name must contain at least three characters'), 'error');
	}
	if (isset($_POST['SectionID']) and (!is_numeric($_POST['SectionID']))) {
		$InputError = 1;
		prnMsg(_('The section number must be an integer'), 'error');
	}
	if (isset($_POST['SectionID']) and mb_strpos($_POST['SectionID'], ".") > 0) {
		$InputError = 1;
		prnMsg(_('The section number must be an integer'), 'error');
	}

	if (isset($_POST['SelectedSectionID']) and $_POST['SelectedSectionID'] != '' and $InputError != 1) {

		/*SelectedSectionID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the delete code below*/

		$SQL = "UPDATE accountsection SET sectionname='" . $_POST['SectionName'] . "'
				WHERE sectionid = '" . $_POST['SelectedSectionID'] . "'";

		$Msg = _('Record Updated');
	} elseif ($InputError != 1) {

		/*SelectedSectionID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account section form */

		$SQL = "INSERT INTO accountsection (sectionid,
											sectionname
										) VALUES (
											'" . $_POST['SectionID'] . "',
											'" . $_POST['SectionName'] . "')";
		$Msg = _('Record inserted');
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($_POST['SelectedSectionID']);
		unset($_POST['SectionID']);
		unset($_POST['SectionName']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'accountgroups'
	$SQL = "SELECT COUNT(sectioninaccounts) AS sections FROM accountgroups WHERE sectioninaccounts='" . $_GET['SelectedSectionID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['sections'] > 0) {
		prnMsg(_('Cannot delete this account section because general ledger accounts groups have been created using this section'), 'warn');
		echo '<div>';
		echo '<br />' . _('There are') . ' ' . $MyRow['sections'] . ' ' . _('general ledger accounts groups that refer to this account section');
		echo '</div>';

	} else {
		//Fetch section name
		$SQL = "SELECT sectionname FROM accountsection WHERE sectionid='" . $_GET['SelectedSectionID'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$SectionName = $MyRow['sectionname'];

		$SQL = "DELETE FROM accountsection WHERE sectionid='" . $_GET['SelectedSectionID'] . "'";
		$Result = DB_query($SQL);
		prnMsg($SectionName . ' ' . _('section has been deleted') . '!', 'success');

	} //end if account group used in GL accounts
	unset($_GET['SelectedSectionID']);
	unset($_GET['delete']);
	unset($_POST['SelectedSectionID']);
	unset($_POST['SectionID']);
	unset($_POST['SectionName']);
}

if (!isset($_GET['SelectedSectionID']) and !isset($_POST['SelectedSectionID'])) {

	/* An account section could be posted when one has been edited and is being updated
	or GOT when selected for modification
	SelectedSectionID will exist because it was sent with the page in a GET .
	If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of account groups will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT sectionid,
			sectionname
		FROM accountsection
		ORDER BY sectionid";

	$ErrMsg = _('Could not get account group sections because');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '<br /></p>';

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Section Number') . '</th>
					<th class="SortedColumn">' . _('Section Description') . '</th>
					<th class="noprint" colspan="2">&nbsp;</th>
				</tr>
			</thead>';

	echo '<tbody>';
	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_array($Result)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		echo '<td class="number">' . $MyRow['sectionid'] . '</td><td>' . $MyRow['sectionname'] . '</td>';
		echo '<td class="noprint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedSectionID=' . urlencode($MyRow['sectionid']), ENT_QUOTES, 'UTF-8') . '">' . _('Edit') . '</a></td>';
		if ($MyRow['sectionid'] == '1' or $MyRow['sectionid'] == '2') {
			echo '<td class="noprint"><b>' . _('Restricted') . '</b></td>';
		} else {
			echo '<td class="noprint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedSectionID=' . urlencode($MyRow['sectionid']) . '&delete=1', ENT_QUOTES, 'UTF-8') . '">' . _('Delete') . '</a></td>';
		}
		echo '</tr>';
	} //END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';
} //end of ifs and buts!


if (isset($_POST['SelectedSectionID']) or isset($_GET['SelectedSectionID'])) {
	echo '<div class="toplink"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Account Sections') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" class="noprint" id="AccountSections" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($_GET['SelectedSectionID'])) {
		//editing an existing section
		echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '<br /></p>';

		$SQL = "SELECT sectionid,
				sectionname
			FROM accountsection
			WHERE sectionid='" . $_GET['SelectedSectionID'] . "'";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result) == 0) {
			prnMsg(_('Could not retrieve the requested section please try again.'), 'warn');
			unset($_GET['SelectedSectionID']);
		} else {
			$MyRow = DB_fetch_array($Result);

			$_POST['SectionID'] = $MyRow['sectionid'];
			$_POST['SectionName'] = $MyRow['sectionname'];

			echo '<input type="hidden" name="SelectedSectionID" value="' . $_POST['SectionID'] . '" />';
			echo '<table class="selection">
					<tr>
						<td>' . _('Section Number') . ':' . '</td>
						<td>' . $_POST['SectionID'] . '</td>
					</tr>';
		}

	} else {

		if (!isset($_POST['SelectedSectionID'])) {
			$_POST['SelectedSectionID'] = '';
		}
		if (!isset($_POST['SectionID'])) {
			$_POST['SectionID'] = '';
		}
		if (!isset($_POST['SectionName'])) {
			$_POST['SectionName'] = '';
		}
		echo '<table class="selection">
			<tr>
				<td>' . _('Section Number') . ':' . '</td>
				<td><input tabindex="1" type="text" name="SectionID" class="number" size="4" autofocus="autofocus" required="required" maxlength="4" value="' . $_POST['SectionID'] . '" /></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('Section Description') . ':' . '</td>
			<td><input tabindex="2" type="text" name="SectionName" autofocus="autofocus" required="required" size="30" maxlength="30" value="' . $_POST['SectionName'] . '" /></td>
		</tr>';

	echo '<tr>
			<td colspan="2"><div class="centre"><input tabindex="3" type="submit" name="submit" value="' . _('Enter Information') . '" /></div></td>
		</tr>
		</table>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>