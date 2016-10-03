<?php

include('includes/session.php');
$Title = _('Internal Stock Request Inquiry');
include('includes/header.php');

echo '<p class="page_title_text"><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" title="', $Title, '" alt="" />', $Title, '</p>';

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}

if (isset($_POST['RequestNo'])) {
	$RequestNo = $_POST['RequestNo'];
}
if (isset($_POST['SearchPart'])) {
	$StockItemsResult = GetSearchItems();
}
if (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}
if (isset($_POST['SelectedStockItem'])) {
	$StockID = $_POST['SelectedStockItem'];
}

if (!isset($_POST['SearchPart'])) { //The scripts is just opened or click a submit button
	$SQL = "SELECT locations.loccode,
						locationname,
						canview
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview=1
						AND locations.internalrequest=1";
	$LocationResult = DB_query($SQL);
	$LocationTotal = DB_num_rows($LocationResult);
	echo '<table class="selection">
				<tr>
					<td>', _('Request Number'), ':</td>
					<td>
						<input type="text" name="OrderNumber" maxlength="8" size="9" />
					</td>
					<td>', _('From Stock Location'), ':</td>
					<td><select name="StockLocation">';
	if ($LocationTotal > 0) {
		echo '<option value="All" selected="selected">', _('All'), '</option>';
		while ($MyRow = DB_fetch_array($LocationResult)) {
			if (isset($_POST['StockLocation']) and ($MyRow['loccode'] == $_POST['StockLocation'])) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		}
		echo '<select>
			</td>';
	} else {
		prnMsg(_('There are no locations which you have authority to inquire on'), 'info');
		include('includes/footer.php');
		exit;
	}

	if (!isset($_POST['AuthorisedStatus'])) {
		$_POST['AuthorisedStatus'] = 'All';
	}
	$AuthorisationStatus = array(
		'All' => _('All'),
		0 => _('Unauthorised'),
		1 => _('Authorised')
	);
	echo '<td>', _('Authorisation status'), '</td>
			<td><select name="AuthorisedStatus">';
	foreach ($AuthorisationStatus as $Code => $Description) {
		if ($_POST['AuthorisedStatus'] === $Code) {
			echo '<option selected="selected" value="', $Code, '">', $Description, '</option>';
		} else {
			echo '<option value="', $Code, '">', $Description, '</option>';
		}
	}
	echo '</select>
			</td>
		</tr>';

	//add the department, sometime we need to check each departments' internal request
	if (!isset($_POST['Department'])) {
		$_POST['Department'] = 'All';
	}
	echo '<td>', _('Department'), '</td>
		<td><select name="Department">';
	//now lets retrieve those deparment available for this user;
	$SQL = "SELECT departments.departmentid,
					departments.description
				FROM departments
				LEFT JOIN stockrequest
					ON departments.departmentid = stockrequest.departmentid
					AND (departments.authoriser = '" . $_SESSION['UserID'] . "' OR stockrequest.userid = '" . $_SESSION['UserID'] . "')
				WHERE stockrequest.dispatchid IS NOT NULL
				GROUP BY stockrequest.departmentid"; //if a full request is need, the users must have all of those departments' authority
	$DepartmentResult = DB_query($SQL);
	if (DB_num_rows($DepartmentResult) > 0) {
		if (isset($_POST['Department']) and $_POST['Department'] == 'All') {
			echo '<option selected="selected" value="All">', _('All'), '</option>';
		} else {
			echo '<option value="All">', _('All'), '</option>';
		}
		while ($MyRow = DB_fetch_array($DepartmentResult)) {
			if (isset($_POST['Department']) and ($_POST['Department'] == $MyRow['departmentid'])) {
				echo '<option selected="selected" value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
			} else {
				echo '<option value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
			}
		}
		echo '</select>
				</td>';
	} else {
		prnMsg(_('There are no internal request result available for you or your department'), 'warn');
		include('includes/footer.php');
		exit;
	}

	//now lets add the time period option
	if (!isset($_POST['FromDate'])) {
		$_POST['FromDate'] = DateAdd(date($_SESSION['DefaultDateFormat']), 'm', -1);
	}
	if (!isset($_POST['ToDate'])) {
		$_POST['ToDate'] = date($_SESSION['DefaultDateFormat']);
	}
	echo '<td>', _('Date From'), '</td>
		<td><input type="text" class="date" alt="', $_SESSION['DefaultDateFormat'], '" name="FromDate" maxlength="10" size="11" value="', $_POST['FromDate'], '" /></td>
		<td>', _('Date To'), '</td>
		<td><input type="text" class="date" alt="', $_SESSION['DefaultDateFormat'], '" name="ToDate" maxlength="10" size="11" value="', $_POST['ToDate'], '" /></td>
		<td><input type="submit" name="Search"  value="', _('Search'), '" /></td>
	</tr>';
	if (!isset($_POST['ShowDetails'])) {
		$_POST['ShowDetails'] = 1;
	}
	if ($_POST['ShowDetails'] === 'on') {
		$Checked = 'checked="checked"';
	} else {
		$Checked = '';
	}
	echo '<tr>
			<td colspan="6" class="centre">', _('Show Details'), '
				<input type="checkbox" ', $Checked, ' name="ShowDetails" />
			</td>
		</tr>
	</table>';

	//following is the item search parts which belong to the existed internal request, we should not search it generally, it'll be rediculous
	//hereby if the authorizer is login, we only show all category available, even if there is problem, it'll be correceted later when items selected -:)
	if (isset($Authorizer)) {
		$WhereAuthorizer = '';
	} else {
		$WhereAuthorizer = " AND internalstockcatrole.secroleid = '" . $_SESSION['AccessLevel'] . "' ";
	}

	$SQL = "SELECT stockcategory.categoryid,
				stockcategory.categorydescription
			FROM stockcategory
			INNER JOIN internalstockcatrole
				ON stockcategory.categoryid = internalstockcatrole.categoryid
			WHERE internalstockcatrole.secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY stockcategory.categorydescription";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {

		echo '<table class="selection">
				<tr>
					<th colspan="6"><h3>', _('To search for internal request for a specific part use the part selection facilities below'), '</h3></th>
				</tr>';

		echo '<tr>
				<td>', _('Stock Category'), '</td>
				<td><select name="StockCat">';
		if (!isset($_POST['StockCat'])) {
			$_POST['StockCat'] = 'All';
		}
		if ($_POST['StockCat'] == 'All') {
			echo '<option selected="selected" value="All">', _('All'), '</option>';
		} else {
			echo '<option value="All">', _('All'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['categoryid'] == $_POST['StockCat']) {
				echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
			} else {
				echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
			}
		}
		echo '</select>
				</td>';

		echo '<td>', _('Enter partial'), '  <b>', _('Description'), '</b>:</td>';
		if (!isset($_POST['Keywords'])) {
			$_POST['Keywords'] = '';
		}
		echo '<td>
				<input type="text" name="Keywords" value="', $_POST['Keywords'], '" size="20" maxlength="25" />
				</td>
			</tr>';

		if (!isset($_POST['StockCode'])) {
			$_POST['StockCode'] = '';
		}
		echo '<tr>
				<td></td>
				<td></td>
				<td>', _('OR'), ' ', _('Enter partial'), ' <b>', _('Stock Code'), '</b>:</td>
				<td>
					<input type="text" autofocus="autofocus" name="StockCode" value="', $_POST['StockCode'], '" size="15" maxlength="18" />
				</td>
			</tr>
		</table>';

	} else {
		echo '<p class="bad">', _('Problem Report'), ':<br />', _('There are no stock categories currently defined please use the link below to set them up'), '</p>';
		echo '<a href="', $RootPath, '/StockCategories.php">', _('Define Stock Categories'), '</a>';
		include('includes/footer.php');
		exit;
	}
	echo '<div class="centre">
			<input type="submit" name="SearchPart" value="', _('Search Now'), '" />
			<input type="submit" name="ResetPart" value="', _('Show All'), '" />
		</div>
	</form>';
}

