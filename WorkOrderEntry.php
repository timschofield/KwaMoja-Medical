<?php

include('includes/SQL_CommonFunctions.inc');
include('includes/DefineWOClass.php');

include('includes/session.inc');

if (empty($_GET['identifier'])) {
	$Identifier = date('U');
} //empty($_GET['identifier'])
else {
	$Identifier = $_GET['identifier'];
}

$Title = _('Work Order Entry');
include('includes/header.inc');

if (isset($_GET['Delete'])) {
	$_SESSION['WorkOrder' . $Identifier]->RemoveItemFromOrder($_GET['Delete']);
}

if (isset($_POST['NewItems'])) {
	foreach ($_POST as $Key => $Value) {
		if (substr($Key, 0, 7) == 'StockID') {
			$Index = substr($Key, 7);
			if ($_POST['Quantity' . $Index] > 0) {
				$_SESSION['WorkOrder' . $Identifier]->AddItemToOrder($_POST['StockID' . $Index], '', $_POST['Quantity' . $Index], 0, '');
			}
		}
	}
}

if (isset($LocCode)) {
	$LocSql = "SELECT locations.loccode
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1
					WHERE locations.loccode='" . $LocCode . "'
						AND locations.usedforwo = 1";
	$LocResult = DB_query($LocSql);
	$LocRow = DB_fetch_array($LocResult);

	if (is_null($LocRow['loccode']) or $LocRow['loccode'] == ''){
		prnMsg(_('Your security settings do not allow you to create or update new Work Order at this location') . ' ' . $LocCode, 'error');
		echo '<br /><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select an existing work order') . '</a>';
		include('includes/footer.inc');
		exit;
	}
}
if (isset($_POST['RequiredBy']) and !isset($_POST['NewItems'])) {
	$_SESSION['WorkOrder' . $Identifier]->RequiredBy = $_POST['RequiredBy'];
	$_SESSION['WorkOrder' . $Identifier]->StartDate = $_POST['StartDate'];
	$_SESSION['WorkOrder' . $Identifier]->LocationCode = $_POST['StockLocation'];

	if (isset($_POST['OutputItem1'])) {
		foreach ($_SESSION['WorkOrder' . $Identifier]->Items as $i => $Item) {
			$_SESSION['WorkOrder' . $Identifier]->UpdateItem($_POST['OutputItem' . $Item->LineNumber], $_POST['WOComments' . $Item->LineNumber], $_POST['OutputQty' . $Item->LineNumber], $_POST['NextLotSNRef' . $Item->LineNumber]);
		}
	}
}

echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['Save'])) {
	$_SESSION['WorkOrder' . $Identifier]->Save();
	prnMsg(_('Works order number') . ' ' . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . ' ' . _('has been successfully saved to the database'), ('success'));
	include('includes/footer.inc');
	exit;
}

if (isset($_GET['New'])) {
	$_SESSION['WorkOrder' . $Identifier] = new WorkOrder();
}

if (isset($_GET['WO'])) {
	$_SESSION['WorkOrder' . $Identifier] = new WorkOrder();
	$_SESSION['WorkOrder' . $Identifier]->Load($_GET['WO']);
}

echo '<form onSubmit="return VerifyForm(this);" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" name="MainForm">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<tr>
			<th colspan="2">' . _('Work Order Details') . '</td>
		</tr>';

echo '<tr>
		<td class="label">' . _('Location where items are to be received into') . ':</td>
		<td><select minlength="0" name="StockLocation" onChange="ReloadForm(Refresh)">';

$SQL = "SELECT locationname,
				locations.loccode
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canupd=1
			WHERE locations.usedforwo = 1";
$LocResult = DB_query($SQL);

while ($LocRow = DB_fetch_array($LocResult)) {
	if ($_SESSION['WorkOrder' . $Identifier]->LocationCode == $LocRow['loccode']) {
		echo '<option selected="True" value="' . $LocRow['loccode'] . '">' . $LocRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $LocRow['loccode'] . '">' . $LocRow['locationname'] . '</option>';
	}
}
echo '</select>
			</td>
		</tr>';

