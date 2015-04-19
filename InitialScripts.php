<?php
$PageSecurity = 15;

include('includes/session.inc');

$Title = _('Initialise New Installation');

include('includes/header.inc');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Initialise New Installation') . '" alt="' . _('Initialise New Installation') . '" />' . $Title . '
	</p>';

echo '<div class="page_help_text">' .
		_('You have reached this page because you have successfuly installed ') . $ProjectName . _(', but there are still some pages that need to be setup.') . '<br />' .
		_('Click on the link below and you will be taken to the next page you need to complete.') . '<br />' .
		_('Once you have completed the page, click on the main menu icon and if there are more pages to be completed you will be brought back here.') . '
	</div>';

$ThisScript = '';
while ($ThisScript == '' and filesize('install/InitialScripts.txt') > 0) {
	$NextScript = ReadFirstLine('install/InitialScripts.txt');
	if (trim($NextScript) == 'Currencies.php') {
		$CurrenciesSQL = "SELECT currency FROM currencies";
		$CurrenciesResult = DB_query($CurrenciesSQL);
		if (DB_num_rows($CurrenciesResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'CompanyPreferences.php' and $ThisScript == '') {
		$CompanySQL = "SELECT coycode FROM companies";
		$CompanyResult = DB_query($CompanySQL);
		if (DB_num_rows($CompanyResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'TaxProvinces.php' and $ThisScript == '') {
		$ProvinceSQL = "SELECT taxprovinceid FROM taxprovinces";
		$ProvinceResult = DB_query($ProvinceSQL);
		if (DB_num_rows($ProvinceResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'TaxAuthorities.php' and $ThisScript == '') {
		$AuthoritySQL = "SELECT taxid FROM taxauthorities";
		$AuthorityResult = DB_query($AuthoritySQL);
		if (DB_num_rows($AuthorityResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			$Row = DB_fetch_row($AuthorityResult);
			$_SESSION['TaxAuthority'] = $Row[0];
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'TaxCategories.php' and $ThisScript == '') {
		$CategorySQL = "SELECT taxcatid FROM taxcategories";
		$CategoryResult = DB_query($CategorySQL);
		if (DB_num_rows($CategoryResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'TaxAuthorityRates.php' and $ThisScript == '') {
		$AuthRatesSQL = "SELECT SUM(taxrate) FROM taxauthrates";
		$AuthRatesResult = DB_query($AuthRatesSQL);
		$Row = DB_fetch_row($AuthRatesResult);
		if ($Row[0] == 0) {
			$ThisScript = 'TaxAuthorityRates.php?TaxAuthority=' . $_SESSION['TaxAuthority'];
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'Locations.php' and $ThisScript == '') {
		$LocationsSQL = "SELECT loccode FROM locations";
		$LocationsResult = DB_query($LocationsSQL);
		if (DB_num_rows($LocationsResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'SalesTypes.php' and $ThisScript == '') {
		$SalesTypesSQL = "SELECT typeabbrev FROM salestypes";
		$SalesTypesResult = DB_query($SalesTypesSQL);
		if (DB_num_rows($SalesTypesResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'Shippers.php' and $ThisScript == '') {
		$ShippersSQL = "SELECT shipper_id FROM shippers";
		$ShippersResult = DB_query($ShippersSQL);
		if (DB_num_rows($ShippersResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			RemoveFirstLine('install/InitialScripts.txt');
		}
	}
	if (trim($NextScript) == 'SystemParameters.php' and $ThisScript == '') {
		$ThisScript = $NextScript;
		RemoveFirstLine('install/InitialScripts.txt');
	}
}

echo '<div class="centre">
		<a href="' . $RootPath . '/' . $ThisScript . '">' . _('Click here to continue') . '</a>
	</div>';

include('includes/footer.inc');

function ReadFirstLine($FileName) {
  $file = file($FileName);
  return $file[0];
}

function RemoveFirstLine($FileName) {
	$FileContents = file($FileName);
	if ($fp = fopen($FileName, 'w')) {
		for($i=1; $i < sizeof($FileContents); $i++) {
			fwrite($fp,$FileContents[$i]);
		}
		fclose($fp);
	} else {
		prnMsg('Cannot open file for writing', 'error');
	}
}

?>