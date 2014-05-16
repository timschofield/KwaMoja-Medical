<?php

function ChooseMenuOption($ch, $RootPath, $ServerPath, $TestSessionID, $IndexPage, $MenuOption) {
	$i=0;
	do {
		$i++;
	} while ($i<sizeOf($IndexPage[1]) and substr($IndexPage[1][$i]['value'],4) != $MenuOption);
	if ($i>=sizeOf($IndexPage[1])) {
		error_log('Error finding option '.$MenuOption.'. Link not found.'."\n", 3, '/home/tim/kwamoja'.date('Ymd').'.log');
		return false;
	}

	$SelectedPage = new URLDetails($TestSessionID);
	$SelectedPage->SetURL($ServerPath.$IndexPage[1][$i]['href']);

	$Page=$SelectedPage->FetchPage($RootPath, $ServerPath, $ch);
	return $Page;

}

?>