<?php

include('includes/session.inc');
$Title = _('Supplier Types') . ' / ' . _('Maintenance');
include('includes/header.inc');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Supplier Types') . '" alt="" />' . _('Supplier Type Setup') . '</p>';
echo '<div class="page_help_text noPrint">' . _('Add/edit/delete Supplier Types') . '</div>';

if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i = 1;
	if (mb_strlen($_POST['TypeName']) > 100) {
		$InputError = 1;
		echo prnMsg(_('The supplier type name description must be 100 characters or less long'), 'error');
		$Errors[$i] = 'SupplierType';
		$i++;
	}

	if (mb_strlen(trim($_POST['TypeName'])) == 0) {
		$InputError = 1;
		echo prnMsg(_('The supplier type name description must contain at least one character'), 'error');
		$Errors[$i] = 'SupplierType';
		$i++;
	}

	$checksql = "SELECT count(*)
			 FROM suppliertype
			 WHERE typename = '" . $_POST['TypeName'] . "'";
	$checkresult = DB_query($checksql);
	$checkrow = DB_fetch_row($checkresult);
	if ($checkrow[0] > 0 and isset($_POST['insert'])) {
		$InputError = 1;
		echo prnMsg(_('You already have a supplier type called') . ' ' . $_POST['TypeName'], 'error');
		$Errors[$i] = 'SupplierName';
		$i++;
	}

	if (isset($_POST['update']) and $InputError != 1) {

		$SQL = "UPDATE suppliertype
			SET typename = '" . $_POST['TypeName'] . "'
			WHERE typeid = '" . $SelectedType . "'";

		$msg = _('The supplier type') . ' ' . $SelectedType . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		// Add new record on submit

		$SQL = "INSERT INTO suppliertype
					(typename)
				VALUES ('" . $_POST['TypeName'] . "')";

		$msg = _('Supplier type') . ' ' . stripslashes($_POST['TypeName']) . ' ' . _('has been created');
		$checkSql = "SELECT count(typeid)
				 FROM suppliertype";
		$Result = DB_query($checkSql);
		$row = DB_fetch_row($Result);

	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);


		// Fetch the default price list.
		$SQL = "SELECT confvalue
					FROM config
					WHERE confname='DefaultSupplierType'";
		$Result = DB_query($SQL);
		$SupplierTypeRow = DB_fetch_row($Result);
		$DefaultSupplierType = $SupplierTypeRow[0];

		// Does it exist
		$checkSql = "SELECT count(*)
				 FROM suppliertype
				 WHERE typeid = '" . $DefaultSupplierType . "'";
		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		// If it doesnt then update config with newly created one.
		if ($checkrow[0] == 0) {
			$SQL = "UPDATE config
					SET confvalue='" . $_POST['TypeID'] . "'
					WHERE confname='DefaultSupplierType'";
			$Result = DB_query($SQL);
			$_SESSION['DefaultSupplierType'] = $_POST['TypeID'];
		}

		prnMsg($msg, 'success');

		unset($SelectedType);
		unset($_POST['TypeID']);
		unset($_POST['TypeName']);
	}

} elseif (isset($_GET['delete'])) {

	$SQL = "SELECT COUNT(*) FROM suppliers WHERE supptype='" . $SelectedType . "'";

	$ErrMsg = _('The number of suppliers using this Type record could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this type because suppliers are currently set up to use this type') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('suppliers with this type code'));
	} else {

		$SQL = "DELETE FROM suppliertype WHERE typeid='" . $SelectedType . "'";
		$ErrMsg = _('The Type record could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('Supplier type') . $SelectedType . ' ' . _('has been deleted'), 'success');

		unset($SelectedType);
		unset($_GET['delete']);

	}
}

if (!isset($SelectedType)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will
	 *  exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then
	 * none of the above are true and the list of sales types will be displayed with links to delete or edit each. These will call
	 * the same page again and allow update/input or deletion of the records
	 */

	$SQL = "SELECT typeid, typename FROM suppliertype";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th class="SortableColumn">' . _('Type ID') . '</th>
			<th class="SortableColumn">' . _('Type Name') . '</th>
		</tr>';

	$k = 0; //row colour counter

	while ($MyRow = DB_fetch_row($Result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		echo '<td>' . $MyRow[0] . '</td>
				<td>' . $MyRow[1] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedType=' . urlencode($MyRow[0]) . '&Edit=Yes">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedType=' . urlencode($MyRow[0]) . '&amp;delete=yes" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this Supplier Type?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre">
			<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Types Defined') . '</a></p>
		</div>';
}
if (!isset($_GET['delete'])) {

	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">'; //Main table

	// The user wish to EDIT an existing type
	if (isset($SelectedType) and $SelectedType != '') {

		$SQL = "SELECT typeid,
				   typename
				FROM suppliertype
				WHERE typeid='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['TypeID'] = $MyRow['typeid'];
		$_POST['TypeName'] = $MyRow['typename'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />';
		echo '<input type="hidden" name="TypeID" value="' . $_POST['TypeID'] . '" />';

		// We dont allow the user to change an existing type code

		echo '<tr>
				<td>' . _('Type ID') . ': </td>
				<td>' . $_POST['TypeID'] . '</td>
			</tr>';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName'] = '';
	}
	echo '<tr>
			<td>' . _('Type Name') . ':</td>
			<td><input type="text" autofocus="autofocus" required="required" minlength="1" maxlength="100" name="TypeName" value="' . $_POST['TypeName'] . '" /></td>
		</tr>';

	if (isset($_GET['Edit'])) {
		echo '<tr>
				<td colspan="2">
					<div class="centre">
						<input type="submit" name="update" value="' . _('Update Type') . '" />
					</div>
				</td>
			</tr>
		</table>
		</form>';
	} else {
		echo '<tr>
				<td colspan="2">
					<div class="centre">
						<input type="submit" name="insert" value="' . _('Add Type') . '" />
					</div>
				</td>
			</tr>
		</table>
		</form>';
	}

} // end if user wish to delete

include('includes/footer.inc');
?>