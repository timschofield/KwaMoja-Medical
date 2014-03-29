<?php

include('includes/session.inc');
$Title = _('Internal Stock Categories Requests By Security Role Maintenance ');

include('includes/header.inc');

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . _('Payment Entry') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
} else {
	$SelectedType = '';
}

if (!isset($_GET['delete']) and (ContainsIllegalCharacters($SelectedType) or mb_strpos($SelectedType, ' ') > 0)) {
	$InputError = 1;
	prnMsg(_('The Selected type cannot contain any of the following characters') . ' " \' - &amp; ' . _('or a space'), 'error');
}
if (isset($_POST['SelectedRole'])) {
	$SelectedRole = mb_strtoupper($_POST['SelectedRole']);
} elseif (isset($_GET['SelectedRole'])) {
	$SelectedRole = mb_strtoupper($_GET['SelectedRole']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedRole);
	unset($SelectedType);
}

if (isset($_POST['Process'])) {

	if ($_POST['SelectedRole'] == '') {
		echo prnMsg(_('You have not selected a security role to maintain the internal stock categories on'), 'error');
		echo '<br />';
		unset($SelectedRole);
		unset($_POST['SelectedRole']);
	}
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if ($_POST['SelectedCategory'] == '') {
		$InputError = 1;
		echo prnMsg(_('You have not selected a stock category to be added as internal to this security role'), 'error');
		echo '<br />';
		unset($SelectedRole);
	}

	if ($InputError != 1) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
				 FROM internalstockcatrole
				 WHERE secroleid= '" . $_POST['SelectedRole'] . "'
				 AND categoryid = '" . $_POST['SelectedCategory'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ($checkrow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The Stock Category') . ' ' . $_POST['categoryid'] . ' ' . _('already allowed as internal for this security role'), 'error');
		} else {
			// Add new record on submit
			$sql = "INSERT INTO internalstockcatrole (secroleid,
												categoryid)
										VALUES ('" . $_POST['SelectedRole'] . "',
												'" . $_POST['SelectedCategory'] . "')";

			$msg = _('Stock Category') . ': ' . stripslashes($_POST['SelectedCategory']) . ' ' . _('has been allowed to user role') . ' ' . $_POST['SelectedRole'] . ' ' . _('as internal');
			$checkSql = "SELECT count(secroleid)
							FROM securityroles";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);
		}
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$result = DB_query($sql);
		prnMsg($msg, 'success');
		unset($_POST['SelectedCategory']);
	}

} elseif (isset($_GET['delete'])) {
	$sql = "DELETE FROM internalstockcatrole
		WHERE secroleid='" . $SelectedRole . "'
		AND categoryid='" . $SelectedType . "'";

	$ErrMsg = _('The Stock Category by Role record could not be deleted because');
	$result = DB_query($sql, $ErrMsg);
	prnMsg(_('Internal Stock Category') . ' ' . stripslashes($SelectedType) . ' ' . _('for user role') . ' ' . $SelectedRole . ' ' . _('has been deleted'), 'success');
	unset($_GET['delete']);
}

if (!isset($SelectedRole)) {

	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">'; //Main table

	echo '<tr><td>' . _('Select User Role') . ':</td><td><select required="required" minlength="1" name="SelectedRole">';

	$SQL = "SELECT secroleid,
					secrolename
			FROM securityroles";

	$result = DB_query($SQL);
	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($SelectedRole) and $myrow['secroleid'] == $SelectedRole) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['secroleid'] . '">' . $myrow['secroleid'] . ' - ' . $myrow['secrolename'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';

	echo '</table>'; // close main table
	DB_free_result($result);

	echo '<div class="centre"><input type="submit" name="Process" value="' . _('Accept') . '" />
				<input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>';

	echo '</form>';

}

//end of ifs and buts!
if (isset($_POST['process']) or isset($SelectedRole)) {

	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Stock Categories available as internal for role') . ' ' . $SelectedRole . '</a></div>';
	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="SelectedRole" value="' . $SelectedRole . '" />';

	$sql = "SELECT internalstockcatrole.categoryid,
					stockcategory.categorydescription
			FROM internalstockcatrole INNER JOIN stockcategory
			ON internalstockcatrole.categoryid=stockcategory.categoryid
			WHERE internalstockcatrole.secroleid='" . $SelectedRole . "'
			ORDER BY internalstockcatrole.categoryid ASC";

	$result = DB_query($sql);

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="3"><h3>' . _('Internal Stock Categories Allowed to user role') . ' ' . $SelectedRole . '</h3></th>
		</tr>
		<tr>
			<th>' . _('Category Code') . '</th>
			<th>' . _('Description') . '</th>
		</tr>';

	$k = 0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		echo '<td>' . $myrow['categoryid'] . '</td>
			<td>' . $myrow['categorydescription'] . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedType=' . urlencode($myrow['categoryid']) . '&amp;delete=yes&amp;SelectedRole=' . urlencode($SelectedRole) . '" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this internal stock category code?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (!isset($_GET['delete'])) {


		echo '<table  class="selection">'; //Main table

		echo '<tr><td>' . _('Select Stock Category Code') . ':</td><td><select minlength="0" name="SelectedCategory">';

		$SQL = "SELECT categoryid,
						categorydescription
				FROM stockcategory";

		$result = DB_query($SQL);
		if (!isset($_POST['SelectedCategory'])) {
			echo '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
		}
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_POST['SelectedCategory']) and $myrow['categoryid'] == $_POST['SelectedCategory']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'] . '</option>';

		} //end while loop

		echo '</select></td></tr>';

		echo '</table>'; // close main table
		DB_free_result($result);

		echo '<div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" />
									<input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>';

		echo '</form>';

	} // end if user wish to delete
}

include('includes/footer.inc');
?>