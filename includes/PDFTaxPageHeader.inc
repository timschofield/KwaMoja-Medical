<?php

/* PDF page header for tax page report */

$PageNumber++;
if ($PageNumber > 1) {
	$PDF->newPage();
}

$FontSize = 8;
$YPos = $Page_Height - $Top_Margin;

$PDF->addText($Left_Margin, $YPos, $FontSize, html_entity_decode($_SESSION['CompanyRecord']['coyname']));

$YPos -= $line_height;

$FontSize = 10;
$PDF->addText($Left_Margin, $YPos, $FontSize, $ReportTitle);

$FontSize = 8;
$PDF->addText($Page_Width - $Right_Margin - 120, $YPos, $FontSize, _('Printed') . ': ' . Date("d M Y") . '    ' . _('Page') . ' ' . $PageNumber);

$YPos -= (3 * $line_height);

/*Draw a rectangle to put the headings in     */
$PDF->line($Page_Width - $Right_Margin, $YPos - 5, $Left_Margin, $YPos - 5);
$PDF->line($Page_Width - $Right_Margin, $YPos + $line_height, $Left_Margin, $YPos + $line_height);
$PDF->line($Page_Width - $Right_Margin, $YPos + $line_height, $Page_Width - $Right_Margin, $YPos - 5);
$PDF->line($Left_Margin, $YPos + $line_height, $Left_Margin, $YPos - 5);

/*set up the headings */
$Xpos = $Left_Margin + 1;

$LeftOvers = $PDF->addTextWrap(40, $YPos, 60 - $Left_Margin, $FontSize, _('Type'), 'centre');
$LeftOvers = $PDF->addTextWrap(100, $YPos, 40 - $Left_Margin, $FontSize, _('No'), 'centre');
$LeftOvers = $PDF->addTextWrap(140, $YPos, 60, $FontSize, _('Date'), 'centre');
$LeftOvers = $PDF->addTextWrap(240, $YPos, 60, $FontSize, _('Name'), 'centre');
$LeftOvers = $PDF->addTextWrap(350, $YPos, 60, $FontSize, _('Code'), 'centre');
$LeftOvers = $PDF->addTextWrap(450, $YPos, 60, $FontSize, _('Net'), 'centre');
$LeftOvers = $PDF->addTextWrap(510, $YPos, 60, $FontSize, _('Tax'), 'centre');

$YPos = $YPos - (2 * $line_height);

?>