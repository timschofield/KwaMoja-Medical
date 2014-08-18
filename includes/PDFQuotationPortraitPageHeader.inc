<?php

/*	Please note that addTextWrap prints a font-size-height further down than
	addText and other functions.*/

// $PageNumber is initialised in 0 by includes/PDFStarter.php.
$PageNumber++;// Increments $PageNumber before printing.
if ($PageNumber > 1) {// Inserts a page break if it is not the first page.
	$PDF->newPage();
}

$XPos = $Page_Width / 2 - 140;
$PDF->addJpegFromFile($_SESSION['LogoFile'], $XPos + 90, 720, 0, 60);

$XPos = $XPos + 130;

$FontSize = 18;
$PDF->addText($XPos, 820, $FontSize, _('Quotation'));
$FontSize = 12;
$YPos = 720;
$PDF->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize = 10;
$PDF->addText($XPos, $YPos - 12, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$PDF->addText($XPos, $YPos - 21, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$PDF->addText($XPos, $YPos - 39, $FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$PDF->addText($XPos, $YPos - 48, $FontSize, $_SESSION['CompanyRecord']['email']);

$XPos = 46;
$YPos = 770;

$FontSize = 12;
$MyRow = array_map(html_entity_decode, $MyRow);
$PDF->addText($XPos, $YPos + 10, $FontSize, _('Delivery To') . ':');
$PDF->addText($XPos, $YPos - 3, $FontSize, $MyRow['deliverto']);
$PDF->addText($XPos, $YPos - 15, $FontSize, $MyRow['deladd1']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $MyRow['deladd2']);
$PDF->addText($XPos, $YPos - 45, $FontSize, $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5']);

$YPos -= 80;

$PDF->addText($XPos, $YPos, $FontSize, _('Quotation For') . ':');
$PDF->addText($XPos, $YPos - 15, $FontSize, $MyRow['name']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $MyRow['address1']);
$PDF->addText($XPos, $YPos - 45, $FontSize, $MyRow['address2']);
$PDF->addText($XPos, $YPos - 60, $FontSize, $MyRow['address3'] . ' ' . $MyRow['address4'] . ' ' . $MyRow['address5']);

$XPos = 50;
$YPos += 25;
/*draw a nice curved corner box around the delivery details */
/*from the top right */
$PDF->partEllipse($XPos + 200, $YPos + 60, 0, 90, 10, 10);
/*line to the top left */
$PDF->line($XPos + 200, $YPos + 70, $XPos, $YPos + 70);
/*Dow top left corner */
$PDF->partEllipse($XPos, $YPos + 60, 90, 180, 10, 10);
/*Do a line to the bottom left corner */
$PDF->line($XPos - 10, $YPos + 60, $XPos - 10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$PDF->partEllipse($XPos, $YPos, 180, 270, 10, 10);
/*Now a line to the bottom right */
$PDF->line($XPos, $YPos - 10, $XPos + 200, $YPos - 10);
/*Now do the bottom right corner */
$PDF->partEllipse($XPos + 200, $YPos, 270, 360, 10, 10);
/*Finally join up to the top right corner where started */
$PDF->line($XPos + 210, $YPos, $XPos + 210, $YPos + 60);


$YPos -= 90;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$PDF->partEllipse($XPos + 200, $YPos + 60, 0, 90, 10, 10);
/*line to the top left */
$PDF->line($XPos + 200, $YPos + 70, $XPos, $YPos + 70);
/*Dow top left corner */
$PDF->partEllipse($XPos, $YPos + 60, 90, 180, 10, 10);
/*Do a line to the bottom left corner */
$PDF->line($XPos - 10, $YPos + 60, $XPos - 10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$PDF->partEllipse($XPos, $YPos, 180, 270, 10, 10);
/*Now a line to the bottom right */
$PDF->line($XPos, $YPos - 10, $XPos + 200, $YPos - 10);
/*Now do the bottom right corner */
$PDF->partEllipse($XPos + 200, $YPos, 270, 360, 10, 10);
/*Finally join up to the top right corner where started */
$PDF->line($XPos + 210, $YPos, $XPos + 210, $YPos + 60);

$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 1, 200, $FontSize, _('Number') . ': ' . $_GET['QuotationNo'], 'right');
$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 2, 200, $FontSize, _('Your Ref') . ': ' . $MyRow['customerref'], 'right');
$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 3, 200, $FontSize, _('Date') . ': ' . ConvertSQLDate($MyRow['quotedate']), 'right');
$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 4, 200, $FontSize, _('Page') . ': ' . $PageNumber, 'right');

$FontSize = 10;

$PDF->addText($Page_Width / 2 - 10, $YPos + 15, $FontSize, _('All amounts stated in') . ' - ' . $MyRow['currcode']);

$YPos -= 45;
$XPos = 40;

$LeftOvers = $PDF->addTextWrap($XPos + 2, $YPos, 100, $FontSize, _('Item Code'), 'left');
$LeftOvers = $PDF->addTextWrap(120, $YPos, 235, $FontSize, _('Item Description'), 'left');
$LeftOvers = $PDF->addTextWrap(180, $YPos, 85, $FontSize, _('Quantity'), 'right');
$LeftOvers = $PDF->addTextWrap(230, $YPos, 85, $FontSize, _('Price'), 'right');
$LeftOvers = $PDF->addTextWrap(280, $YPos, 85, $FontSize, _('Discount'), 'right');
$LeftOvers = $PDF->addTextWrap(330, $YPos, 85, $FontSize, _('Tax Class'), 'right');
$LeftOvers = $PDF->addTextWrap(400, $YPos, 85, $FontSize, _('Tax Amount'), 'right');
$LeftOvers = $PDF->addTextWrap($Page_Width-$Right_Margin - 90, $YPos, 90, $FontSize, _('Total'), 'right');

// Draws a box with round corners around line items:
$pdf->RoundRectangle(
	$Left_Margin,// RoundRectangle $XPos.
	$YPos + $FontSize + 5,// RoundRectangle $YPos.
	$Page_Width - $Left_Margin - $Right_Margin,// RoundRectangle $Width.
	$YPos + $FontSize - $Bottom_Margin + 5,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.
$YPos -= $FontSize;// This is to use addTextWrap's $YPos instead of normal $YPos.

?>