if (!isset($_POST['StartDate'])) {
	$_POST['StartDate'] = Date($_SESSION['DefaultDateFormat']);
}

echo '<tr>
		<td class="label">' . _('Start Date') . ':</td>
		<td><input type="text" name="StartDate" size="12" minlength="0" maxlength="12" value="' . $_SESSION['WorkOrder' . $Identifier]->StartDate . '" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" /></td>
	</tr>';

if (!isset($_POST['RequiredBy'])) {
	$_POST['RequiredBy'] = Date($_SESSION['DefaultDateFormat']);
}

echo '<tr>
		<td class="label">' . _('Required By') . ':</td>
		<td><input type="text" name="RequiredBy" size="12" minlength="0" maxlength="12" value="' . $_SESSION['WorkOrder' . $Identifier]->RequiredBy . '" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" /></td>
	</tr>';

if ($_SESSION['WorkOrder' . $Identifier]->CostIssued > 0) {
	echo '<tr>
			<td class="label">' . _('Accumulated Costs') . ':</td>
			<td class="number">' . locale_number_format($MyRow['costissued'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
}

echo '</table>';

echo '<table class="selection">
		<tr>
			<th colspan="8"><h3>' . _('Items to be manufactured') . '
				<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/add.png" class="PrintIcon noPrint" title="' . _('Add items to the work order') . '" alt="' . _('Add items to the work order') . '" onclick="ShowTable(\'ItemSelect\');" />
			</h3></th>
		</tr>
		<tr>
			<th>' . _('Output Item') . '</th>
			<th>' . _('Comment') . '</th>
			<th>' . _('Qty Required') . '</th>
			<th>' . _('Qty Received') . '</th>
			<th>' . _('Balance Remaining') . '</th>
			<th>' . _('Next Lot/SN Ref') . '</th>
		</tr>';
$j = 0;

if ($_SESSION['WorkOrder' . $Identifier]->NumberOfItems > 0) {
	foreach ($_SESSION['WorkOrder' . $Identifier]->Items as $i => $WOItem) {
		if ($j == 1) {
			echo '<tr class="OddTableRows">';
			$j = 0;
		} else {
			echo '<tr class="EvenTableRows">';
			++$j;
		}
		echo '<td>
				<input type="hidden" name="OutputItem' . $WOItem->LineNumber . '" value="' . $WOItem->StockID . '" />' . $WOItem->StockID . ' - ' . $WOItem->Description . '
			</td>
			<td>
				<textarea style="width:100%" rows="3" cols="40" name="WOComments' . $i . '" >' . $WOItem->Comments . '</textarea>
			</td>';
		if ($WOItem->Controlled == 1 and $_SESSION['DefineControlledOnWOEntry'] == 1) {
			echo '<td class="number">' . locale_number_format($_POST['OutputQty' . $i], $_POST['DecimalPlaces' . $i]) . '</td>';
		} else {
			echo '<td><input type="text" class="number" name="OutputQty' . $WOItem->LineNumber . '" value="' . locale_number_format($WOItem->QuantityRequired - $WOItem->QuantityReceived, $WOItem->DecimalPlaces) . '" size="10" required="required" minlength="1" maxlength="10" /></td>';
		}
		echo '<td class="number"><input type="hidden" name="RecdQty' . $WOItem->LineNumber . '" value="' . locale_number_format($WOItem->QuantityReceived, $WOItem->DecimalPlaces) . '" />' . locale_number_format($WOItem->QuantityReceived, $WOItem->DecimalPlaces) . '</td>
		  		<td class="number">' . locale_number_format($WOItem->QuantityRequired - $WOItem->QuantityReceived, $WOItem->DecimalPlaces) . '</td>';
		if ($WOItem->Controlled == 1) {
			echo '<td><input type="text" name="NextLotSNRef' . $WOItem->LineNumber . '" value="' . $WOItem->NextLotSerialNumbers . '" /></td>';
			if ($_SESSION['DefineControlledOnWOEntry'] == 1) {
				if ($_POST['Serialised' . $i] == 1) {
					$LotOrSN = _('S/Ns');
				} else {
					$LotOrSN = _('Batches');
				}
				echo '<td><a href="' . $RootPath . '/WOSerialNos.php?WO=' . urlencode($_SESSION['WorkOrder' . $Identifier]->OrderNumber) . '&StockID=' . urlencode($_POST['OutputItem' . $i]) . '&Description=' . urlencode($_POST['OutputItemDesc' . $i]) . '&Serialised=' . urlencode($_POST['Serialised' . $i]) . '&NextSerialNo=' . urlencode($_POST['NextLotSNRef' . $i]) . '">' . $LotOrSN . '</a></td>';
			}
		}
		echo '<td>';
		if ($_SESSION['WorkOrder' . $Identifier]->OrderNumber != 0) {
			wikiLink('WorkOrder', $_SESSION['WorkOrder' . $Identifier]->OrderNumber . $WOItem->StockID);
		}
		echo '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '&Delete=' . $WOItem->LineNumber . '">' . _('Delete') . '</a>
				</td>
			</tr>';
	}
	echo '<input type="hidden" name="NumberOfOutputs" value="' . ($i) . '" />';
}
echo '</table>';

echo '<table class="selection print">
		<tr>
			<th colspan="7"><h3>' . _('Requirements for order') . '
				<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon noPrint" title="' . _('Print Requirements') . '" alt="' . _('Print Requirements') . '" onclick="window.print();" />
			</h3></th>
		</tr>
		<tr>
			<th>' . _('Parent Item') . '</th>
			<th>' . _('Component Item') . '</th>
			<th>' . _('Qty Required') . '</th>
			<th>' . _('Standard Cost') . '</th>
			<th>' . _('Auto Issue') . '</th>
		</tr>';

foreach ($_SESSION['WorkOrder' . $Identifier]->Items as $i => $WOItem) {
	$WOItem->RefreshRequirements($_SESSION['WorkOrder' . $Identifier]->LocationCode);
	foreach ($WOItem->Requirements as $j => $WORequirement) {
		if ($WORequirement->AutoIssue == 0) {
			$AutoIssue = _('No');
		} else {
			$AutoIssue = _('Yes');
		}
		echo '<tr class="EvenTableRows">
				<td>' . $WORequirement->ParentStockID . '</td>
				<td>' . $WORequirement->StockID . '</td>
				<td class="number">' . locale_number_format($WORequirement->Quantity, $WORequirement->DecimalPlaces) . '</td>
				<td class="number">' . $WORequirement->StandardCost . '</td>
				<td>' . $AutoIssue . '</td>
			</tr>';
	}
}
echo '</table>';

echo '<div class="centre"><input type="submit" name="Refresh" value="' . _('Refresh Details') . '" /></div>';

echo '<div class="centre"><input type="submit" name="Save" value="' . _('Save Details') . '" /></div>';

$SQL = "SELECT categoryid,
			categorydescription
		FROM stockcategory
		WHERE stocktype='F' OR stocktype='M'
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);

