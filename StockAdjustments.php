<?php

include('includes/DefineStockAdjustment.php');
include('includes/DefineSerialItems.php');
include('includes/session.inc');
$Title = _('Stock Adjustments');
/* Manual links before header.inc */
$ViewTopic = 'Inventory';
$BookMark = 'InventoryAdjustments';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other adjustment sessions on the same machine  */
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}

if (isset($_GET['NewAdjustment'])) {
	unset($_SESSION['Adjustment' . $identifier]);
	$_SESSION['Adjustment' . $identifier] = new StockAdjustment();
}

if (!isset($_SESSION['Adjustment' . $identifier])) {
	$_SESSION['Adjustment' . $identifier] = new StockAdjustment();
}

$NewAdjustment = false;

if (isset($_GET['StockID'])) {
	$NewAdjustment = true;
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	if ($_POST['StockID'] != $_SESSION['Adjustment' . $identifier]->StockID) {
		$NewAdjustment = true;
		$StockID = trim(mb_strtoupper($_POST['StockID']));
	}
}

if ($NewAdjustment == true) {

	$_SESSION['Adjustment' . $identifier]->StockID = trim(mb_strtoupper($StockID));
	$Result = DB_query("SELECT description,
							controlled,
							serialised,
							decimalplaces,
							perishable,
							stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS totalcost,
							units
						FROM stockmaster
						INNER JOIN stockcosts
							ON stockmaster.stockid=stockcosts.stockid
							AND stockcosts.succeeded=0
						WHERE stockcosts.stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'");
	$MyRow = DB_fetch_array($Result);
	$_SESSION['Adjustment' . $identifier]->ItemDescription = $MyRow['description'];
	$_SESSION['Adjustment' . $identifier]->Controlled = $MyRow['controlled'];
	$_SESSION['Adjustment' . $identifier]->Serialised = $MyRow['serialised'];
	$_SESSION['Adjustment' . $identifier]->DecimalPlaces = $MyRow['decimalplaces'];
	$_SESSION['Adjustment' . $identifier]->SerialItems = array();
	if (!isset($_SESSION['Adjustment' . $identifier]->Quantity) or !is_numeric($_SESSION['Adjustment' . $identifier]->Quantity)) {
		$_SESSION['Adjustment' . $identifier]->Quantity = 0;
	}

	$_SESSION['Adjustment' . $identifier]->PartUnit = $MyRow['units'];
	$_SESSION['Adjustment' . $identifier]->StandardCost = $MyRow['totalcost'];
	$DecimalPlaces = $MyRow['decimalplaces'];
	DB_free_result($Result);


} //end if it's a new adjustment
if (isset($_POST['tag'])) {
	$_SESSION['Adjustment' . $identifier]->tag = $_POST['tag'];
}
if (isset($_POST['Narrative'])) {
	$_SESSION['Adjustment' . $identifier]->Narrative = $_POST['Narrative'];
}

if ($_SESSION['RestrictLocations'] == 0) {
	$SQL = "SELECT locationname,
					loccode
				FROM locations";
} else {
	$SQL = "SELECT locationname,
					loccode
				FROM locations
				INNER JOIN www_users
					ON locations.loccode=www_users.defaultlocation
				WHERE www_users.userid='" . $_SESSION['UserID'] . "'";
}
$ResultStkLocs = DB_query($SQL);
$LocationList = array();
while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	$LocationList[$MyRow['loccode']] = $MyRow['locationname'];
}

