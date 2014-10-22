<?php
//include_once("ConnectDB_mysql.inc");

// All functions
// *************
//function beg() /
//{
//var myConfirm = confirm("Sure you want to leave?");
//return myConfirm
//}


//
function GetYesNoStr($YesNo)
{
		If ($YesNo ==0) {
				$YesNoStr='Yes';
		} else {
				$YesNoStr='No';
		}
      return $YesNoStr;
}

function GetOpenCloseStr($OC)
{
		If ($OC ==0) {
				$OCStr='Open';
		} else {
				$OCStr='Closed';
		}
      return $OCStr;
}

function GetPayTypeDesc($PT)
{
		If ($PT==0) {
			$PTStr='Salary';
		} elseif ($PT==1) {
			$PTStr='Hourly';
		} else {
			$PTStr='Unknown';
		}
      return $PTStr;
}


function GetPayPeriodDesc($PeriodID, &$db)
{

/*Gets the GL Codes relevant to the stock item account from the stock category record */

    $QuerySQL = "SELECT payperiodid, payperioddesc FROM prlpayperiod
	             WHERE payperiodid = '$PeriodID'";
	$ErrMsg =  _('The period code could not be retreived because');
	$GetPayDescResult = DB_query($QuerySQL, $db, $ErrMsg);

	$myrow = DB_fetch_array($GetPayDescResult);
	return $myrow[1];
}

function GetOthIncRow($OIID, &$db,$PayRow){

/*Gets the GL Codes relevant to the stock item account from the stock category record */
    $sql = "SELECT othincdesc,taxable FROM prlothinctable
	             WHERE othincid = '$OIID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
            return $myrow[$PayRow];;
}


function GetPayPeriodRow($PeriodID, &$db,$PayRow){

/*Gets the GL Codes relevant to the stock item account from the stock category record */
    $sql = "SELECT payperiodid, payperioddesc,numberofpayday FROM prlpayperiod
	             WHERE payperiodid = '$PeriodID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
            return $myrow[$PayRow];;
}

function GetMyTax($MyTaxableIncome, &$db){
	if ($MyTaxableIncome>0) {
		$sql = "SELECT rangefrom,rangeto,fixtaxableamount,fixtax,percentofexcessamount,taxname
				FROM prltaxtablerate
				WHERE rangefrom<='$MyTaxableIncome'
				AND rangeto>='$MyTaxableIncome'";
				$result = DB_query($sql, $db);
				$myrow = DB_fetch_array($result);
				$MyFixTax=$myrow['fixtax'];
				$MyTaxAmt=$MyFixTax+(($MyTaxableIncome-$myrow['fixtaxableamount'])*($myrow['percentofexcessamount']/100));
	} else {
				$MyTaxAmt=0;
	}
	return $MyTaxAmt;
}

function GetMyTax2($MyEstTaxableIncome,$FSPPID,$MyTaxStatusID, &$db){
	if ($MyEstTaxableIncome>0) {
		$sql = "SELECT payperiodid,taxstatusid,rangefrom,rangeto,fixtaxableamount,fixtax,percentofexcessamount
				FROM prltaxtablerate2
				WHERE rangefrom<='$MyEstTaxableIncome'
				AND rangeto>='$MyEstTaxableIncome'
				AND payperiodid='$FSPPID'
				AND taxstatusid='$MyTaxStatusID'";
				$result = DB_query($sql, $db);
				$myrow = DB_fetch_array($result);
				$MyFixTax=$myrow['fixtax'];
				$MyTaxAmt=$MyFixTax+(($MyEstTaxableIncome-$myrow['fixtaxableamount'])*($myrow['percentofexcessamount']/100));
	} else {
				$MyTaxAmt=0;
	}
	return $MyTaxAmt;
}

function GetHDMFEE($GrossIncome, &$db){
	$sql = "SELECT rangefrom,rangeto,dedtypeee,employeeshare
			FROM prlgrosspaytable
			WHERE rangefrom<='$GrossIncome'
			AND rangeto>='$GrossIncome'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			if ($myrow['dedtypeee']=='Fixed') {
				$MyHDMFAmt= $myrow['employeeshare'];
			} elseif ($myrow['dedtypeee']=='Percentage') {
				$MyHDMFAmt=$GrossIncome * ($myrow['employeeshare']/100);
			} else {
				$MyHDMFAmt= 0;
			}
		    return $MyHDMFAmt;
}

function GetHDMFER($GrossIncome, &$db){
	$sql = "SELECT rangefrom,rangeto,dedtypeer,employershare
			FROM prlgrosspaytable
			WHERE rangefrom<='$GrossIncome'
			AND rangeto>='$GrossIncome'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			if ($myrow['dedtypeer']=='Fixed') {
			//	$MyHDMFAmt= $myrow['employeeshare'];
			} elseif ($myrow['dedtypeer']=='Percentage') {
				$MyHDMFAmt=$GrossIncome * ($myrow['employershare']/100);
			} else {
				$MyHDMFAmt= 0;
			}
		    return $GrossIncome;
}



function GetTaxStatusRow($TaxID, &$db,$PayRow){
		$sql = "SELECT taxstatusid,taxstatusdescription,personalexemption,additionalexemption,totalexemption
			FROM prltaxstatus
			WHERE taxstatusid='$TaxID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
            return $myrow[$PayRow];
}