echo '<table class="search" id="ItemSelect">
		<tr>
			<td>' . _('Select a stock category') . ':<select minlength="0" name="StockCat">';

if (!isset($_POST['StockCat'])) {
	echo '<option selected="True" value="All">' . _('All') . '</option>';
	$_POST['StockCat'] = 'All';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}

while ($MyRow1 = DB_fetch_array($Result1)) {

	if ($_POST['StockCat'] == $MyRow1['categoryid']) {
		echo '<option selected="True" value=' . $MyRow1['categoryid'] . '>' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value=' . $MyRow1['categoryid'] . '>' . $MyRow1['categorydescription'] . '</option>';
	}
}

if (!isset($_POST['Keywords'])) {
	$_POST['Keywords'] = '';
}

if (!isset($_POST['StockCode'])) {
	$_POST['StockCode'] = '';
}

echo '</select></td>
		<td>' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</td>
		<td><input type="text" name="Keywords" size="20" minlength="0" maxlength="25" value="' . $_POST['Keywords'] . '" /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><font size="3"><b>' . _('OR') . ' </b></font>' . _('Enter extract of the') . ' <b>' . _('Stock Code') . '</b>:</td>
		<td><input type="text" name="StockCode" size="15" minlength="0" maxlength="18" value="' . $_POST['StockCode'] . '" /></td>
	</tr>
	<tr>
		<th colspan="3">
			<button type="submit" name="Search">' . _('Search Now') . '</button>
		</th>
	</tr>
	</table>';
