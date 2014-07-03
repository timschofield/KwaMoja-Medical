<?php

include('includes/session.inc');
$Title = _('Stock Re-Order Level Maintenance');
include('includes/header.inc');

if (isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

echo '<div class="toplink">
		<a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a>
	</div>';

echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $Title . '</b>
	</p>';

$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockID . "'");
$MyRow = DB_fetch_row($result);

echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if ($_SESSION['RestrictLocations'] == 0) {
	$sql = "SELECT locstock.loccode,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces
				FROM locstock
				INNER JOIN locations
					ON locstock.loccode=locations.loccode
				INNER JOIN stockmaster
					ON locstock.stockid=stockmaster.stockid
				WHERE locstock.stockid = '" . $StockID . "'
				ORDER BY locations.locationname";
} else {
	$sql = "SELECT locstock.loccode,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces
				FROM locstock
				INNER JOIN locations
					ON locstock.loccode=locations.loccode
				INNER JOIN www_users
					ON locations.loccode=www_users.defaultlocation
				INNER JOIN stockmaster
					ON locstock.stockid=stockmaster.stockid
				WHERE locstock.stockid = '" . $StockID . "'
					AND www_users.userid='" . $_SESSION['UserID'] . "'
				ORDER BY locations.locationname";
}

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');

$LocStockResult = DB_query($sql, $ErrMsg, $DbgMsg);

echo '<table class="selection">';
echo '<tr>
		<th colspan="3">' . _('Stock Code') . ':<input type="text" name="StockID" size="21" value="' . $StockID . '" required="required" minlength="1" maxlength="20" /><input type="submit" name="Show" value="' . _('Show Re-Order Levels') . '" /></th>
	</tr>';
echo '<tr>
		<th colspan="3"><h3><b>' . $StockID . ' - ' . $MyRow[0] . '</b>  (' . _('In Units of') . ' ' . $MyRow[1] . ')</h3></th>
	</tr>
<tbody>
	<tr>
		<th class="SortableColumn">' . _('Location') . '</th>
		<th>' . _('Quantity On Hand') . '</th>
		<th>' . _('Re-Order Level') . '</th>
	</tr>';

$k = 0; //row colour counter

while ($MyRow = DB_fetch_array($LocStockResult)) {

	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	if (isset($_POST['UpdateData']) AND $_POST['Old_' . $MyRow['loccode']] != filter_number_format($_POST[$MyRow['loccode']]) AND is_numeric(filter_number_format($_POST[$MyRow['loccode']])) AND filter_number_format($_POST[$MyRow['loccode']]) >= 0) {

		$MyRow['reorderlevel'] = filter_number_format($_POST[$MyRow['loccode']]);
		$sql = "UPDATE locstock SET reorderlevel = '" . filter_number_format($_POST[$MyRow['loccode']]) . "'
	   		WHERE stockid = '" . $StockID . "'
			AND loccode = '" . $MyRow['loccode'] . "'";
		$UpdateReorderLevel = DB_query($sql);

	}

	printf('<td>%s</td>
			<td class="number">%s</td>
			<td><input type="text" class="number" name="%s" required="required" minlength="1" maxlength="10" size="10" value="%s" />
			<input type="hidden" name="Old_%s" value="%s" /></td></tr>', $MyRow['locationname'], locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), $MyRow['loccode'], $MyRow['reorderlevel'], $MyRow['loccode'], $MyRow['reorderlevel']);
	//end of page full new headings if
}
//end of while loop

echo '</tbody>
	</table>
	<div class="centre">
		<input type="submit" name="UpdateData" value="' . _('Update') . '" />';

echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . urlencode($StockID) . '">' . _('Show Stock Movements') . '</a>';
echo '<a href="' . $RootPath . '/StockUsage.php?StockID=' . urlencode($StockID) . '">' . _('Show Stock Usage') . '</a>';
echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . _('Search Outstanding Sales Orders') . '</a>';
echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . _('Search Completed Sales Orders') . '</a>';

echo '</div>
	</form>';
include('includes/footer.inc');
?>