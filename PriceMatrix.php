<?php

//The scripts used to provide a Price break matrix for those users who like selling product in quantity break at different constant price.

include('includes/session.inc');
$Title = _('Price break matrix Maintenance');
include('includes/header.inc');

if (isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}

if (!isset($StockID)) {
	prnMsg( _('This page must be called with a stock code. Please select a stock item first'), 'warn');
	include('includes/footer.inc');
	exit;
}

$SQL = "SELECT description FROM stockmaster WHERE stockid='" . $StockID . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

echo '<div class="toplink"><a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a></div>';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . ' - ' . $MyRow['description'] . '</p><br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	if (!is_numeric(filter_number_format($_POST['QuantityBreak']))) {
		prnMsg(_('The quantity break must be entered as a positive number'), 'error');
		$InputError = 1;
	}

	if (filter_number_format($_POST['QuantityBreak']) <= 0) {
		prnMsg(_('The quantity of all items on an order in the discount category') . ' ' . $_POST['StockID'] . ' ' . _('at which the price will apply is 0 or less than 0') . '. ' . _('Positive numbers are expected for this entry'), 'warn');
		$InputError = 1;
	}
	if (!is_numeric(filter_number_format($_POST['Price']))) {
		prnMsg(_('The price must be entered as a positive number'), 'warn');
		$InputError = 1;
	}

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if ($InputError != 1) {

		$sql = "INSERT INTO pricematrix (salestype,
							stockid,
							quantitybreak,
							price)
					VALUES( '" . $_POST['SalesType'] . "',
							'" . $StockID . "',
							'" . filter_number_format($_POST['QuantityBreak']) . "',
							'" . filter_number_format($_POST['Price']) . "')";

		$result = DB_query($sql);
		prnMsg(_('The price matrix record has been added'), 'success');
		unset($_POST['QuantityBreak']);
		unset($_POST['Price']);
	}
} elseif (isset($_GET['Delete']) and $_GET['Delete'] == 'yes') {
	/*the link to delete a selected record was clicked instead of the submit button */

	$sql = "DELETE FROM pricematrix
		WHERE stockid='" . $StockID . "'
		AND salestype='" . $_GET['SalesType'] . "'
		AND quantitybreak='" . $_GET['QuantityBreak'] . "'";

	$result = DB_query($sql);
	prnMsg(_('The price matrix record has been deleted'), 'success');
	echo '<br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';

$sql = "SELECT typeabbrev,
				sales_type
			FROM salestypes";

$result = DB_query($sql);

echo '<tr>
		<td>' . _('Customer Price List') . ' (' . _('Sales Type') . '):</td>
		<td>';

echo '<select tabindex="1" name="SalesType">';

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['SalesType']) and $myrow['typeabbrev'] == $_POST['SalesType']) {
		echo '<option selected="selected" value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
	} else {
		echo '<option value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
	}
}

echo '</select>
		</td>
	</tr>';

echo '<input type="hidden" name="StockID" value="' . $StockID . '" /></td>';

echo '<tr>
		<td>' . _('Quantity Break') . '</td>
		<td><input class="integer" tabindex="3" required="required" type="number" name="QuantityBreak" size="10" maxlength="10" value="0" /></td>
	</tr>
	<tr>
		<td>' . _('Price') . ' :</td>
		<td><input class="number" tabindex="4" type="number" required="required" name="Price" title="' . _('The price to apply to orders where the quantity exceeds the specified quantity') . '" size="5" maxlength="5" value="0" /></td>
	</tr>
	</table>
	<div class="centre">
		<input tabindex="5" type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>';

$sql = "SELECT sales_type,
			salestype,
			stockid,
			quantitybreak,
			price
		FROM pricematrix
		INNER JOIN salestypes
			ON pricematrix.salestype=salestypes.typeabbrev
		ORDER BY salestype,
				stockid,
				quantitybreak";

$result = DB_query($sql);

echo '<table class="selection">
		<tr>
			<th>' . _('Sales Type') . '</th>
			<th>' . _('Price Matrix Category') . '</th>
			<th>' . _('Quantity Break') . '</th>
			<th>' . _('Sell Price') . ' %' . '</th>
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
	$DeleteURL = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=yes&amp;SalesType=' . $myrow['salestype'] . '&amp;StockID=' . $myrow['stockid'] . '&amp;QuantityBreak=' . $myrow['quantitybreak'];

	printf('<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td><a href="%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this discount matrix record?') . '\');">' . _('Delete') . '</a></td>
			</tr>', $myrow['sales_type'], $myrow['stockid'], $myrow['quantitybreak'], $myrow['price'], $DeleteURL);

}

echo '</table>
	  </form>';

include('includes/footer.inc');
?>