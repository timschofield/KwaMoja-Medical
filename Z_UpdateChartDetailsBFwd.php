<?php


include('includes/session.inc');
$Title = _('Recalculation of Brought Forward Balances in Chart Details Table');
include('includes/header.inc');

echo '<form method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" onSubmit="return VerifyForm(this);">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['FromPeriod']) or !isset($_POST['ToPeriod'])) {

	$sql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno ASC";
	$Periods = DB_query($sql);

	while ($myrow = DB_fetch_array($Periods)) {
		$PeriodsArray[$myrow['periodno']] = MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
	}

	$DefaultFromPeriod = min(array_keys($PeriodsArray));
	$DefaultToPeriod = max(array_keys($PeriodsArray));

	/*Show a form to allow input of criteria for TB to show */
	echo '<table>
			<tr>
				<td>' . _('Select Period From') . ':</td>
				<td><select minlength="0" name="FromPeriod">';
	foreach ($PeriodsArray as $PeriodNo => $PeriodName) {
		if ($PeriodNo == $DefaultFromPeriod) {
			echo '<option selected="selected" value="' . $PeriodNo . '">' . $PeriodName . '</option>';
		} else {
			echo '<option value="' . $PeriodNo . '">' . $PeriodName . '</option>';
		}
	}

	echo '</select>
			</td>
		</tr>';

	echo '<tr>
			<td>' . _('Select Period To') . ':</td>
			<td><select minlength="0" name="ToPeriod">';

	foreach ($PeriodsArray as $PeriodNo => $PeriodName) {
		if ($PeriodNo == $DefaultToPeriod) {
			echo '<option selected="selected" value="' . $PeriodNo . '">' . $PeriodName . '</option>';
		} else {
			echo '<option value="' . $PeriodNo . '">' . $PeriodName . '</option>';
		}
	}
	echo '</select>
				</td>
			</tr>
		</table>';

	echo '<div class="centre"><input type="submit" name="recalc" value="' . _('Do the Recalculation') . '" /></div>
		</form>';

} else {
	/*OK do the updates */

	if ($_POST['FromPeriod'] > $_POST['ToPeriod']) {
		prnMsg(_('The selected period from is actually after the period to') . '. ' . _('Please re-select the reporting period'), 'error');
		unset($_POST['FromPeriod']);
		unset($_POST['ToPeriod']);
		include('includes/footer.inc');
	}

	for ($i = $_POST['FromPeriod']; $i <= $_POST['ToPeriod']; $i++) {

		$sql = "SELECT accountcode,
					period,
					budget,
					actual,
					bfwd,
					bfwdbudget
				FROM chartdetails
				WHERE period ='" . $i . "'";

		$ErrMsg = _('Could not retrieve the ChartDetail records because');
		$result = DB_query($sql, $ErrMsg);

		while ($myrow = DB_fetch_array($result)) {

			$CFwd = $myrow['bfwd'] + $myrow['actual'];
			$CFwdBudget = $myrow['bfwdbudget'] + $myrow['budget'];

			echo '<br />' . _('Account Code') . ': ' . $myrow['accountcode'] . ' ' . _('Period') . ': ' . $myrow['period'];

			$sql = "UPDATE chartdetails SET bfwd='" . $CFwd . "',
										bfwdbudget='" . $CFwdBudget . "'
					WHERE period='" . ($myrow['period'] + 1) . "'
					AND  accountcode = '" . $myrow['accountcode'] . "'";

			$ErrMsg = _('Could not update the chartdetails record because');
			$updresult = DB_query($sql, $ErrMsg);
		}
	}
	/* end of for loop */
}

include('includes/footer.inc');
?>