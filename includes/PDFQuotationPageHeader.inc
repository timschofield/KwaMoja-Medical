<?php

/* pdf-php by R&OS code to set up a new sales order page */

/*	Please note that addTextWrap prints a font-size-height further down than
	addText and other functions.*/

// $PageNumber is initialised in 0 by includes/PDFStarter.php.
$PageNumber++;// Increments $PageNumber before printing.
if ($PageNumber > 1) {// Inserts a page break if it is not the first page.
	$PDF->newPage();
}

// Print 'Quotation' title
$PDF->addText(361, 500, 18, _('Quotation'));

// Print 'Quotation' info
$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 1, 200, $FontSize, _('Number') . ': ' . $_GET['QuotationNo'], 'right');
$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 2, 200, $FontSize, _('Your Ref') . ': ' . $MyRow['customerref'], 'right');
$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 3, 200, $FontSize, _('Date'). ': '.ConvertSQLDate($MyRow['quotedate']), 'right');
$PDF->addTextWrap($Page_Width - $Right_Margin - 200, $Page_Height - $Top_Margin - $FontSize * 4, 200, $FontSize, _('Page') . ': ' . $PageNumber, 'right');

// Print company logo
$PDF->addJpegFromFile($_SESSION['LogoFile'], 301, 520, 0, 60);

// Print company info
$XPos = 361;
$YPos = 482;
$FontSize = 14;
$PDF->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize = 10;
$PDF->addText($XPos, $YPos - 12, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$PDF->addText($XPos, $YPos - 21, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$PDF->addText($XPos, $YPos - 39, $FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$PDF->addText($XPos, $YPos - 48, $FontSize, $_SESSION['CompanyRecord']['email']);

$XPos = 46;
$YPos = 566;

$FontSize = 14;
$MyRow = array_map(html_entity_decode, $MyRow);
$PDF->addText($XPos, $YPos, $FontSize, _('Delivery To') . ':');
$PDF->addText($XPos, $YPos - 15, $FontSize, $MyRow['deliverto']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $MyRow['deladd1']);
$PDF->addText($XPos, $YPos - 45, $FontSize, $MyRow['deladd2']);
$PDF->addText($XPos, $YPos - 60, $FontSize, $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5']);

// Prints 'Quotation For' info

$YPos -= 80;

$PDF->addText($XPos, $YPos, $FontSize, _('Quotation For') . ':');
$PDF->addText($XPos, $YPos - 15, $FontSize, $MyRow['name']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $MyRow['address1']);
$PDF->addText($XPos, $YPos - 45, $FontSize, $MyRow['address2']);
$PDF->addText($XPos, $YPos - 60, $FontSize, $MyRow['address3'] . ' ' . $MyRow['address4'] . ' ' . $MyRow['address5']);

// Draw a rectangle with rounded corners around 'Delivery To' info
$XPos = 50;
$YPos += 12;
$PDF->RoundRectangle($XPos - 10, $YPos + 60 + 10, 225 + 10 + 10, 60 + 10 + 10, 10, 10); // Function RoundRectangle from includes/class.pdf.php

// Draw a rectangle with rounded corners around 'Quotation For' info
$YPos -= 82;
$PDF->RoundRectangle($XPos - 10, $YPos + 60 + 10, 225 + 10 + 10, 60 + 10 + 10, 10, 10); // Function RoundRectangle from includes/class.pdf.php

$FontSize = 10;

// Print the currency name
$PDF->addText($Page_Width / 2 - 60, $YPos - 5, $FontSize, _('All amounts stated in') . ' - ' . $MyRow['currcode'] . ' ' . _($MyRow['currency']));
// Print table header
$XPos = 40;
$YPos -= 37;

$LeftOvers = $PDF->addTextWrap(42, $YPos, 103, $FontSize, _('Item Code'), 'left');
if (strlen($LeftOvers) > 0) { // If translated text is greater than 103, prints remainder
	$LeftOvers = $PDF->addTextWrap(585, $YPos - $FontSize, 65, $FontSize, $LeftOvers, 'left');
}
$LeftOvers = $PDF->addTextWrap(145, $YPos, 250, $FontSize, _('Item Description'), 'left');
$LeftOvers = $PDF->addTextWrap(420, $YPos, 85, $FontSize, _('Quantity'), 'right');
$LeftOvers = $PDF->addTextWrap(485, $YPos, 85, $FontSize, _('Price'), 'right');
$LeftOvers = $PDF->addTextWrap(535, $YPos, 85, $FontSize, _('Discount'), 'right');
$LeftOvers = $PDF->addTextWrap(615, $YPos, 55, $FontSize, _('Tax Class'), 'right');
if (strlen($LeftOvers) > 0) { // If translated text is greater than 55, prints remainder
	$LeftOvers = $PDF->addTextWrap(615, $YPos - $FontSize, 55, $FontSize, $LeftOvers, 'right');
}
$LeftOvers = $PDF->addTextWrap(665, $YPos, 70, $FontSize, _('Tax Amount'), 'right');
if (strlen($LeftOvers) > 0) { // If translated text is greater than 70, prints remainder
	$LeftOvers = $PDF->addTextWrap(665, $YPos - $FontSize, 70, $FontSize, $LeftOvers, 'right');
}
$LeftOvers = $PDF->addTextWrap($Page_Width - $Right_Margin - 90, $YPos, 90, $FontSize, _('Total'), 'right');

// Draws a box with round corners around line items:
$PDF->RoundRectangle(
	$Left_Margin,// RoundRectangle $XPos.
	$YPos + $FontSize + 5,// RoundRectangle $YPos.
	$Page_Width - $Left_Margin - $Right_Margin,// RoundRectangle $Width.
	$YPos + $FontSize - $Bottom_Margin + 5,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

// Line under table headings:
$LineYPos = $YPos - $FontSize - 1;
$PDF->line($Page_Width - $Right_Margin, $LineYPos, $Left_Margin, $LineYPos);

$YPos -= $FontSize;// This is to use addTextWrap's $YPos instead of normal $YPos.

?>