if (isset($_POST['StockLocation'])) {
	if ($_SESSION['Adjustment' . $identifier]->StockLocation != $_POST['StockLocation']) {/* User has changed the stock location, so the serial no must be validated again */
		$_SESSION['Adjustment' . $identifier]->SerialItems = array();
	}
	$_SESSION['Adjustment' . $identifier]->StockLocation = $_POST['StockLocation'];
} else {
	if (empty($_SESSION['Adjustment' . $identifier]->StockLocation)) {
		if (empty($_SESSION['UserStockLocation'])) {
			$_SESSION['Adjustment' . $identifier]->StockLocation = key(reset($LocationList));
		} else {
			$_SESSION['Adjustment' . $identifier]->StockLocation = $_SESSION['UserStockLocation'];
		}
	}
}
if (isset($_POST['Quantity'])) {
	if ($_POST['Quantity'] == '' or !is_numeric(filter_number_format($_POST['Quantity']))) {
		$_POST['Quantity'] = 0;
	}
} else {
	$_POST['Quantity'] = 0;
}
if ($_POST['Quantity'] != 0) { //To prevent from serilised quantity changing to zero
	$_SESSION['Adjustment' . $identifier]->Quantity = filter_number_format($_POST['Quantity']);
	if (count($_SESSION['Adjustment' . $identifier]->SerialItems) == 0 and $_SESSION['Adjustment' . $identifier]->Controlled == 1 ) {/* There is no quantity available for controlled items */
		$_SESSION['Adjustment' . $identifier]->Quantity = 0;
	}
}

if (isset($_GET['OldIdentifier'])) {
	$_SESSION['Adjustment' . $identifier]->StockLocation = $_SESSION['Adjustment' . $_GET['OldIdentifier']]->StockLocation;
}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />' . ' ' . _('Inventory Adjustment') . '</p>';

if (isset($_POST['CheckCode'])) {

	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . _('Select Item to Adjust') . '</p>';

	if (mb_strlen($_POST['StockText']) > 0) {
		$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE description " . LIKE . " '%" . $_POST['StockText'] . "%'";
	} else {
		$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'";
	}
	$ErrMsg = _('The stock information cannot be retrieved because');
	$DbgMsg = _('The SQL to get the stock description was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo '<table class="selection">
			<tr>
				<th class="SortableColumn">' . _('Stock Code') . '</th>
				<th class="SortableColumn">' . _('Stock Description') . '</th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td><a href="StockAdjustments.php?StockID=' . urlencode($MyRow[0]) . '&amp;Description=' . urlencode($MyRow[1]) . '&amp;OldIdentifier=' . urlencode($identifier) . '">' . _('Adjust') . '</a>
			</tr>';
	}
	echo '</table>';
	include('includes/footer.inc');
	exit;
}

