<?php
/*	Please note that addTextWrap() prints a font-size-height further down than
addText() and other functions. Use addText() instead of addTextWrap() to
print left aligned elements.*/

if (!$FirstPage) {
	/* only initiate a new page if its not the first */
	$PDF->newPage();
}

$YPos = $Page_Height - $Top_Margin;

// Company Logo:
/*$pdf->addJpegFromFile($_SESSION['LogoFile'], $Page_Width/2-118, $YPos-60, 0, 35);*/
$PDF->Image(
	$_SESSION['LogoFile'],// Name of the file containing the image.
	$Page_Width / 2 - 118,// Abscissa from left border to the upper-left corner (LTR).
	$Page_Height -($YPos - 60) -(35),// Ordinate from top border to the upper-left corner (LTR).
	0,// Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	35,// Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	''// Image format. If not specified, the type is inferred from the file extension.
);// Public function Image() in ~/includes/tcpdf/tcpdf.php.

$FontSize = 15;
if ($InvOrCredit == 'Invoice') {

	$PDF->addText($Page_Width / 2 - 60, $YPos, $FontSize, _('TAX INVOICE') . ' ');
} else {
	$PDF->addText($Page_Width / 2 - 60, $YPos, $FontSize, _('TAX CREDIT NOTE') . ' ');
}

// Prints page number:
$FontSize = 10;
$YPos -= $FontSize; //Downs one line height mesure (addText position is from left-bottom).
$PDF->addTextWrap($Page_Width - $Left_Margin - 72, $YPos, 72, $FontSize, _('Page') . ' ' . $PageNumber, 'right');


$XPos = $Page_Width - 265;
$YPos -= 85;
// Draws a rounded rectangle around billing details:
$PDF->RoundRectangle(
	$XPos - 10,// RoundRectangle $XPos.
	$YPos + 77,// RoundRectangle $YPos.
	245,// RoundRectangle $Width.
	97,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

$YPos = $Page_Height - $Top_Margin - 10;

$FontSize = 10;
$LineHeight = 13;
$LineCount = 1;
$PDF->addText($Page_Width - 268, $YPos - $LineCount * $LineHeight, $FontSize, _('Number'));
$PDF->addText($Page_Width - 180, $YPos - $LineCount * $LineHeight, $FontSize, $FromTransNo);
$LineCount += 1;
$PDF->addText($Page_Width - 268, $YPos - $LineCount * $LineHeight, $FontSize, _('Customer Code'));
$PDF->addText($Page_Width - 180, $YPos - $LineCount * $LineHeight, $FontSize, $MyRow['debtorno'] . ' ' . _('Branch') . ' ' . $MyRow['branchcode']);
$LineCount += 1;
$PDF->addText($Page_Width - 268, $YPos - $LineCount * $LineHeight, $FontSize, _('Date'));
$PDF->addText($Page_Width - 180, $YPos - $LineCount * $LineHeight, $FontSize, ConvertSQLDate($MyRow['trandate']));

if ($InvOrCredit == 'Invoice') {
	$LineCount += 1;
	$PDF->addText($Page_Width - 268, $YPos - $LineCount * $LineHeight, $FontSize, _('Order No'));
	$PDF->addText($Page_Width - 180, $YPos - $LineCount * $LineHeight, $FontSize, $MyRow['orderno']);
	$LineCount += 1;
	$PDF->addText($Page_Width - 268, $YPos - $LineCount * $LineHeight, $FontSize, _('Order Date'));
	$PDF->addText($Page_Width - 180, $YPos - $LineCount * $LineHeight, $FontSize, ConvertSQLDate($MyRow['orddate']));
	$LineCount += 1;
	$PDF->addText($Page_Width - 268, $YPos - $LineCount * $LineHeight, $FontSize, _('Dispatch Detail'));
	$PDF->addText($Page_Width - 180, $YPos - $LineCount * $LineHeight, $FontSize, $MyRow['shippername'] . '-' . $MyRow['consignment']);
	$LineCount += 1;
	$PDF->addText($Page_Width - 268, $YPos - $LineCount * $LineHeight, $FontSize, _('Dispatched From'));
	$PDF->addText($Page_Width - 180, $YPos - $LineCount * $LineHeight, $FontSize, $MyRow['locationname']);
}

/*End of the text in the right side box */

/*Now print out company info at the top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 20;

$FontSize = 10;
$LineHeight = 13;
$LineCount = 0;

$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize = 8;
$LineHeight = 10;

if ($_SESSION['CompanyRecord']['regoffice1'] <> '') {
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
}
if ($_SESSION['CompanyRecord']['regoffice2'] <> '') {
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
}
if (($_SESSION['CompanyRecord']['regoffice3'] <> '') OR ($_SESSION['CompanyRecord']['regoffice4'] <> '') OR ($_SESSION['CompanyRecord']['regoffice5'] <> '')) {
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']); // country in 6 not printed
}
$LineCount += 1;
$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, _('Phone') . ':' . $_SESSION['CompanyRecord']['telephone']);
$LineCount += 1;
$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$LineCount += 1;
$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);
$LineCount += 1;
$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, $_SESSION['TaxAuthorityReferenceName'] . ': ' . $_SESSION['CompanyRecord']['gstno']);

/*Now the customer company info */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 110;

$FontSize = 8;
$LineHeight = 10;
$LineCount = 0;

$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, _('Sold To') . ':');

$XPos += 20;
$FontSize = 10;
$LineHeight = 13;