function GetPayrollRow($PayrollID, &$db,$PayRow){
//payrollid - 0, and so on
/*Gets the GL Codes relevant to the stock item account from the stock category record */
		//$sql = "SELECT payrollidyrolldesc,payperiodid,startdate,enddate,fsmonth,fsyear,payclosed
		$sql = "SELECT payrollid,payrolldesc,payperiodid,startdate,enddate,fsmonth,fsyear,payclosed
			FROM prlpayrollperiod
			WHERE payrollid = '$PayrollID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			if ($PayRow==11) {
			return $myrow['payclosed'];
			}else {
            return $myrow[$PayRow];
			}
}


function GetEmpRow($EmpID, $db,$EmpRow){
		$sql = "SELECT paytype,payperiodid,periodrate,hourlyrate,marital,taxstatusid,employmentid,active,socialsecurity_company,grosspay,basicpay,tax_identificationno
			FROM prlemployeemaster
			WHERE employeeid= '" . $EmpID . "'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			if ($EmpRow==35) return $myrow['taxstatusid'];
			if ($EmpRow==29) return $myrow['paytype'];
			if ($EmpRow==19) return $myrow['atmnumber'];
			if ($EmpRow==20) return $myrow['socialsecurity_company'];
			if ($EmpRow==21) return $myrow['grosspay'];
			if ($EmpRow==22) return $myrow['basicpay'];
			if ($EmpRow==23) return $myrow['tax_identificationno'];
            return $myrow[$PayRow];
}

function GetName($EmpID, &$db){
		$sql = "SELECT lastname,firstname,middlename
			FROM prlemployeemaster
			WHERE employeeid= '$EmpID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
            return $myrow['lastname'].', '.$myrow['firstname'].', '.$myrow['middlename'];
}


function GetSSSRow($SSSGross, &$db){
		$sql = "SELECT rangefrom,rangeto,salarycredit,employerss,employerec,employeess,total
			FROM prlnssftable
			WHERE rangefrom<='$SSSGross'
			AND rangeto>='$SSSGross'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
		    return $myrow;
}

function GetPHRow($PHGross, &$db){
		$sql = "SELECT rangefrom,rangeto,employerbasicpay,employerec,employeebasicpay,total
			FROM prlbasicpaytable
			WHERE rangefrom<='$PHGross'
			AND rangeto>='$PHGross'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
		    return $myrow;
}




function GetMonthStr($Mos)
{
		If ($Mos ==1) {
				$MosStr='January';
		} elseif ($Mos ==2){
				$MosStr='February';
		} elseif ($Mos ==3){
				$MosStr='March';
		} elseif ($Mos ==4){
				$MosStr='April';
		} elseif ($Mos ==5){
				$MosStr='May';
		} elseif ($Mos ==6){
				$MosStr='June';
		} elseif ($Mos ==7){
				$MosStr='July';
		} elseif ($Mos ==8){
				$MosStr='August';
		} elseif ($Mos ==9){
				$MosStr='September';
		} elseif ($Mos ==10){
				$MosStr='October';
		} elseif ($Mos ==11){
				$MosStr='November';
		} elseif ($Mos ==12){
				$MosStr='December';
		} else {
				$MosStr='Month';
		}
      return $MosStr;
}

//unused
function monthoption($Mos)
{
   $MosStr= GetMonthStr($Mos);
   echo '<OPTION SELECTED VALUE=$Mos>'. _($MosStr);
   echo '<OPTION VALUE=1>' . _('January');
   echo '<OPTION VALUE=2>' . _('February');
   echo '<OPTION VALUE=3>' . _('March');
   echo '<OPTION VALUE=4>' . _('April');
   echo '<OPTION VALUE=5>' . _('May');
   echo '<OPTION VALUE=6>' . _('June');
   echo '<OPTION VALUE=7>' . _('July');
   echo '<OPTION VALUE=8>' . _('August');
   echo '<OPTION VALUE=9>' . _('September');
   echo '<OPTION VALUE=10>' . _('October');
   echo '<OPTION VALUE=11>' . _('November');
   echo '<OPTION VALUE=12>' . _('December');
   return 1;
}

//unsed
function yearoption($FSYear)
{
	if (($FSYear==0) or ($FSYear==null)) {
	    echo '<OPTION SELECTED VALUE=0>'. _('Year');
	} else {
	    echo '<OPTION SELECTED VALUE=$FSYear>'. _($FSYear);
	}
	for ($yy=2006;$yy<=2015;$yy++)
                    {
                        echo "<option value=$yy>$yy</option>\n";

                    }

  return 1;
}


//unused
function makedropdown($optionid,$optionname,$table)
{
	   // Query to choose all departments
	   $querydrop = "select $optionid,$optionname from $table order by $optionname";
       $resultdrop= MYSQL_QUERY($querydrop);
       $numberdrop = MYSQL_NUMROWS($resultdrop);

           if ($numberdrop==0)
           {

               echo "<option value=\"\" selected>No Data</option>";

           }
           else if ($numberdrop>0)
           {

              $i=0;

                echo "<option value=\"\">Please Choose</option>";

                while ($i<$numberdrop)
                {

                       $opid = mysql_result($resultdrop,$i,"$optionid");
           	          $opname = mysql_result($resultdrop,$i,"$optionname");

                          echo "<option value=\"$opid\">$opname</option>\n";

                          $i++;

                }

           }

           return 0;
}

?>