if (isset($_POST['EnterAdjustment']) and $_POST['EnterAdjustment'] != '') {

	$InputError = false;
	/*Start by hoping for the best */
	$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The entered item code does not exist'), 'error');
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Adjustment' . $identifier]->Quantity)) {
		prnMsg(_('The quantity entered must be numeric'), 'error');
		$InputError = true;
	} elseif (strlen(substr(strrchr($_SESSION['Adjustment' . $identifier]->Quantity, "."), 1)) > $_SESSION['Adjustment' . $identifier]->DecimalPlaces) {
		prnMsg(_('The decimal places input is greater than the number defined for this item. The number of decimal places defined for this item is') . ' ' . $_SESSION['Adjustment' . $identifier]->DecimalPlaces, 1, 'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment' . $identifier]->Quantity == 0) {
		prnMsg(_('The quantity entered cannot be zero') . '. ' . _('There would be no adjustment to make'), 'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment' . $identifier]->Controlled == 1 and count($_SESSION['Adjustment' . $identifier]->SerialItems) == 0) {
		prnMsg(_('The item entered is a controlled item that requires the detail of the serial numbers or batch references to be adjusted to be entered'), 'error');
		$InputError = true;
	}

	if ($_SESSION['ProhibitNegativeStock'] == 1) {
		$SQL = "SELECT quantity FROM locstock
				WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
				AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'";
		$CheckNegResult = DB_query($SQL);
		$CheckNegRow = DB_fetch_array($CheckNegResult);
		if ($CheckNegRow['quantity'] + $_SESSION['Adjustment' . $identifier]->Quantity < 0) {
			$InputError = true;
			prnMsg(_('The system parameters are set to prohibit negative stocks. Processing this stock adjustment would result in negative stock at this location. This adjustment will not be processed.'), 'error');
		}
	}

	if (!$InputError) {

		/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$AdjustmentNumber = GetNextTransNo(17);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
		$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		$Result = DB_Txn_Begin();

		// Need to get the current location quantity will need it later for the stock movement
		$SQL = "SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
			AND loccode= '" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) == 1) {
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}

		$SQL = "INSERT INTO stockmoves (stockid,
										type,
										transno,
										loccode,
										trandate,
										prd,
										reference,
										qty,
										newqoh)
									VALUES (
										'" . $_SESSION['Adjustment' . $identifier]->StockID . "',
										17,
										'" . $AdjustmentNumber . "',
										'" . $_SESSION['Adjustment' . $identifier]->StockLocation . "',
										'" . $SQLAdjustmentDate . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['Adjustment' . $identifier]->Narrative . "',
										'" . $_SESSION['Adjustment' . $identifier]->Quantity . "',
										'" . ($QtyOnHandPrior + $_SESSION['Adjustment' . $identifier]->Quantity) . "'
									)";


		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Adjustment' . $identifier]->Controlled == 1) {
			foreach ($_SESSION['Adjustment' . $identifier]->SerialItems as $Item) {
				/*We need to add or update the StockSerialItem record and
				The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not */
				$SQL = "SELECT COUNT(*)
						FROM stockserialitems
						WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
						AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'
						AND serialno='" . $Item->BundleRef . "'";
				$ErrMsg = _('Unable to determine if the serial item exists');
				$Result = DB_query($SQL, $ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0] == 1) {

					$SQL = "UPDATE stockserialitems SET quantity= quantity + " . $Item->BundleQty . "
							WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
							AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'
							AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
														loccode,
														serialno,
														qualitytext,
														quantity,
														expirationdate)
											VALUES ('" . $_SESSION['Adjustment' . $identifier]->StockID . "',
											'" . $_SESSION['Adjustment' . $identifier]->StockLocation . "',
											'" . $Item->BundleRef . "',
											'',
											'" . $Item->BundleQty . "',
											'" . FormatDateForSQL($Item->ExpiryDate) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}


				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno,
													stockid,
													serialno,
													moveqty)
										VALUES ('" . $StkMoveNo . "',
											'" . $_SESSION['Adjustment' . $identifier]->StockID . "',
											'" . $Item->BundleRef . "',
											'" . $Item->BundleQty . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			}
			/* foreach controlled item in the serialitems array */
		}
		/*end if the adjustment item is a controlled item */



		$SQL = "UPDATE locstock SET quantity = quantity + '" . $_SESSION['Adjustment' . $identifier]->Quantity . "'
				WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
				AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the stock record was used');

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and $_SESSION['Adjustment' . $identifier]->StandardCost > 0) {

			$StockGLCodes = GetStockGLCode($_SESSION['Adjustment' . $identifier]->StockID);

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										amount,
										narrative,
										tag)
								VALUES (17,
									'" . $AdjustmentNumber . "',
									'" . $SQLAdjustmentDate . "',
									'" . $PeriodNo . "',
									'" . $StockGLCodes['adjglact'] . "',
									'" . round($_SESSION['Adjustment' . $identifier]->StandardCost * -($_SESSION['Adjustment' . $identifier]->Quantity), $_SESSION['CompanyRecord']['decimalplaces']) . "',
									'" . $_SESSION['Adjustment' . $identifier]->StockID . " x " . $_SESSION['Adjustment' . $identifier]->Quantity . " @ " . $_SESSION['Adjustment' . $identifier]->StandardCost . " " . $_SESSION['Adjustment' . $identifier]->Narrative . "',
									'" . $_SESSION['Adjustment' . $identifier]->tag . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										amount,
										narrative,
										tag)
								VALUES (17,
									'" . $AdjustmentNumber . "',
									'" . $SQLAdjustmentDate . "',
									'" . $PeriodNo . "',
									'" . $StockGLCodes['stockact'] . "',
									'" . round($_SESSION['Adjustment' . $identifier]->StandardCost * $_SESSION['Adjustment' . $identifier]->Quantity, $_SESSION['CompanyRecord']['decimalplaces']) . "',
									'" . $_SESSION['Adjustment' . $identifier]->StockID . " x " . $_SESSION['Adjustment' . $identifier]->Quantity . " @ " . $_SESSION['Adjustment' . $identifier]->StandardCost . " " . $_SESSION['Adjustment' . $identifier]->Narrative . "',
									'" . $_SESSION['Adjustment' . $identifier]->tag . "'
									)";

			$Errmsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}

		EnsureGLEntriesBalance(17, $AdjustmentNumber);

		$Result = DB_Txn_Commit();

		$ConfirmationText = _('A stock adjustment for') . ' ' . $_SESSION['Adjustment' . $identifier]->StockID . ' -  ' . $_SESSION['Adjustment' . $identifier]->ItemDescription . ' ' . _('has been created from location') . ' ' . $_SESSION['Adjustment' . $identifier]->StockLocation . ' ' . _('for a quantity of') . ' ' . locale_number_format($_SESSION['Adjustment' . $identifier]->Quantity, $_SESSION['Adjustment' . $identifier]->DecimalPlaces);
		prnMsg($ConfirmationText, 'success');

		if ($_SESSION['InventoryManagerEmail'] != '') {
			$ConfirmationText = $ConfirmationText . ' ' . _('by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
			$EmailSubject = _('Stock adjustment for') . ' ' . $_SESSION['Adjustment' . $identifier]->StockID;
			if ($_SESSION['SmtpSetting'] == 0) {
				mail($_SESSION['InventoryManagerEmail'], $EmailSubject, $ConfirmationText);
			} else {
				include('includes/htmlMimeMail.php');
				$mail = new htmlMimeMail();
				$mail->setSubject($EmailSubject);
				$mail->setText($ConfirmationText);
				$Result = SendmailBySmtp($mail, array(
					$_SESSION['InventoryManagerEmail']
				));
			}

		}
		$StockID = $_SESSION['Adjustment' . $identifier]->StockID;
		unset($_SESSION['Adjustment' . $identifier]);
	}
	/* end if there was no input error */

}
/* end if the user hit enter the adjustment */


echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" class="noPrint">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_SESSION['Adjustment' . $identifier])) {
	$Controlled = 0;
	$Quantity = 0;
	$DecimalPlaces = 2;
} else {
	$StockID = $_SESSION['Adjustment' . $identifier]->StockID;
	$Controlled = $_SESSION['Adjustment' . $identifier]->Controlled;
	$Quantity = $_SESSION['Adjustment' . $identifier]->Quantity;
	$SQL = "SELECT stockcosts.materialcost,
				stockcosts.labourcost,
				stockcosts.overheadcost,
				units,
				decimalplaces
			FROM stockmaster
			INNER JOIN stockcosts
				ON stockmaster.stockid=stockcosts.stockid
				AND stockcosts.succeeded=0
			WHERE stockcosts.stockid='" . $StockID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_SESSION['Adjustment' . $identifier]->PartUnit = $MyRow['units'];
	$_SESSION['Adjustment' . $identifier]->StandardCost = $MyRow['materialcost'] + $MyRow['labourcost'] + $MyRow['overheadcost'];
	$DecimalPlaces = $MyRow['decimalplaces'];
}
echo '<table class="selection">
		<tr>
			<th colspan="4"><h3>' . _('Adjustment Details') . '</h3></th>
		</tr>';
if (!isset($_GET['Description'])) {
	$_GET['Description'] = '';
}
echo '<tr>
		<td>' . _('Stock Code') . ':</td>
		<td>';
if (isset($StockID)) {
	echo '<input type="text" name="StockID" size="21" value="' . $StockID . '" minlength="0" maxlength="20" /></td></tr>';
} else {
	echo '<input type="text" name="StockID" size="21" value="" minlength="0" maxlength="20" /></td></tr>';
}
echo '<tr>
		<td>' . _('Partial Description') . ':</td>
		<td><input type="text" name="StockText" size="21" value="' . stripslashes($_GET['Description']) . '" />&nbsp; &nbsp;' . _('Partial Stock Code') . ':</td>
		<td>';
if (isset($StockID)) {
	echo '<input type="text" name="StockCode" size="21" value="' . $StockID . '" minlength="0" maxlength="20" />';
} else {
	echo '<input type="text" name="StockCode" size="21" value="" minlength="0" maxlength="20" />';
}
echo '</td>
		<td><input type="submit" name="CheckCode" value="' . _('Check Part') . '" /></td>
	</tr>';
