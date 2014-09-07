<?php

include('includes/session.inc');

if (isset($_GET['WO'])) {
	$WO = filter_number_format($_GET['WO']);
} elseif (isset($_POST['WO'])) {
	$WO = filter_number_format($_POST['WO']);
} else {
	$WO = '';
}

if (isset($_GET['StockId'])) {
	$StockId = $_GET['StockId'];
} elseif (isset($_POST['StockId'])) {
	$StockId = $_POST['StockId'];
}

if (isset($_GET['Location'])) {
	$Location = $_GET['Location'];
} elseif (isset($_POST['Location'])) {
	$Location = $_POST['Location'];
}


if (isset($WO) and isset($StockId) and $WO != '') {

	$SQL = "SELECT woitems.qtyreqd,
					woitems.qtyrecd,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
			FROM woitems, stockmaster
			WHERE stockmaster.stockid = woitems.stockid
				AND woitems.wo = '" . $WO . "'
				AND woitems.stockid = '" . $StockId . "' ";

	$ErrMsg = _('The SQL to find the details of the item to produce failed');
	$ResultItems = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($ResultItems) != 0) {
		include('includes/PDFStarter.php');

		$PDF->addInfo('Title', _('WO Production Slip'));
		$PDF->addInfo('Subject', _('WO Production Slip'));

		while ($myItem = DB_fetch_array($ResultItems)) {
			// print the info of the parent product
			$FontSize = 10;
			$PageNumber = 1;
			$line_height = 12;
			$Xpos = $Left_Margin + 1;
			$fill = false;

			$QtyPending = $myItem['qtyreqd'] - $myItem['qtyrecd'];

			PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $StockId, $myItem['description'], $QtyPending, $myItem['units'], $myItem['decimalplaces'], $ReportDate);

			$PartCounter = 0;

			$SQLBOM = "SELECT bom.parent,
						bom.component,
						bom.quantity AS bomqty,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.description,
						stockmaster.shrinkfactor,
						locstock.quantity AS qoh
					FROM bom, stockmaster, locstock
					WHERE bom.component = stockmaster.stockid
						AND bom.component = locstock.stockid
						AND locstock.loccode = '" . $Location . "'
						AND bom.parent = '" . $StockId . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'
						AND (bom.effectiveto > '" . Date('Y-m-d') . "'
						 OR bom.effectiveto='0000-00-00')";

			$ErrMsg = _('The bill of material could not be retrieved because');
			$BOMResult = DB_query($SQLBOM, $ErrMsg);
			while ($myComponent = DB_fetch_array($BOMResult)) {

				$ComponentNeeded = $myComponent['bomqty'] * $QtyPending;
				$PrevisionShrinkage = $ComponentNeeded * ($myComponent['shrinkfactor'] / 100);

				$Xpos = $Left_Margin + 1;

				$PDF->addTextWrap($Xpos, $YPos, 150, $FontSize, $myComponent['component'], 'left');
				$PDF->addTextWrap(150, $YPos, 50, $FontSize, locale_number_format($myComponent['bomqty'], $myComponent['decimalplaces']), 'right');
				$PDF->addTextWrap(200, $YPos, 30, $FontSize, $myComponent['units'], 'left');
				$PDF->addTextWrap(230, $YPos, 50, $FontSize, locale_number_format($ComponentNeeded, $myComponent['decimalplaces']), 'right');
				$PDF->addTextWrap(280, $YPos, 30, $FontSize, $myComponent['units'], 'left');
				$PDF->addTextWrap(310, $YPos, 50, $FontSize, locale_number_format($PrevisionShrinkage, $myComponent['decimalplaces']), 'right');
				$PDF->addTextWrap(360, $YPos, 30, $FontSize, $myComponent['units'], 'left');

				$YPos -= $line_height;

				if ($YPos < $Bottom_Margin + $line_height) {
					PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $Stockid, $myItem['description'], $QtyPending, $myItem['units'], $myItem['decimalplaces'], $ReportDate);
				}
			}
		}

		// Production Notes
		$PDF->addTextWrap($Xpos, $YPos - 50, 100, $FontSize, _('Incidences / Production Notes :'), 'left');
		$YPos -= (8 * $line_height);

		//add components prepared by
		$PDF->addTextWrap(40, $YPos - 50, 100, $FontSize, _('Components Prepared By :'), 'left');
		$PDF->addTextWrap(40, $YPos - 70, 100, $FontSize, _('Name'), 'left');
		$PDF->addTextWrap(80, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(40, $YPos - 90, 100, $FontSize, _('Date'), 'left');
		$PDF->addTextWrap(80, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(40, $YPos - 110, 100, $FontSize, _('Hour'), 'left');
		$PDF->addTextWrap(80, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(40, $YPos - 150, 100, $FontSize, _('Signature'), 'left');
		$PDF->addTextWrap(80, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $fill);

		//add Produced by
		$PDF->addTextWrap(200, $YPos - 50, 100, $FontSize, _('Item Produced By :'), 'left');
		$PDF->addTextWrap(200, $YPos - 70, 100, $FontSize, _('Name'), 'left');
		$PDF->addTextWrap(240, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(200, $YPos - 90, 100, $FontSize, _('Date'), 'left');
		$PDF->addTextWrap(240, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(200, $YPos - 110, 100, $FontSize, _('Hour'), 'left');
		$PDF->addTextWrap(240, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(200, $YPos - 150, 100, $FontSize, _('Signature'), 'left');
		$PDF->addTextWrap(240, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $fill);

		//add Quality Control by
		$PDF->addTextWrap(400, $YPos - 50, 100, $FontSize, _('Quality Control By :'), 'left');
		$PDF->addTextWrap(400, $YPos - 70, 100, $FontSize, _('Name'), 'left');
		$PDF->addTextWrap(440, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(400, $YPos - 90, 100, $FontSize, _('Date'), 'left');
		$PDF->addTextWrap(440, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(400, $YPos - 110, 100, $FontSize, _('Hour'), 'left');
		$PDF->addTextWrap(440, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $fill);
		$PDF->addTextWrap(400, $YPos - 150, 100, $FontSize, _('Signature'), 'left');
		$PDF->addTextWrap(440, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $fill);

		if ($YPos < $Bottom_Margin + $line_height) {
			PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $Stockid, $myItem['description'], $QtyPending, $myItem['units'], $myItem['decimalplaces'], $ReportDate);
		}

		$PDF->OutputD('WO-' . $WO . '-' . $StockId . '-' . Date('Y-m-d') . '.pdf');
		$PDF->__destruct();
	} else {
		$Title = _('WO Item production Slip');
		include('includes/header.inc');
		prnMsg(_('There were no items with ready to produce'), 'info');
		prnMsg($SQL);
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;

	}
}


function PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $StockId, $Description, $Qty, $UOM, $DecimalPlaces, $ReportDate) {

	/*PDF page header for MRP Planned Work Orders report */
	if ($PageNumber > 1) {
		$PDF->newPage();
	}
	$line_height = 12;
	$FontSize = 10;
	$YPos = $Page_Height - $Top_Margin;

	$PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);
	$PDF->addTextWrap(190, $YPos, 100, $FontSize, $ReportDate);
	$PDF->addTextWrap($Page_Width - $Right_Margin - 150, $YPos, 160, $FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber, 'left');
	$YPos -= $line_height;

	$PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, _('Work Order Item Production Slip'));
	$YPos -= (2 * $line_height);

	$PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, _('WO') . ': ' . $WO);
	$YPos -= $line_height;

	$PDF->addTextWrap($Left_Margin, $YPos, 500, $FontSize, _('Item Code') . ': ' . $StockId . ' --> ' . $Description);
	$YPos -= $line_height;

	$PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, _('Quantity') . ': ' . locale_number_format($Qty, $DecimalPlaces) . ' ' . $UOM);
	$YPos -= (2 * $line_height);

	if (file_exists($_SESSION['part_pics_dir'] . '/' . $StockId . '.jpg')) {
		$PDF->Image($_SESSION['part_pics_dir'] . '/' . $StockId . '.jpg', 135, $Page_Height - $Top_Margin - $YPos + 10, 200, 200);
		$YPos -= (16 * $line_height);
	}
	/*end checked file exist*/


	/*set up the headings */
	$Xpos = $Left_Margin + 1;

	$PDF->addTextWrap($Xpos, $YPos, 150, $FontSize, _('Component Code'), 'left');
	$PDF->addTextWrap(150, $YPos, 50, $FontSize, _('Qty BOM'), 'right');
	$PDF->addTextWrap(200, $YPos, 30, $FontSize, _(''), 'left');
	$PDF->addTextWrap(230, $YPos, 50, $FontSize, _('Qty Needed'), 'right');
	$PDF->addTextWrap(280, $YPos, 30, $FontSize, _(''), 'left');
	$PDF->addTextWrap(310, $YPos, 50, $FontSize, _('Shrinkage'), 'right');
	$PDF->addTextWrap(360, $YPos, 30, $FontSize, _(''), 'left');

	$FontSize = 10;
	$YPos -= $line_height;

	$PageNumber++;
}

?>