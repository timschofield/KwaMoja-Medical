<?php

include('includes/session.inc');

$Title = _('Account Groups');
$ViewTopic = 'GeneralLedger';
$BookMark = 'AccountGroups';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

function CheckForRecursiveGroup($ParentGroupName, $GroupName) {
	/* returns true ie 1 if the group contains the parent group as a child group
	ie the parent group results in a recursive group structure otherwise false ie 0 */

	$ErrMsg = _('An error occurred in retrieving the account groups of the parent account group during the check for recursion');
	$DbgMsg = _('The SQL that was used to retrieve the account groups of the parent account group and that failed in the process was');

	do {
		$SQL = "SELECT parentgroupname
				FROM accountgroups
				WHERE groupname='" . $GroupName . "'";

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		$MyRow = DB_fetch_row($Result);
		if ($ParentGroupName == $MyRow[0]) {
			return true;
		} //$ParentGroupName == $MyRow[0]
		$GroupName = $MyRow[0];
	} while ($MyRow[0] != '');
	return false;
} //end of function CheckForRecursiveGroupName

// If $Errors is set, then unset it.
if (isset($Errors)) {
	unset($Errors);
} //isset($Errors)

$Errors = array();

if (isset($_POST['MoveGroup'])) {
	$SQL = "UPDATE chartmaster SET group_='" . $_POST['DestinyAccountGroup'] . "' WHERE group_='" . $_POST['OriginalAccountGroup'] . "'";
	$ErrMsg = _('An error occurred in moving the account group');
	$DbgMsg = _('The SQL that was used to move the account group was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo '<div class="toplink">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">', _('Review Account Groups'), '</a>
		</div>';
	prnMsg(_('All accounts in the account group') . ': ' . $_POST['OriginalAccountGroup'] . ' ' . _('have been changed to the account group') . ': ' . $_POST['DestinyAccountGroup'], 'success');
	echo '<p class="page_title_text noPrint" >
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', $Title, '
		</p>';
} //isset($_POST['MoveGroup'])

if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$SQL = "SELECT count(groupname)
			FROM accountgroups
			WHERE groupname='" . $_POST['GroupName'] . "'";

	$DbgMsg = _('The SQL that was used to retrieve the information was');
	$ErrMsg = _('Could not check whether the group exists because');

	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] != 0 and $_POST['SelectedAccountGroup'] == '') {
		$InputError = 1;
		prnMsg(_('The account group name already exists in the database'), 'error');
	} //$MyRow[0] != 0 and $_POST['SelectedAccountGroup'] == ''
	if (mb_strlen($_POST['GroupName']) == 0) {
		$InputError = 1;
		prnMsg(_('The account group name must be at least one character long'), 'error');
	} //mb_strlen($_POST['GroupName']) == 0
	if ($_POST['ParentGroupName'] != '') {
		if (CheckForRecursiveGroup($_POST['GroupName'], $_POST['ParentGroupName'])) {
			$InputError = 1;
			prnMsg(_('The parent account group selected appears to result in a recursive account structure - select an alternative parent account group or make this group a top level account group'), 'error');
		} else {
			$SQL = "SELECT pandl,
						sequenceintb,
						sectioninaccounts
					FROM accountgroups
					WHERE groupname='" . $_POST['ParentGroupName'] . "'";

			$DbgMsg = _('The SQL that was used to retrieve the information was');
			$ErrMsg = _('Could not check whether the group is recursive because');

			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			$ParentGroupRow = DB_fetch_array($Result);
			$_POST['SequenceInTB'] = $ParentGroupRow['sequenceintb'];
			$_POST['PandL'] = $ParentGroupRow['pandl'];
			$_POST['SectionInAccounts'] = $ParentGroupRow['sectioninaccounts'];
			prnMsg(_('Since this account group is a child group, the sequence in the trial balance, the section in the accounts and whether or not the account group appears in the balance sheet or profit and loss account are all properties inherited from the parent account group. Any changes made to these fields will have no effect.'), 'warn');
		}
	} //$_POST['ParentGroupName'] != ''
	if (!ctype_digit($_POST['SectionInAccounts'])) {
		$InputError = 1;
		prnMsg(_('The section in accounts must be an integer'), 'error');
	} //!ctype_digit($_POST['SectionInAccounts'])
	if (!ctype_digit($_POST['SequenceInTB'])) {
		$InputError = 1;
		prnMsg(_('The sequence in the trial balance must be an integer'), 'error');
	} //!ctype_digit($_POST['SequenceInTB'])
	if (!ctype_digit($_POST['SequenceInTB']) or $_POST['SequenceInTB'] > 10000) {
		$InputError = 1;
		prnMsg(_('The sequence in the TB must be numeric and less than') . ' 10,000', 'error');
	} //!ctype_digit($_POST['SequenceInTB']) or $_POST['SequenceInTB'] > 10000

	$SQL = "SELECT COUNT(pandl) AS porl
				FROM accountgroups
				WHERE sectioninaccounts='" . $_POST['SectionInAccounts'] . "'
					AND pandl='" . ((int) ($_POST['PandL'] xor 1)) . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['porl'] > 0) {
		$InputError = 1;
		prnMsg(_('You are trying to mix Balance Sheet groups with P and L groups within the same Account Section'), 'error');
	}

	if (isset($_POST['OldGroupName']) and $InputError != 1) {
		/*SelectedAccountGroup could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the delete code below*/
		if ($_POST['OldGroupName'] !== $_POST['GroupName']) {
			DB_IgnoreForeignKeys();
			$SQL = "UPDATE chartmaster SET group_='" . $_POST['GroupName'] . "' WHERE group_='" . $_POST['OldGroupName'] . "'";
			$ErrMsg = _('An error occurred in renaming the account group');
			$DbgMsg = _('The SQL that was used to rename the account group was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			$SQL = "UPDATE accountgroups SET parentgroupname='" . $_POST['GroupName'] . "' WHERE parentgroupname='" . $_POST['OldGroupName'] . "'";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			DB_ReinstateForeignKeys();
		}
		$SQL = "UPDATE accountgroups SET groupname='" . $_POST['GroupName'] . "',
										sectioninaccounts='" . $_POST['SectionInAccounts'] . "',
										pandl='" . $_POST['PandL'] . "',
										sequenceintb='" . $_POST['SequenceInTB'] . "',
										parentgroupname='" . $_POST['ParentGroupName'] . "'
									WHERE groupname = '" . $_POST['SelectedAccountGroup'] . "'";
		$ErrMsg = _('An error occurred in updating the account group');
		$DbgMsg = _('The SQL that was used to update the account group was');
		$Msg = _('Record Updated');
	} elseif ($InputError != 1) {
		/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account group form */

		$SQL = "INSERT INTO accountgroups ( groupname,
											sectioninaccounts,
											sequenceintb,
											pandl,
											parentgroupname
										) VALUES (
											'" . $_POST['GroupName'] . "',
											'" . $_POST['SectionInAccounts'] . "',
											'" . $_POST['SequenceInTB'] . "',
											'" . $_POST['PandL'] . "',
											'" . $_POST['ParentGroupName'] . "')";
		$ErrMsg = _('An error occurred in inserting the account group');
		$DbgMsg = _('The SQL that was used to insert the account group was');
		$Msg = _('Record inserted');
	} //$InputError != 1

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg($Msg, 'success');
		unset($_POST['SelectedAccountGroup']);
		unset($_POST['GroupName']);
		unset($_POST['SequenceInTB']);
	} //$InputError != 1
} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartMaster'

	$SQL = "SELECT COUNT(group_) AS groups FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
	$ErrMsg = _('An error occurred in retrieving the group information from chartmaster');
	$DbgMsg = _('The SQL that was used to retrieve the information was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['groups'] > 0) {
		prnMsg(_('Cannot delete this account group because general ledger accounts have been created using this group'), 'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow['groups'] . ' ' . _('general ledger accounts that refer to this account group');
		echo '<form onSubmit="VerifyForm(this)" method="post" class="noPrint" id="AccountGroups" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';

		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="OriginalAccountGroup" value="' . $_GET['SelectedAccountGroup'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<td>', _('Parent Group'), ':', '</td>
				<td><select minlength="0" tabindex="2" name="DestinyAccountGroup">';

		$SQL = "SELECT groupname FROM accountgroups";
		$GroupResult = DB_query($SQL, $ErrMsg, $DbgMsg);
		while ($GroupRow = DB_fetch_array($GroupResult)) {
			if (isset($_POST['ParentGroupName']) and $_POST['ParentGroupName'] == $GroupRow['groupname']) {
				echo '<option selected="selected" value="', htmlentities($GroupRow['groupname'], ENT_QUOTES, 'UTF-8'), '">', htmlentities($GroupRow['groupname'], ENT_QUOTES, 'UTF-8'), '</option>';
			} else {
				echo '<option value="', htmlentities($GroupRow['groupname'], ENT_QUOTES, 'UTF-8'), '">', htmlentities($GroupRow['groupname'], ENT_QUOTES, 'UTF-8'), '</option>';
			}
		} //$GroupRow = DB_fetch_array($GroupResult)
		echo '</select>
					</td>
				</tr>
			</table>';
		echo '<div class="centre">
				<input tabindex="6" type="submit" name="MoveGroup" value="', _('Move Group'), '" />
			</div>';

	} else {
		$SQL = "SELECT COUNT(groupname) groupnames FROM accountgroups WHERE parentgroupname = '" . $_GET['SelectedAccountGroup'] . "'";
		$ErrMsg = _('An error occurred in retrieving the parent group information');
		$DbgMsg = _('The SQL that was used to retrieve the information was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow['groupnames'] > 0) {
			prnMsg(_('Cannot delete this account group because it is a parent account group of other account group(s)'), 'warn');
			echo '<br />', _('There are'), ' ', $MyRow['groupnames'], ' ', _('account groups that have this group as its/there parent account group');

		} else {
			$SQL = "DELETE FROM accountgroups WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";
			$ErrMsg = _('An error occurred in deleting the account group');
			$DbgMsg = _('The SQL that was used to delete the account group was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg($_GET['SelectedAccountGroup'] . ' ' . _('group has been deleted') . '!', 'success');
		}

	} //end if account group used in GL accounts

} //isset($_GET['delete'])

if (!isset($_GET['SelectedAccountGroup']) and !isset($_POST['SelectedAccountGroup'])) {
	/* An account group could be posted when one has been edited and is being updated or GOT when selected for modification
	SelectedAccountGroup will exist because it was sent with the page in a GET .
	If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of account groups will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT groupname,
					sectionname,
					sequenceintb,
					pandl,
					parentgroupname
			FROM accountgroups
			LEFT JOIN accountsection ON sectionid = sectioninaccounts
			ORDER BY sequenceintb";

	$DbgMsg = _('The sql that was used to retrieve the account group information was ');
	$ErrMsg = _('Could not get account groups because');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo '<p class="page_title_text noPrint" >
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', $Title, '
		</p>';

	echo '<table class="selection">
			<tr>
				<th class="SortableColumn">', _('Group Name'), '</th>
				<th class="SortableColumn">', _('Section'), '</th>
				<th class="SortableColumn">', _('Sequence In TB'), '</th>
				<th>', _('Profit and Loss'), '</th>
				<th>', _('Parent Group'), '</th>
				<th colspan="2"></th>
			</tr>';

	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_array($Result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		switch ($MyRow['pandl']) {
			case -1:
				$PandLText = _('Yes');
				break;
			case 1:
				$PandLText = _('Yes');
				break;
			case 0:
				$PandLText = _('No');
				break;
		} //end of switch statement

		echo '<td>', $MyRow['groupname'], '</td>
			<td>', $MyRow['sectionname'], '</td>
			<td class="number">', $MyRow['sequenceintb'], '</td>
			<td>', $PandLText, '</td>
			<td>', $MyRow['parentgroupname'], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($MyRow['groupname']), ENT_QUOTES, 'UTF-8'), '">', _('Edit'), '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($MyRow['groupname']), ENT_QUOTES, 'UTF-8'), '&amp;delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this account group?') . '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
		</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!


if (isset($_POST['SelectedAccountGroup']) or isset($_GET['SelectedAccountGroup'])) {
	echo '<div class="toplink">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Review Account Groups'), '</a>
		</div>';
} //isset($_POST['SelectedAccountGroup']) or isset($_GET['SelectedAccountGroup'])

if (!isset($_GET['delete'])) {
	echo '<form onSubmit="return VerifyForm(this)" method="post" class="noPrint" id="AccountGroups" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (isset($_GET['SelectedAccountGroup'])) {
		//editing an existing account group

		$SQL = "SELECT groupname,
						sectioninaccounts,
						sequenceintb,
						pandl,
						parentgroupname
				FROM accountgroups
				WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";

		$ErrMsg = _('An error occurred in retrieving the account group information');
		$DbgMsg = _('The SQL that was used to retrieve the account group and that failed in the process was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		if (DB_num_rows($Result) == 0) {
			prnMsg(_('The account group name does not exist in the database'), 'error');
			include('includes/footer.inc');
			exit;
		} //DB_num_rows($Result) == 0
		$MyRow = DB_fetch_array($Result);

		$_POST['GroupName'] = $MyRow['groupname'];
		$_POST['SectionInAccounts'] = $MyRow['sectioninaccounts'];
		$_POST['SequenceInTB'] = $MyRow['sequenceintb'];
		$_POST['PandL'] = $MyRow['pandl'];
		$_POST['ParentGroupName'] = $MyRow['parentgroupname'];

		echo '<p class="page_title_text noPrint"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '<br /></p>';

		echo '<input type="hidden" name="SelectedAccountGroup" value="', stripslashes($_GET['SelectedAccountGroup']), '" /></td>
				<input type="hidden" name="OldGroupName" value="', $_POST['GroupName'], '" />';

		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">', _('Edit Account Group Details'), '</th>
			</tr>';

	} elseif (!isset($_POST['MoveGroup'])) { //end of if $_POST['SelectedAccountGroup'] only do the else when a new record is being entered

		if (!isset($_POST['SelectedAccountGroup'])) {
			$_POST['SelectedAccountGroup'] = '';
		} //!isset($_POST['SelectedAccountGroup'])
		if (!isset($_POST['GroupName'])) {
			$_POST['GroupName'] = '';
		} //!isset($_POST['GroupName'])
		if (!isset($_POST['SectionInAccounts'])) {
			$_POST['SectionInAccounts'] = '';
		} //!isset($_POST['SectionInAccounts'])
		if (!isset($_POST['SequenceInTB'])) {
			$_POST['SequenceInTB'] = '';
		} //!isset($_POST['SequenceInTB'])
		if (!isset($_POST['PandL'])) {
			$_POST['PandL'] = '';
		} //!isset($_POST['PandL'])

		echo '<input type="hidden" name="SelectedAccountGroup" value="', $_POST['SelectedAccountGroup'], '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">', _('New Account Group Details'), '</th>
			</tr>';
	} //!isset($_POST['MoveGroup'])
	echo '<tr>
			<td>', _('Account Group Name'), ':</td>
			<td><input tabindex="1" type="text" name="GroupName" size="50" autofocus="autofocus" required="required" minlength="3" maxlength="50" value="', stripslashes($_POST['GroupName']), '" /></td>
		</tr>';

	$SQL = "SELECT groupname FROM accountgroups";
	$GroupResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo '<tr>
			<td>', _('Parent Group'), ':</td>
			<td><select minlength="0" tabindex="2" name="ParentGroupName">';

	if (!isset($_POST['ParentGroupName'])) {
		echo '<option selected="selected" value="">' . _('Top Level Group') . '</option>';
	} else {
		echo '<option value="">' . _('Top Level Group') . '</option>';
	}

	while ($GroupRow = DB_fetch_array($GroupResult)) {
		if (isset($_POST['ParentGroupName']) and $_POST['ParentGroupName'] == $GroupRow['groupname']) {
			echo '<option selected="selected" value="', htmlspecialchars($GroupRow['groupname'], ENT_QUOTES, 'UTF-8'), '">', $GroupRow['groupname'], '</option>';
		} else {
			echo '<option value="', htmlspecialchars($GroupRow['groupname'], ENT_QUOTES, 'UTF-8'), '">', $GroupRow['groupname'], '</option>';
		}
	} //$GroupRow = DB_fetch_array($GroupResult)
	echo '</select>
				</td>
			</tr>';

	$SQL = "SELECT sectionid, sectionname FROM accountsection ORDER BY sectionid";
	$SecResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo '<tr>
			<td>', _('Section In Accounts'), ':</td>
			<td><select required="required" minlength="1" tabindex="3" name="SectionInAccounts">';
	echo '<option value=""></option>';
	while ($SecRow = DB_fetch_array($SecResult)) {
		if ($_POST['SectionInAccounts'] == $SecRow['sectionid']) {
			echo '<option selected="selected" value="', $SecRow['sectionid'], '">', $SecRow['sectionname'], ' (' . $SecRow['sectionid'], ')</option>';
		} else {
			echo '<option value="', $SecRow['sectionid'], '">', $SecRow['sectionname'], ' (', $SecRow['sectionid'], ')</option>';
		}
	} //$SecRow = DB_fetch_array($SecResult)
	echo '</select>
				</td>
			</tr>';

	echo '<tr>
			<td>', _('Profit and Loss'), ':</td>
			<td><select required="required" minlength="1" tabindex="4" name="PandL">';

	echo '<option selected="selected" value=""></option>';
	if ($_POST['PandL'] == 1) {
		echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	} else {
		echo '<option value="1">', _('Yes'), '</option>';
	}
	if ($_POST['PandL'] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>';
	} else {
		echo '<option value="0">', _('No'), '</option>';
	}

	echo '</select>
				</td>
			</tr>';

	echo '<tr>
			<td>', _('Sequence In TB'), ':</td>
			<td><input tabindex="5" type="text" required="required" minlength="1" maxlength="4" name="SequenceInTB" class="integer" value="', $_POST['SequenceInTB'], '" /></td>
		</tr>';

	echo '</table>';

	echo '<div class="centre">
			<input tabindex="6" type="submit" name="submit" value="', _('Enter Information'), '" />
		</div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record
include('includes/footer.inc');
?>