if (isset($_SESSION['Adjustment' . $identifier]) and mb_strlen($_SESSION['Adjustment' . $identifier]->ItemDescription) > 1) {
	echo '<tr>
			<td colspan="3"><h3>' . $_SESSION['Adjustment' . $identifier]->ItemDescription . ' (' . _('In Units of') . ' ' . $_SESSION['Adjustment' . $identifier]->PartUnit . ' ) - ' . _('Unit Cost') . ' = ' . locale_number_format($_SESSION['Adjustment' . $identifier]->StandardCost, 4) . '</h3></td>
		</tr>';
}

echo '<tr>
		<td>' . _('Adjustment to Stock At Location') . ':</td>
		<td><select name="StockLocation" onchange="submit();"> ';
foreach ($LocationList as $Loccode => $Locationname) {
	if ($Loccode == $_SESSION['Adjustment' . $identifier]->StockLocation) {
		echo '<option selected="selected" value="' . $Loccode . '">' . $Locationname . '</option>';
	} else {
		echo '<option value="' . $Loccode . '">' . $Locationname . '</option>';
	}
}

echo '</select></td></tr>';
if (isset($_SESSION['Adjustment' . $identifier]) and !isset($_SESSION['Adjustment' . $identifier]->Narrative)) {
	$_SESSION['Adjustment' . $identifier]->Narrative = '';
	$Narrative = '';
} else {
	$Narrative = '';
}

echo '<tr>
		<td>' . _('Comments On Why') . ':</td>
		<td><input type="text" name="Narrative" size="32" minlength="0" maxlength="30" value="' . $Narrative . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Adjustment Quantity') . ':</td>';

echo '<td>';
if ($Controlled == 1) {
	if ($_SESSION['Adjustment' . $identifier]->StockLocation == '') {
		$_SESSION['Adjustment' . $identifier]->StockLocation = $_SESSION['UserStockLocation'];
	}
	echo '<input type="hidden" name="Quantity" value="' . $_SESSION['Adjustment' . $identifier]->Quantity . '" />
				' . locale_number_format($_SESSION['Adjustment' . $identifier]->Quantity, $DecimalPlaces) . ' &nbsp; &nbsp; &nbsp; &nbsp;
				[<a class="FontSize" href="' . $RootPath . '/StockAdjustmentsControlled.php?AdjType=REMOVE&identifier=' . urlencode($identifier) . '">' . _('Remove') . '</a>]
				[<a class="FontSize" href="' . $RootPath . '/StockAdjustmentsControlled.php?AdjType=ADD&identifier=' . urlencode($identifier) . '">' . _('Add') . '</a>]';
} else {
	echo '<input type="text" class="number" name="Quantity" size="12" required="required" minlength="1" maxlength="12" value="' . locale_number_format($Quantity, $DecimalPlaces) . '" />';
}
echo '</td></tr>';
//Select the tag
echo '<tr>
		<td>' . _('Select Tag') . '</td>
		<td><select minlength="0" name="tag">';

$SQL = "SELECT tagref,
				tagdescription
		FROM tags
		ORDER BY tagref";

$Result = DB_query($SQL);
echo '<option value="0">0 - ' . _('None') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['Adjustment' . $identifier]->tag) and $_SESSION['Adjustment' . $identifier]->tag == $MyRow['tagref']) {
		echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	}
}
echo '</select></td></tr>';
// End select tag

echo '</table>
	<div class="centre">
		<input type="submit" name="EnterAdjustment" value="' . _('Enter Stock Adjustment') . '" />';

if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation'] = '';
}

echo '<a href="' . $RootPath . '/StockStatus.php?StockID=' . urlencode($StockID) . '">' . _('Show Stock Status') . '</a>';
echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . urlencode($StockID) . '">' . _('Show Movements') . '</a>';
echo '<a href="' . $RootPath . '/StockUsage.php?StockID=' . urlencode($StockID) . '&amp;StockLocation=' . urlencode($_POST['StockLocation']) . '">' . _('Show Stock Usage') . '</a>';
echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . urlencode($StockID) . '&amp;StockLocation=' . urlencode($_POST['StockLocation']) . '">' . _('Search Outstanding Sales Orders') . '</a>';
echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . _('Search Completed Sales Orders') . '</a>';

echo '</div>
	  </form>';
include('includes/footer.inc');
?>