/* End of the item criteria search form */


if (isset($_POST['Search'])) {

	//insert wildcard characters in spaces
	$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
	$DescriptionSearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
	$CodeSearchString = '%' . $_POST['StockCode'] . '%';

	if ($_POST['StockCat'] == 'All') {
		$_POST['StockCat'] = '%';
	}

	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.units
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='M')
					AND stockmaster.stockid " . LIKE . " '" . $CodeSearchString . "'
					AND stockmaster.description " . LIKE . " '" . $DescriptionSearchString . "'
					AND stockmaster.categoryid " . LIKE . " '" . $_POST['StockCat'] . "'
					AND stockmaster.discontinued=0
					AND mbflag='M'
				ORDER BY stockmaster.stockid
				LIMIT " . $_SESSION['DisplayRecordsMax'];

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(_('There are no products available meeting the criteria specified'), 'info');

		if ($Debug == 1) {
			prnMsg(_('The SQL statement used was') . ':<br />' . $SQL, 'info');
		}
	}
} //end of if search

if (isset($SearchResult)) {

	if (DB_num_rows($SearchResult) > 0) {

		echo '<table cellpadding="2" class="selection">
				<tr>
					<th>' . _('Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Units') . '</th>
					<th>' . _('Image') . '</th>
					<th>' . _('Quantity') . '</th>
				</tr>';
		$k = 0; //row colour counter
		$ItemCodes = array();
		foreach ($_SESSION['WorkOrder' . $Identifier]->Items as $WOItem) {
			$ItemCodes[] = $WOItem->StockID;
		}

		$LineNumber = 1;
		while ($MyRow = DB_fetch_array($SearchResult)) {

			if (!in_array($MyRow['stockid'], $ItemCodes)) {
				if (function_exists('imagecreatefrompng')) {
					$ImageSource = '<img title="' . $MyRow['longdescription'] . '" src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($MyRow['stockid']) . '&text=&width=64&height=64" />';
				} else {
					if (file_exists($_SERVER['DOCUMENT_ROOT'] . $RootPath . '/' . $_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.jpg')) {
						$ImageSource = '<img src="' . $_SERVER['DOCUMENT_ROOT'] . $RootPath . '/' . $_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.jpg" alt="' . _('No Image') . '" />';
					} else {
						$ImageSource = _('No Image');
					}
				}

				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}

				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<input type="hidden" name="StockID' . $LineNumber . '" value="%s" />
						<td><input type="text" required="required" minlength="1" maxlength="11" class="number" name="Quantity' . $LineNumber . '" value="0" /></td>
						</tr>', $MyRow['stockid'], $MyRow['description'], $MyRow['units'], $ImageSource, $MyRow['stockid']);
			} //end if not already on work order
			$LineNumber++;
		} //end of while loop
	} //end if more than 1 row to show
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="NewItems" value="Add to Work Order" />
		</div>
	</form>';
} #end if SearchResults to show

include('includes/footer.inc');

?>