if (isset($StockItemsResult)) {

	if (DB_num_rows($StockItemsResult) > 1) {
		echo '<a href="', $RootPath, '/InternalStockRequestInquiry.php">', _('Return to Main Inquiry Screen'), '</a>';
		echo '<table cellpadding="2" class="selection">';
		echo '<thead>
				<tr>
					<th class="SortedColumn" >', _('Code'), '</th>
					<th class="SortedColumn" >', _('Description'), '</th>
					<th class="SortedColumn" >', _('Total Applied'), '</th>
					<th>', _('Units'), '</th>
				</tr>
			</thead>';

		$k = 0; //row colour counter
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($StockItemsResult)) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			echo '<td><input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '" /></td>
				<td>', $MyRow['description'], '</td>
				<td class="number">', locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['units'], '</td>
			</tr>';
			//end of page full new headings if
		}
		//end of while loop

		echo '</tbody>
		</table>';

	}

} elseif (isset($_POST['Search']) or isset($StockID)) { //lets show the search result here

	if ($_POST['ShowDetails'] === 'on' or isset($StockID)) {
		$SQL = "SELECT stockrequest.dispatchid,
						stockrequest.loccode,
						stockrequest.departmentid,
						departments.description,
						locations.locationname,
						despatchdate,
						authorised,
						closed,
						narrative,
						userid,
						stockrequestitems.stockid,
						stockmaster.description as stkdescription,
						quantity,
						stockrequestitems.decimalplaces,
						uom,
						completed
					FROM stockrequest
					INNER JOIN stockrequestitems
						ON stockrequest.dispatchid=stockrequestitems.dispatchid
					INNER JOIN departments
						ON stockrequest.departmentid=departments.departmentid
					INNER JOIN locations
						ON locations.loccode=stockrequest.loccode
					INNER JOIN stockmaster
						ON stockrequestitems.stockid=stockmaster.stockid";
	} else {
		$SQL = "SELECT stockrequest.dispatchid,
						stockrequest.loccode,
						stockrequest.departmentid,
						departments.description,
						locations.locationname,
						despatchdate,
						authorised,
						closed,
						narrative,
						userid
					FROM stockrequest
					INNER JOIN departments
						ON stockrequest.departmentid=departments.departmentid
					INNER JOIN locations
						ON locations.loccode=stockrequest.loccode";
	}
	//lets add the condition selected by users
	if (isset($_POST['RequestNo'])) {
		$SQL .= " WHERE stockrequest.dispatchid = '" . $_POST['RequsetNo'] . "'";
	} else {
		//first the constraint of locations;
		if (isset($_POST['StockLocation']) and $_POST['StockLocation'] != 'All') { //retrieve the location data from current code
			$SQL .= " WHERE stockrequest.loccode='" . $_POST['StockLocation'] . "'";
		} else { //retrieve the location data from serialzed data
			$SQL .= " WHERE stockrequest.loccode " . LIKE . " '%%'";
		}
		//the authorization status
		if ($_POST['AuthorisedStatus'] != 'All') { //no bothering for all
			$SQL .= " AND authorised = '" . $_POST['AuthorisedStatus'] . "'";
		}
		//the department: if the department is all, no bothering for this since user has no relation ship with department; but consider the efficency, we should use the departments to filter those no needed out
		if ($_POST['Department'] == 'All') {
			$SQL .= " AND stockrequest.departmentid " . LIKE . " '%%'";
		} else {
			$SQL .= " AND stockrequest.departmentid='" . $_POST['Department'] . "'";
		}
		//Date from
		if (isset($_POST['FromDate'])) {
			$SQL .= " AND despatchdate>='" . FormatDateForSQL($_POST['FromDate']) . "'";
		}
		if (isset($_POST['ToDate'])) {
			$SQL .= " AND despatchdate<='" . FormatDateForSQL($_POST['ToDate']) . "'";
		}
		//item selected
		if (isset($StockID)) {
			$SQL .= " AND stockrequestitems.stockid='" . $StockID . "'";
		}
	} //end of no request no selected
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		if ($_POST['ShowDetails'] === 'on' or isset($StockID)) {
			echo '<table>
					<tr>
						<th>', _('ID'), '</th>
						<th>', _('Locations'), '</th>
						<th>', _('Department'), '</th>
						<th>', _('Authorised'), '</th>
						<th>', _('Dispatch Date'), '</th>
						<th>', _('Stock ID'), '</th>
						<th>', _('Description'), '</th>
						<th>', _('Quantity'), '</th>
						<th>', _('Units'), '</th>
						<th>', _('Completed'), '</th>
					</tr>';
		} else {
			echo '<table>
					<tr>
						<th>', _('ID'), '</th>
						<th>', _('Locations'), '</th>
						<th>', _('Department'), '</th>
						<th>', _('Authorised'), '</th>
						<th>', _('Dispatch Date'), '</th>
					</tr>';
		}

		if ($_POST['ShowDetails'] === 'on' or isset($StockID)) {
			$ID = ''; //mark the ID change of the internal request
		}
		$i = 0;
		//There are items without details AND with it
		while ($MyRow = DB_fetch_array($Result)) {
			if ($i == 0) {
				echo '<tr class="EvenTableRows">';
				$i = 1;
			} elseif ($i == 1) {
				echo '<tr class="OddTableRows">';
				$i = 0;
			}
			if ($MyRow['authorised'] == 0) {
				$Authorised = _('No');
			} else {
				$Authorised = _('Yes');
			}
			if ($MyRow['despatchdate'] == '0000-00-00') {
				$DespatchDate = _('Not yet');
			} else {
				$DespatchDate = ConvertSQLDate($MyRow['despatchdate']);
			}
			if (isset($ID)) {
				if (isset($MyRow['completed']) and $MyRow['completed'] == 0) {
					$Completed = _('No');
				} else {
					$Completed = _('Yes');
				}
			}
			if (isset($ID) and ($ID != $MyRow['dispatchid'])) {
				$ID = $MyRow['dispatchid'];
				echo '<td>', $MyRow['dispatchid'], '</td>
						<td>', $MyRow['locationname'], '</td>
						<td>', $MyRow['description'], '</td>
						<td>', $Authorised, '</td>
						<td>', $DespatchDate, '</td>
						<td>', $MyRow['stockid'], '</td>
						<td>', $MyRow['stkdescription'], '</td>
						<td>', locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), '</td>
						<td>', $MyRow['uom'], '</td>
						<td>', $Completed, '</td>';

			} elseif (isset($ID) and ($ID == $MyRow['dispatchid'])) {
				echo '<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>', $MyRow['stockid'], '</td>
						<td>', $MyRow['stkdescription'], '</td>
						<td>', locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), '</td>
						<td>', $MyRow['uom'], '</td>
						<td>', $Completed, '</td>';
			} elseif (!isset($ID)) {
				echo '<td>', $MyRow['dispatchid'], '</td>
						<td>', $MyRow['locationname'], '</td>
						<td>', $MyRow['description'], '</td>
						<td>', $Authorised, '</td>
						<td>', $DespatchDate, '</td>';
			}
			echo '</tr>';
		} //end of while loop;
		echo '</table>';
	} else {
		prnMsg(_('There are no stock request available'), 'warn');
	}

}

