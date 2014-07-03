<?php

include('includes/session.inc');
$Title = _('Where Used Inquiry');
include('includes/header.inc');

if (isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}

echo '<div class="toplink"><a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a></div>
	<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '
	</p>';
if (isset($StockID)) {
	$result = DB_query("SELECT description,
								units,
								mbflag
						FROM stockmaster
						WHERE stockid='" . $StockID . "'");
	$MyRow = DB_fetch_row($result);
	if (DB_num_rows($result) == 0) {
		prnMsg(_('The item code entered') . ' - ' . $StockID . ' ' . _('is not set up as an item in the system') . '. ' . _('Re-enter a valid item code or select from the Select Item link above'), 'error');
		include('includes/footer.inc');
		exit;
	}
	echo '<br />
		<div class="centre"><h3>' . $StockID . ' - ' . $MyRow[0] . '  (' . _('in units of') . ' ' . $MyRow[1] . ')</h3></div>';
}

echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">
	<div class="centre">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($StockID)) {
	echo _('Enter an Item Code') . ': <input type="text" name="StockID" size="21" autofocus="autofocus" required="required" minlength="1" maxlength="20" value="' . $StockID . '" />';
} else {
	echo _('Enter an Item Code') . ': <input type="text" name="StockID" size="21" autofocus="autofocus" required="required" minlength="1" maxlength="20" />';
}

echo '<input type="submit" name="ShowWhereUsed" value="' . _('Show Where Used') . '" />';

echo '<br />
	  </div>';

if (isset($StockID)) {

	$SQL = "SELECT bom.*,
				stockmaster.description
			FROM bom INNER JOIN stockmaster
			ON bom.parent = stockmaster.stockid
			WHERE component='" . $StockID . "'
			AND bom.effectiveafter<=CURRENT_DATE
			AND bom.effectiveto >=CURRENT_DATE";

	$ErrMsg = _('The parents for the selected part could not be retrieved because');
	$result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($result) == 0) {
		prnMsg(_('The selected item') . ' ' . $StockID . ' ' . _('is not used as a component of any other parts'), 'error');
	} else {

		echo '<table width="97%" class="selection">
				<tr>
					<th>' . _('Used By') . '</th>
					<th>' . _('Work Centre') . '</th>
					<th>' . _('Location') . '</th>
					<th>' . _('Quantity Required') . '</th>
					<th>' . _('Effective After') . '</th>
					<th>' . _('Effective To') . '</th>
				</tr>';
		$k = 0;
		while ($MyRow = DB_fetch_array($result)) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			echo '<td><a target="_blank" href="' . $RootPath . '/BOMInquiry.php?StockID=' . $MyRow['parent'] . '" alt="' . _('Show Bill Of Material') . '">' . $MyRow['parent'] . ' - ' . $MyRow['description'] . '</a></td>
				<td>' . $MyRow['workcentreadded'] . '</td>
				<td>' . $MyRow['loccode'] . '</td>
				<td class="number">' . locale_number_format($MyRow['quantity'], 'Variable') . '</td>
				<td>' . ConvertSQLDate($MyRow['effectiveafter']) . '</td>
				<td>' . ConvertSQLDate($MyRow['effectiveto']) . '</td>
				</tr>';

			//end of page full new headings if
		}

		echo '</table>';
	}
} // StockID is set

echo '</form>';
include('includes/footer.inc');
?>