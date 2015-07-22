<?php

include('includes/session.inc');

$Title = _('Payroll Records Maintenance');

include('includes/header.inc');
include('includes/prlFunctions.php');

if (isset($_GET['PayrollID'])){
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])){
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}


if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
		// Checking if Employee ID is set
       if ($PayrollID=="")
       {
           $InputError=1;
  	       prnMsg(_('Payroll ID must not be empty.') ,'error');
       }
	   if ($_POST['PayPeriodID']=="")
       {
           $InputError=1;
		   prnMsg(_('PayPeriod ID must not be empty.') ,'error');
       }
	if (!is_date($_POST['StartDate'])) {
		$InputError = 1;
		prnMsg(_('The field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (!is_date($_POST['EndDate'])) {
		$InputError = 1;
		prnMsg(_('The field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if ($_POST['submit']!=_('Update Payroll Period')) {
       if (isset($_POST['FSMonth']) and ($_POST['FSMonth']) =="")
       {
             echo "<ul><li>FS Month is a Mandatory Field.</li></ul>";
             $InputError=1;
       }
	}

	if ($InputError != 1){

	    $SQL_StartDate = FormatDateForSQL($_POST['StartDate']);
		$SQL_EndDate = FormatDateForSQL($_POST['EndDate']);

		if (!isset($_POST["New"])) {
			$sql = "UPDATE prlpayrollperiod SET
					payrolldesc='" . DB_escape_string($_POST['Description']) ."',
					payperiodid='" . DB_escape_string($_POST['PayPeriodID']) ."',
					startdate='" . $SQL_StartDate . "',
					enddate='" . $SQL_EndDate . "',
					deductsss='" . DB_escape_string($_POST['nssf_number']) . "',
					deductbasicpay='" . DB_escape_string($_POST['basicpay']) . "',
					deductgrosspay='" . DB_escape_string($_POST['grosspay']) . "'
					 WHERE payrollid = '$PayrollID'";
					$ErrMsg = _('The payroll record could not be updated because');
					$DbgMsg = _('The SQL that was used to update the payroll failed was');
					$result = DB_query($sql, $ErrMsg, $DbgMsg);
					prnMsg(_('The payroll master record for') . ' ' . $PayrollID . ' ' . _('has been updated'),'success');

		} else { //its a new payroll
				$sql = "INSERT INTO prlpayrollperiod (
					payrollid,
					payrolldesc,
					payperiodid,
					startdate,
					enddate,
					fsmonth,
					fsyear,
					deductsss,
					deductbasicpay,
					deductgrosspay,
					payclosed)
				VALUES ('$PayrollID',
					'" . DB_escape_string($_POST['Description']) ."',
					'" . DB_escape_string($_POST['PayPeriodID']) ."',
					'" . $SQL_StartDate . "',
					'" . $SQL_EndDate . "',
					'" .isset( $_POST['fsMonth']) . "',
					'" .isset ($_POST['fsYear']) . "',
					'" . DB_escape_string($_POST['nssf_number']) . "',
					'" . DB_escape_string($_POST['basicpay']) . "',
					'" . DB_escape_string($_POST['grosspay']) . "',
					'0'
					)";
			$ErrMsg = _('The payroll period') . ' ' . $_POST['Description'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the payroll period but failed was');
			$result = DB_query($sql, $ErrMsg, $DbgMsg);

			prnMsg(_('A new payroll period for') . ' ' . $_POST['Description'] . ' ' . _('has been added to the database'),'success');

			unset ($PayrollID);
			unset($_POST['Description']);
			unset($_POST['PayPeriodID']);
			unset($SQL_StartDate);
			unset($SQL_EndDate);
			unset($_POST['fsMonth']);
			unset($_POST['fsYear']);
			unset($_POST['SSS']);
			unset($_POST['HDMF']);
			unset($_POST['grosspay']);
		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'),'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;
		$sql = "SELECT counterindex,payrollid,employeeid,basicpay,absent,late,otpay,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($sql);
		if(DB_num_rows($PayDetails)>0)
		{
		  $CancelDelete = 1;
		  exit("Payroll can not be deleted. Payroll records found...");
		}

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts

	if ($CancelDelete == 0) {
		$sql="DELETE FROM prlpayrollperiod WHERE payrollid='$PayrollID'";
		$result = DB_query($sql);
		prnMsg(_('Payroll record for') . ' ' . $Description . ' ' . _('has been deleted'),'success');
		unset($PayrollID);
		unset($_SESSION['PayrollID']);
	} //end if Delete payroll
} //end of (isset($_POST['submit']))

if (!isset($PayrollID)) {
/*If the page was called without $PayrollID passed to page then assume a new payroll is to be entered*/
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<input type="hidden" name="New" value="Yes">';
	echo '<table>';
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('Payroll ID') . ":</td><td><input type='text' name='PayrollID'  SIZE=11 MAXLENGTH=10></td>";
	     //'<td><align=right><b>Accept Alpha Numeric Character</b></td>'</tr>";
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('Description') . ":</td><td><input type='Text' name='Description' SIZE=42 MAXLENGTH=40></td></tr>";
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('Pay Period') . ":</td><td><select name='PayPeriodID'>";
	DB_data_seek($result, 0);
	$sql = 'SELECT payperiodid, payperioddesc FROM prlpayperiod';
	$result = DB_query($sql);
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['PayPeriodID']) and $_POST['PayPeriodID'] == $myrow['payperiodid']){
			echo '<option selected="selected" value=' . $myrow['payperiodid'] . '>' . $myrow['payperioddesc'];
		} else {
			echo '<option value=' . $myrow['payperiodid'] . '>' . $myrow['payperioddesc'];
		}
	} //end while loop
	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('Start Date') . ' (' . $_SESSION['DefaultDateFormat'] . ":</td><td><input type='Text' name='StartDate' value=$DateString SIZE=12 MAXLENGTH=10></td></tr>";
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('End Date') . ' (' . $_SESSION['DefaultDateFormat'] . ":</td><td><input type='Text' name='EndDate' value=$DateString SIZE=12 MAXLENGTH=10></td></tr>";
?>
	       <tr>
	          <td width=200 height="20">
              <div align="right"><b>FS Month :</b></div>
              </td>
              <td height="20">
                <select name="FSMonth">
   	            <option value="" selected="selected">Month</option>
                <option value=01>January</option>
                <option value=02>February</option>
                <option value=03>March</option>
                <option value=04>April</option>
                <option value=05>May</option>
                <option value=06>June</option>
                <option value=07>July</option>
                <option value=08>August</option>
                <option value=09>September</option>
                <option value=10>October</option>
                <option value=11>November</option>
                <option value=12>December</option>
              </select>
              <select name="FSYear">
              <option value="" Selected>Year</option>
              <?php

                    for ($yy=2006;$yy<=2015;$yy++)
                    {
                        echo "<option value=$yy>$yy</option>\n";

                    }
              ?>
              </select>
              </td>
          </tr>
	<?php
    echo '</select></td></tr><tr><td width=200 height=20><div align="right"><b>' . _('Deduct NSSF') . ":</td><td><select name='nssf_number'>";
	echo '<option value=0>' . _('Yes');
	echo '<option value=1>' . _('No');
	echo '</select></td></tr>';
    echo '</select></td></tr><tr><td width=200 height=20><div align="right"><b>' . _('Deduct Gross Pay') . ":</td><td><select name='grosspay'>";
	echo '<option value=0>' . _('Yes');
	echo '<option value=1>' . _('No');
    echo '</select></td></tr><tr><td width=200 height=20><div align="right"><b>' . _('Deduct Basic Pay') . ":</td><td><select name='basicpay'>";
	echo '<option value=0>' . _('Yes');
	echo '<option value=1>' . _('No');
	echo '</select></td></tr>';
	echo "</select></td></tr></table><p><input type='Submit' name='submit' value='" . _('Insert New Payroll') . "'>";
	echo '</form>';

} else {
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';
		if (!isset($_POST["New"])) {
				$sql = "SELECT payrollid,
					payrolldesc,
					payperiodid,
					startdate,
					enddate,
					deductnssf,
					deductbasicpay,
					deductgrosspay,
					payclosed
			FROM prlpayrollperiod
			WHERE payrollid = '$PayrollID'";
			$result = DB_query($sql);
			$myrow = DB_fetch_array($result);
		$_POST['Description'] = $myrow['payrolldesc'];
		$_POST['PayPeriodID'] = $myrow['payperiodid'];
		$_POST['StartDate']  = ConvertSQLDate($myrow['startdate']);
		$_POST['EndDate']  = ConvertSQLDate($myrow['enddate']);
		$_POST['SSS']  = $myrow['deductsss'];
		$_POST['HDMF']  = $myrow['deducthdmf'];
		$_POST['grosspay']  = $myrow['deductgrosspay'];
		$_POST['Status']  = $myrow['payclosed'];
		echo '<input type="hidden" name="PayrollID" value="' . $PayrollID . '">';
		} else {
		// its a new employee  being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td><div align="right"><b>' . _('Payroll ID') . ":</td><td><input type='text' name='PayrollID' value='$PayrollID' SIZE=12 MAXLENGTH=10></td></tr>";
		}
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('Description') . ":</td><td><input type='Text' name='Description' value='" . $_POST['Description'] . "' SIZE=42 MAXLENGTH=40></td></tr>";
	echo '</select></td></tr>';
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('Pay Period') . ":</td><td><select name='PayPeriodID'>";
	DB_data_seek($result, 0);
	$sql = 'SELECT payperiodid, payperioddesc FROM prlpayperiod';
	$result = DB_query($sql);
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['payperiodid'] == $_POST['PayPeriodID']){
			echo '<option selected="selected" value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['payperiodid'] . '>' . $myrow['payperioddesc'];
	} //end while loop
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('Start Date') . ":</td><td><input type='Text' name='StartDate' value='" . $_POST['StartDate'] . "' SIZE=22 MAXLENGTH=20></td></tr>";
	echo '<tr><td width=200 height=20><div align="right"><b>' . _('End Date') . ":</td><td><input type='Text' name='EndDate' value='" . $_POST['EndDate'] . "' SIZE=22 MAXLENGTH=20></td></tr>";
	echo '</select></td></tr><tr><td width=200 height=20><div align="right"><b>' . _('Deduct NSSF') . ":</td><td><select name='SSS'>";
	if ($_POST['SSS'] == 0){
		echo '<option selected="selected" value=0>' . _('Yes');
		echo '<option value=1>' . _('No');
	} else {
		echo '<option value=0>' . _('Yes');
		echo '<option selected="selected" value=1>' . _('No');
	}

    echo '</select></td></tr><tr><td width=200 height=20><div align="right"><b>' . _('Deduct HDMF') . ":</td><td><select name='HDMF'>";
	if ($_POST['HDMF'] == 0){
		echo '<option selected="selected" value=0>' . _('Yes');
		echo '<option value=1>' . _('No');
	} else {
		echo '<option value=0>' . _('Yes');
		echo '<option selected="selected" value=1>' . _('No');
	}
    echo '</select></td></tr><tr><td width=200 height=20><div align="right"><b>' . _('Deduct PhilHealt') . ":</td><td><select name='grosspay'>";
	if ($_POST['grosspay'] == 0){
		echo '<option selected="selected" value=0>' . _('Yes');
		echo '<option value=1>' . _('No');
	} else {
		echo '<option value=0>' . _('Yes');
		echo '<option selected="selected" value=1>' . _('No');
	}
	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Employee Details') . "'></form>";
	} else {
		//echo "</select></td></tr></table><p><input type='Submit' name='submit' value='" . _('Update Payroll Period') . "'>";
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update Payroll Period') . "'>";
		echo '<p><font color=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete Payroll') . '" onclick="return confirm("' . _('Are you sure you wish to delete this payroll period?') . '");"></form>';
	}

}

include('includes/footer.inc');
?>