include('includes/footer.php');

function GetSearchItems($SQLConstraint = '') {
	if ($_POST['Keywords'] and $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	$SQL = "SELECT stockmaster.stockid,
				   stockmaster.description,
				   stockmaster.decimalplaces,
				   SUM(stockrequestitems.quantity) AS qoh,
				   stockmaster.units
			FROM stockrequestitems
			INNER JOIN stockrequest
				ON stockrequestitems.dispatchid=stockrequest.dispatchid
			INNER JOIN departments
				ON stockrequest.departmentid = departments.departmentid
			INNER JOIN stockmaster
				ON stockrequestitems.stockid = stockmaster.stockid";
	if (isset($_POST['StockCat']) and ((trim($_POST['StockCat']) == '') or $_POST['StockCat'] == 'All')) {
		$WhereStockCat = '';
	} else {
		$WhereStockCat = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL .= " WHERE stockmaster.description " . LIKE . " '" . $SearchString . "'
			  " . $WhereStockCat;


	} elseif (isset($_POST['StockCode'])) {
		$SQL .= " WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'" . $WhereStockCat;

	} elseif (!isset($_POST['StockCode']) and !isset($_POST['Keywords'])) {
		$SQL .= " WHERE stockmaster.categoryid='" . $_POST['StockCat'] . "'";

	}
	$SQL .= ' AND (departments.authoriser="' . $_SESSION['UserID'] . '" OR userid="' . $_SESSION['UserID'] . '") ';
	$SQL .= $SQLConstraint;
	$SQL .= " GROUP BY stockmaster.stockid,
					    stockmaster.description,
					    stockmaster.decimalplaces,
					    stockmaster.units
					    ORDER BY stockmaster.stockid";
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	return $StockItemsResult;

}
?>