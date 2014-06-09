<?php

include('includes/session.inc');

$UpdateSecurity = $_SESSION['PageSecurityArray']['PurchData.php'];
$Title = _('Stock Cost Update');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<script src="javascripts/Chart.js"></script>';
if (isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}

echo '<div class="toplink"><a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a></div>';

echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />' . $Title . '
	</p>';

if (isset($_POST['UpdateData'])) {

	$sql = "SELECT stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					mbflag,
					sum(quantity) as totalqoh
			FROM stockmaster
			INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
			INNER JOIN stockcosts
				ON stockmaster.stockid=stockcosts.stockid
				AND stockcosts.succeeded=0
			WHERE stockmaster.stockid='" . $StockID . "'
			GROUP BY description,
					units,
					lastcost,
					actualcost,
					stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					mbflag";
	$ErrMsg = _('The entered item code does not exist');
	$OldResult = DB_query($sql, $ErrMsg);
	$OldRow = DB_fetch_array($OldResult);
	$_POST['QOH'] = $OldRow['totalqoh'];
	$_POST['OldMaterialCost'] = $OldRow['materialcost'];
	if ($OldRow['mbflag'] == 'M') {
		$_POST['OldLabourCost'] = $OldRow['labourcost'];
		$_POST['OldOverheadCost'] = $OldRow['overheadcost'];
	} else {
		$_POST['OldLabourCost'] = 0;
		$_POST['OldOverheadCost'] = 0;
		$_POST['LabourCost'] = 0;
		$_POST['OverheadCost'] = 0;
	}
	DB_free_result($OldResult);

	$OldCost = $_POST['OldMaterialCost'] + $_POST['OldLabourCost'] + $_POST['OldOverheadCost'];
	$NewCost = filter_number_format($_POST['MaterialCost']) + filter_number_format($_POST['LabourCost']) + filter_number_format($_POST['OverheadCost']);

	$result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $StockID . "'");
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result) == 0) {
		prnMsg(_('The entered item code does not exist'), 'error', _('Non-existent Item'));
	} elseif ($OldCost != $NewCost) {

		$Result = DB_Txn_Begin();
		ItemCostUpdateGL($StockID, $NewCost, $OldCost, $_POST['QOH']);

		$ErrMsg = _('The old cost details for the stock item could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$SQL = "UPDATE stockcosts SET succeeded=1 WHERE stockid='" . $StockID . "'";
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$SQL = "INSERT INTO stockcosts VALUES('" . $StockID . "',
										'" . filter_number_format($_POST['MaterialCost']) . "',
										'" . filter_number_format($_POST['LabourCost']) . "',
										'" . filter_number_format($_POST['OverheadCost']) . "',
										CURRENT_TIME,
										0)";
		$ErrMsg = _('The new cost details for the stock item could not be inserted because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$Result = DB_Txn_Commit();

		UpdateCost($StockID); //Update any affected BOMs

	}
}

$ErrMsg = _('The cost details for the stock item could not be retrieved because');
$DbgMsg = _('The SQL that failed was');

$SQL = "SELECT description,
				units,
				lastcost,
				actualcost,
				stockcosts.materialcost,
				stockcosts.labourcost,
				stockcosts.overheadcost,
				mbflag,
				stocktype,
				lastcostupdate,
				sum(quantity) as totalqoh
			FROM stockmaster
			INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
			INNER JOIN stockcategory
				ON stockmaster.categoryid = stockcategory.categoryid
			INNER JOIN stockcosts
				ON stockmaster.stockid = stockcosts.stockid
				AND stockcosts.succeeded=0
			WHERE stockmaster.stockid='" . $StockID . "'
			GROUP BY description,
					units,
					lastcost,
					actualcost,
					stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					mbflag,
					stocktype";
$result = DB_query($SQL, $ErrMsg, $DbgMsg);

$myrow = DB_fetch_array($result);
$ItemDescription = $myrow['description'];
echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table widthe="98%">
		<tr>
			<td style="vertical-align:top; width:50%">';

echo '<table cellpadding="2" class="selection">
		<tr>
			<th colspan="2">' . _('Item Code') . ':
				<input type="text" name="StockID" value="' . $StockID . '"  required="required" minlength="1" maxlength="20" />
				<input type="submit" name="Show" value="' . _('Show Cost Details') . '" />
			</th>
		</tr>
		<tr>
			<th colspan="2">' . $StockID . ' - ' . $myrow['description'] . '</th>
		</tr>
		<tr>
			<th colspan="2">' . _('Total Quantity On Hand') . ': ' . $myrow['totalqoh'] . ' ' . $myrow['units'] . '</th>
		</tr>
		<tr>
			<th colspan="2">' . _('Last Cost update on') . ': ' . ConvertSQLDate($myrow['lastcostupdate']) . '</th>
		</tr>
	</table>';

if (($myrow['mbflag'] == 'D' and $myrow['stocktype'] != 'L') or $myrow['mbflag'] == 'A' or $myrow['mbflag'] == 'K') {
	echo '</form>'; // Close the form
	if ($myrow['mbflag'] == 'D') {
		echo '<br />' . $StockID . ' ' . _('is a service item');
	} else if ($myrow['mbflag'] == 'A') {
		echo '<br />' . $StockID . ' ' . _('is an assembly part');
	} else if ($myrow['mbflag'] == 'K') {
		echo '<br />' . $StockID . ' ' . _('is a kit set part');
	}
	prnMsg(_('Cost information cannot be modified for kits assemblies or service items') . '. ' . _('Please select a different part'), 'warn');
	include('includes/footer.inc');
	exit;
}