if ($MyRow['invaddrbranch'] == 0) {
	$LineCount += 2;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['name']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['address1']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['address2']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['address3']) . ' ' . html_entity_decode($MyRow['address4']) . ' ' . html_entity_decode($MyRow['address5']) . ' ' . html_entity_decode($MyRow['address6']));
} else {
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['name']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['brpostaddr1']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['brpostaddr2']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['brpostaddr3']) . ' ' . html_entity_decode($MyRow['brpostaddr4']) . ' ' . html_entity_decode($MyRow['brpostaddr5']) . ' ' . html_entity_decode($MyRow['brpostaddr6']));
}

$XPos = $Page_Width - 265;
$YPos = $Page_Height - $Top_Margin - 120;

$FontSize = 8;
$LineHeight = 10;
$LineCount = 0;

if ($InvOrCredit == 'Invoice') {
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, _('Delivered To (check Dispatch Detail)') . ':');
	$FontSize = 10;
	$LineHeight = 13;
	$XPos += 20;
	$LineCount += 1;
	// Before trying to call htmlspecialchars_decode, check that its supported, if not substitute a compatible version
	if (!function_exists('htmlspecialchars_decode')) {
		function htmlspecialchars_decode($str) {
			$trans = get_html_translation_table(HTML_SPECIALCHARS);

			$decode = ARRAY();
			foreach ($trans AS $char => $entity) {
				$decode[$entity] = $char;
			}

			$str = strtr($str, $decode);

			return $str;
		}
	}
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['deliverto']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['deladd1']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['deladd2']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['deladd3']) . ' ' . html_entity_decode($MyRow['deladd4']) . ' ' . html_entity_decode($MyRow['deladd5']) . ' ' . html_entity_decode($MyRow['deladd6']));
	//$XPos -=80;
}
if ($InvOrCredit == 'Credit') {
	/* then its a credit note */
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, _('Charge Branch') . ':');
	$FontSize = 10;
	$LineHeight = 13;
	$XPos += 20;
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['brname']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['braddress1']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['braddress2']));
	$LineCount += 1;
	$PDF->addText($XPos, $YPos - $LineCount * $LineHeight, $FontSize, html_entity_decode($MyRow['braddress3']) . ' ' . html_entity_decode($MyRow['braddress4']) . ' ' . html_entity_decode($MyRow['braddress5']) . ' ' . html_entity_decode($MyRow['braddress6']));
	//$XPos -=80;
}

$XPos = $Left_Margin;

$YPos = $Page_Height - $Top_Margin - 190;
$XPos = $Left_Margin;
$FontSize = 8;

$PDF->addText($Left_Margin, $YPos - 8, $FontSize, _('All amounts stated in') . ': ' . $MyRow['currcode']);

$BoxHeight = $Page_Height - 282;

// Draws a rounded rectangle around line items:
$PDF->RoundRectangle(
	$Left_Margin,// RoundRectangle $XPos.
	$Bottom_Margin + $BoxHeight + 10,// RoundRectangle $YPos.
	$Page_Width - $Right_Margin - $Left_Margin,// RoundRectangle $Width.
	$BoxHeight + 10,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

$YPos -= 35;
/*Set up headings */
$FontSize = 10;
$LineHeight = 12;
$LineCount = 0;

$PDF->addText($Left_Margin + 2, ($YPos + $LineHeight) - $LineCount * $LineHeight, $FontSize, _('Cust. Tax Ref') . ':');
/*Print a vertical line */
$PDF->line($Left_Margin + 178, $YPos + $LineHeight, $Left_Margin + 178, $YPos - $LineHeight * 2 + 4);
$PDF->addText($Left_Margin + 180, ($YPos + $LineHeight) - $LineCount * $LineHeight, $FontSize, _('Cust. Reference No.') . ':');
/*Print a vertical line */
$PDF->line($Left_Margin + 358, $YPos + $LineHeight, $Left_Margin + 358, $YPos - $LineHeight * 2 + 4);
$PDF->addText($Left_Margin + 360, ($YPos + $LineHeight) - $LineCount * $LineHeight, $FontSize, _('Sales Person') . ':');
$LineCount += 1;
$PDF->addText($Left_Margin + 12, ($YPos + $LineHeight) - $LineCount * $LineHeight, $FontSize, $MyRow['taxref']);
if ($InvOrCredit == 'Invoice') {
	$PDF->addText($Left_Margin + 190, ($YPos + $LineHeight) - $LineCount * $LineHeight, $FontSize, $MyRow['customerref']);
}
$PDF->addText($Left_Margin + 370, ($YPos + $LineHeight) - $LineCount * $LineHeight, $FontSize, $MyRow['salesmanname']);

$YPos -= 20;

/*draw a line */
$PDF->line($XPos, $YPos, $Page_Width - $Right_Margin, $YPos);

$YPos -= 12;

$TopOfColHeadings = $YPos;

$PDF->addText($Left_Margin, $YPos + 12, $FontSize, _('Item Code'));
$PDF->addText($Left_Margin + 80, $YPos + 12, $FontSize, _('Description'));
$PDF->addText($Left_Margin + 270, $YPos + 12, $FontSize, _('Unit Price'));
$PDF->addText($Left_Margin + 350, $YPos + 12, $FontSize, _('Qty'));
$PDF->addText($Left_Margin + 390, $YPos + 12, $FontSize, _('UOM'));
$PDF->addText($Left_Margin + 420, $YPos + 12, $FontSize, _('Disc.'));
$PDF->addTextWrap($Page_Width - $Left_Margin - 72, $YPos, 72, $FontSize, _('Price'), 'right');

$YPos -= 0;

/*draw a line */
$PDF->line($XPos, $YPos, $Page_Width - $Right_Margin, $YPos);

$YPos -= ($line_height);

?>