$HistorySQL = "SELECT stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					stockcosts.costfrom,
					stockcosts.succeeded
				FROM stockcosts
				WHERE stockid='" . $StockID . "'
				ORDER BY costfrom DESC
				LIMIT 10";
$HistoryResult = DB_query($HistorySQL);
echo '<table cellpadding="2" class="selection">
		<tr>
			<th>' . _('Cost From') . '</th>
			<th>' . _('Material Cost') . '</th>
			<th>' . _('Labour Cost') . '</th>
			<th>' . _('Overhead Cost') . '</th>
		</tr>';
while ($HistoryRow = DB_fetch_array($HistoryResult)) {
	echo '<tr>
			<td>' . ConvertSQLDate($HistoryRow['costfrom']) . '</td>
			<td class="number">' . locale_number_format($HistoryRow['materialcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			<td class="number">' . locale_number_format($HistoryRow['labourcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			<td class="number">' . locale_number_format($HistoryRow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
		</tr>';
	$Dates[] = '"' . ConvertSQLDate($HistoryRow['costfrom']) . '"';
	$MaterialCosts[] = $HistoryRow['materialcost'];
	$LabourCosts[] = $HistoryRow['labourcost'];
	$OverheadCosts[] = $HistoryRow['overheadcost'];
	$AllCosts[] = $HistoryRow['materialcost'] + $HistoryRow['labourcost'] + $HistoryRow['overheadcost'];
}
echo '</table>';

if (!in_array($UpdateSecurity, $_SESSION['AllowedPageSecurityTokens'])) {
	echo '<table cellpadding="2" class="selection">
			<tr>
				<td>' . _('Cost') . ':</td>
				<td class="number">' . locale_number_format($myrow['materialcost'] + $myrow['labourcost'] + $myrow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>
		</table>';
} else {

	if ($myrow['mbflag'] == 'M') {
		echo '<input type="hidden" name="MaterialCost" value="' . $myrow['materialcost'] . '" />';
		echo '<table cellpadding="2" class="selection">';
		echo '<tr>
				<td>' . _('Standard Material Cost Per Unit') . ':</td>
				<td class="number">' . locale_number_format($myrow['materialcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>';
		echo '<tr>
				<td>' . _('Standard Labour Cost Per Unit') . ':</td>
				<td class="number"><input type="text" class="number" name="LabourCost" value="' . locale_number_format($myrow['labourcost'], $_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
		echo '<tr>
				<td>' . _('Standard Overhead Cost Per Unit') . ':</td>
				<td class="number"><input type="text" class="number" name="OverheadCost" value="' . locale_number_format($myrow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
	} elseif ($myrow['mbflag'] == 'B' or $myrow['mbflag'] == 'D') {
		echo '<tr>
				<td>' . _('Standard Cost') . ':</td>
				<td class="number"><input type="text" class="number" name="MaterialCost" value="' . locale_number_format($myrow['materialcost'], $_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
	} else {
		echo '<input type="hidden" name="LabourCost" value="0" />
			<input type="hidden" name="OverheadCost" value="0" />';
	}
	echo '</table>
			 <div class="centre">
				  <input type="submit" name="UpdateData" value="' . _('Update') . '" />
			 </div>';
}
echo '</td>'; //box1
echo '<td>';
echo '<canvas id="canvas" height="450" width="600"></canvas>';
echo '<script>

var lineChartData = {
labels : [' . implode(',', array_reverse($Dates)) . '],
datasets : [
{
fillColor : "rgba(128,0,128,0.5)",
strokeColor : "rgba(128,0,128,1)",
pointColor : "rgba(128,0,128,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($AllCosts)) . ']
},
{
fillColor : "rgba(30,144,255,0.5)",
strokeColor : "rgba(30,144,255,1)",
pointColor : "rgba(30,144,255,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($MaterialCosts)) . ']
},
{
fillColor : "rgba(255,255,0,0.5)",
strokeColor : "rgba(255,255,0,1)",
pointColor : "rgba(255,255,0,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($LabourCosts)) . ']
},
{
fillColor : "rgba(139,105,20,0.5)",
strokeColor : "rgba(139,105,20,1)",
pointColor : "rgba(139,105,20,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($OverheadCosts)) . ']
}
]

}

var myLine = new Chart(document.getElementById("canvas").getContext("2d")).Line(lineChartData);

</script>';
echo '</td>
		<td>
			<ul style="border-radius:4px;border:#7F7F7F solid 1px; padding:15px">
				<li style="color:#800080">Total</li>
				<li style="color:#1E90FF">Materials</li>
				<li style="color:#FFFF00">Labour</li>
				<li style="color:#8B6914">Overheads</li>
			</ul>
		</td>
	</tr>'; //Box

echo '</table>'; //Container
if ($myrow['mbflag'] != 'D') {
	echo '<div class="centre">
			<a href="' . $RootPath . '/StockStatus.php?StockID=' . urlencode($StockID) . '">' . _('Show Stock Status') . '</a>
			<a href="' . $RootPath . '/StockMovements.php?StockID=' . urlencode($StockID) . '">' . _('Show Stock Movements') . '</a>
			<a href="' . $RootPath . '/StockUsage.php?StockID=' . urlencode($StockID) . '">' . _('Show Stock Usage') . '</a>
			<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . _('Search Outstanding Sales Orders') . '</a>
			<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . _('Search Completed Sales Orders') . '</a>
		</div>';
}
echo '</form>';
include('includes/footer.inc');
?>