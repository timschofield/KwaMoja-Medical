<?php

$Host = $_SESSION['Installer']['HostName'];
$DBUser = $_SESSION['Installer']['UserName'];
$DBPassword = $_SESSION['Installer']['Password'];
$DBType = $_SESSION['Installer']['DBMS'];
$_SESSION['DatabaseName'] = $_SESSION['Installer']['Database'];
$DefaultDatabase = 'default';

/* Try to connect to the DBMS */
switch($_SESSION['Installer']['DBMS']) {
	case 'mariadb':
		$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
		break;
	case 'mysql':
		$DB = @mysql_connect($_SESSION['Installer']['HostName'] . ':' . $_SESSION['Installer']['DBPort'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
		break;
	case 'mysqli':
		$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
		break;
	case 'posgres':
		$DB = pg_connect('host=' . $_SESSION['Installer']['HostName'] . ' dbname=kwamoja port=5432 user=postgres');;
		break;
	default:
		$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
		break;
}
if (!$DB) {
	$Errors[] = _('Failed to connect the database management system');
}

include($PathPrefix . 'includes/ConnectDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
include($PathPrefix . 'includes/UpgradeDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
include($PathPrefix . 'includes/DateFunctions.inc');
date_default_timezone_set($_SESSION['Installer']['TimeZone']);
$Path_To_Root = '..';
$Config_File = $Path_To_Root . '/config.php';
if (!file_exists($Path_To_Root . '/companies/' . $_SESSION['Installer']['Database'])) {
	$CompanyDir = $Path_To_Root . '/companies/' . $_SESSION['Installer']['Database'];
	$Result = mkdir($CompanyDir);
	$Result = mkdir($CompanyDir . '/part_pics');
	$Result = mkdir($CompanyDir . '/EDI_Incoming_Orders');
	$Result = mkdir($CompanyDir . '/reports');
	$Result = mkdir($CompanyDir . '/EDI_Sent');
	$Result = mkdir($CompanyDir . '/EDI_Pending');
	$Result = mkdir($CompanyDir . '/reportwriter');
	$Result = mkdir($CompanyDir . '/pdf_append');
	$Result = mkdir($CompanyDir . '/FormDesigns');
	copy($Path_To_Root . '/companies/default/FormDesigns/GoodsReceived.xml', $CompanyDir . '/FormDesigns/GoodsReceived.xml');
	copy($Path_To_Root . '/companies/default/FormDesigns/PickingList.xml', $CompanyDir . '/FormDesigns/PickingList.xml');
	copy($Path_To_Root . '/companies/default/FormDesigns/PurchaseOrder.xml', $CompanyDir . '/FormDesigns/PurchaseOrder.xml');
	copy($Path_To_Root . '/companies/default/FormDesigns/Journal.xml', $CompanyDir . '/FormDesigns/Journal.xml');
	echo '<div class="success">' . _('The companies directory has been successfully created') . '</div>';
	ob_flush();
	if (isset($File_Temp_Name)) {
		$Result = move_uploaded_file($File_Temp_Name, $CompanyDir . '/logo.jpg');

	} else {
		$Result = copy($Path_To_Root . '/logo_server.jpg', $CompanyDir . '/logo.jpg');
	}
	if ($Result) {
		echo '<div class="success">' . _('Your logo has been successfully uploaded') . '</div>';
	} else {
		echo '<div class="warn">' . _('Your logo could not be uploaded. You must copy this to your companies directory later.') . '</div>';
	}
	ob_flush();
} else {
	echo '<div class="error">' . _('This company name already exists') . '</div>';
	exit;
}
//$Msg holds the text of the new config.php file
$Msg = "<?php\n\n";
$Msg .= "// User configurable variables\n";
$Msg .= "//---------------------------------------------------\n\n";
$Msg .= "//DefaultLanguage to use for the login screen and the setup of new users.\n";
$Msg .= "\$DefaultLanguage = '" . $_SESSION['Installer']['Language'] . "';\n\n";
$Msg .= "// Whether to display the demo login and password or not on the login screen\n";
$Msg .= "\$AllowDemoMode = FALSE;\n\n";
$Msg .= "// Connection information for the database\n";
$Msg .= "// \$Host is the computer ip address or name where the database is located\n";
$Msg .= "// assuming that the webserver is also the sql server\n";
$Msg .= "\$Host = '" . $Host . "';\n\n";
$Msg .= "// assuming that the web server is also the sql server\n";
$Msg .= "\$DBType = '" . $_SESSION['Installer']['DBMS'] . "';\n";
$Msg .= "//assuming that the web server is also the sql server\n";
$Msg .= "\$DBUser = '" . $DBUser . "';\n";
$Msg .= "\$DBPassword = '" . $DBPassword . "';\n";
$Msg .= "// The timezone of the business - this allows the possibility of having;\n";
$Msg .= "define('TIMEZONE', '" . $_SESSION['Installer']['TimeZone'] . "');\n";
$Msg .= "date_default_timezone_set(TIMEZONE);\n";
$Msg .= "\$AllowCompanySelectionBox = 'ShowSelectionBox';\n";
$Msg .= "//The system administrator name use the user input mail;\n";
if (strtolower($_SESSION['Installer']['Email']) != 'admin@kwamoja.com') {
	$Msg .= "\$SysAdminEmail = '" . $_SESSION['Installer']['Email'] . "';\n";
}
if (isset($NewCompany)) {
	$Msg .= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
} else {
	$Msg .= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
}
$Msg .= "\$SessionLifeTime = 3600;\n";
$Msg .= "\$MaximumExecutionTime = 120;\n";
$Msg .= "\$DefaultClock = 12;\n";
$Msg .= "\$RootPath = dirname(htmlspecialchars(\$_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));\n";
$Msg .= "if (isset(\$DirectoryLevelsDeep)){\n";
$Msg .= "   for (\$i=0;\$i<\$DirectoryLevelsDeep;\$i++){\n";
$Msg .= "		\$RootPath = mb_substr(\$RootPath,0, strrpos(\$RootPath,'/'));\n";
$Msg .= "	}\n";
$Msg .= "}\n";

$Msg .= "if (\$RootPath == '/' OR \$RootPath == '\\\') {\n";
$Msg .= "	\$RootPath = '';\n";
$Msg .= "}\n";
$Msg .= "error_reporting(E_ALL && ~E_NOTICE);\n";
$Msg .= "/* Make sure there is nothing - not even spaces after this last ?> */\n";
$Msg .= "?>";

//write the config.php file since we have test the writability of the root path and companies,
//there is little possibility that it will fail here. So just an warn if it is failed.
if (!$zp = fopen($Path_To_Root . '/config.php', 'w')) {
	echo '<div class="error">' . _("Cannot open the configuration file") . $Config_File . '</div>';
} else {
	if (!fwrite($zp, $Msg)) {
		fclose($zp);
		echo '<div class="error">' . _("Cannot write to the configuration file") . $Config_File . '</div>';
	}
	//close file
	fclose($zp);
}
echo '<div class="success">' . _('The config.php file has been created based on your settings.') . '</div>';
ob_flush();

$InitialScripts[] = 'Currencies.php';
$InitialScripts[] = 'CompanyPreferences.php';
$InitialScripts[] = 'TaxProvinces.php';
$InitialScripts[] = 'TaxAuthorities.php';
$InitialScripts[] = 'TaxCategories.php';
$InitialScripts[] = 'TaxAuthorityRates.php';
$InitialScripts[] = 'Locations.php';
$InitialScripts[] = 'SalesTypes.php';
$InitialScripts[] = 'Shippers.php';
$InitialScripts[] = 'SystemParameters.php';

$FileHandle = fopen($Path_To_Root . '/install/InitialScripts.txt', 'w');
foreach ($InitialScripts as $InitialScript) {
	fwrite($FileHandle, $InitialScript . "\n");
}
fclose($FileHandle);

echo '<legend>' . _('Building your database.') . ' ' . _('This may take some time, please be patient') . '</legend>';
echo '<div id="progress" class="centre" style="border-radius: 5px;width:100%;border:1px solid #157213;"></div>';
echo '<div id="information" style="width"></div>';
$StartingUpdate = 0;
$EndingUpdate = HighestFileName('../');
unset($_SESSION['Updates']);
$_SESSION['Updates']['Errors'] = 0;
$_SESSION['Updates']['Successes'] = 0;
$_SESSION['Updates']['Warnings'] = 0;
for ($UpdateNumber = $StartingUpdate; $UpdateNumber <= $EndingUpdate; $UpdateNumber++) {
	if (file_exists($PathPrefix . 'sql/updates/' . $UpdateNumber . '.php')) {
		$percent = intval($UpdateNumber / $EndingUpdate * 100) . "%";
		echo '<script language="javascript">
						document.getElementById("progress").innerHTML="<div style=\"margin: 1px;border-radius: 5px; width:' . $percent . ';background-color:#157213;\">&nbsp;</div>";
						document.getElementById("information").innerHTML="' . $UpdateNumber . ' file(s) processed.";
					</script>';
		echo str_repeat(' ', 1024 * 4);
		$SQL = "SET foreign_key_checks=0";
		$Result = executeSQL($SQL, $DB, False);
		flush();
		if ($Result == 0) {
			include($PathPrefix . 'sql/updates/' . $UpdateNumber . '.php');
		}
		flush();
	}
}
if ($_SESSION['Updates']['Errors'] > 0) {
	echo 'Number of Errors ' . $_SESSION['Updates']['Errors'];
	foreach ($_SESSION['Updates']['Messages'] as $Message) {
		echo '<div class="error">' . $Message . '</div>';
	}
	exit;
}

function CryptPass($Password ) {
	if (PHP_VERSION_ID < 50500) {
		$Salt = base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
		$Salt = str_replace('+', '.', $Salt);
		$Hash = crypt($Password, '$2y$10$'.$Salt.'$');
	} else {
		$Hash = password_hash($Password,PASSWORD_DEFAULT);
	}
	return $Hash;
}

InsertRecord('www_users', array('userid'),
							array('admin'),
							array('userid', 'password', 'realname', 'email', 'displayrecordsmax', 'fullaccess', 'cancreatetender', 'modulesallowed', 'blocked', 'theme', 'language', 'pdflanguage', 'fontsize'),
							array($_SESSION['Installer']['AdminAccount'], CryptPass($_SESSION['Installer']['KwaMojaPassword']), $_SESSION['Installer']['AdminAccount'], $_SESSION['Installer']['Email'], 50, 1, 1, '1,1,1,1,1,1,1,1,1,1,1,1,', 0, 'aguapop', $_SESSION['Installer']['Language'], 0, 0)
						, $DB);
/* Now we uploade the chosen chart of accounts */
if (!isset($_POST['Demo'])) {
	$SQL = "SET foreign_key_checks=0";
	$Result = executeSQL($SQL, $DB, False);
	include($PathPrefix . 'install/coa/' . $_SESSION['Installer']['CoA']);
	echo '<div class="success">' . _('Your chosen chart of accounts has been uploaded') . '</div>';
	ob_flush();
	/* Create the admin user */
} else {
	echo '<legend>' . _('Populating the database with demo data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/data.sql', $DB, $DBType, false, $_SESSION['Installer']['Database']);
}

ChangeConfigValue('VersionNumber', '14.02');

function HighestFileName($PathPrefix) {
	$files = glob($PathPrefix . 'sql/updates/*.php');
	natsort($files);
	return basename(array_pop($files), ".php");
}

function executeSQL($SQL, $TrapErrors = False) {
	global $SQLFile;
	/* Run an sql statement and return an error code */
	if (!isset($SQLFile)) {
		DB_IgnoreForeignKeys();
		$Result = DB_query($SQL, '', '', false, $TrapErrors);
		$ErrorNumber = DB_error_no();
		DB_ReinstateForeignKeys();
		return $ErrorNumber;
	} else {
		fwrite($SQLFile, $SQL . ";\n");
	}
}

function updateDBNo($NewNumber) {
	global $SQLFile;
	if (!isset($SQLFile)) {
		$SQL = "UPDATE config SET confvalue='" . $NewNumber . "' WHERE confname='DBUpdateNumber'";
		executeSQL($SQL);
		$_SESSION['DBUpdateNumber'] = $NewNumber;
	}
}
//@para $File is the sql file name
//@para $DB is the DB connect reference
//@para $DBType refer to mysqli or mysql connection
//@para $NewDB is the new database name
//@para $DemoDB is the demo database name
//The purpose of this function is populate the database with mysql extention
function PopulateSQLDataBySQL($File, $DB, $DBType, $NewDB = false, $DemoDB = 'kwamojademo') {
	$DBName = ($NewDB) ? $NewDB : $DemoDB;
	($DBType == 'mysql') ? mysql_select_db($DBName, $DB) : mysqli_select_db($DB, $DBName);
	$SQLScriptFile = file($File);
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$SQL = '';
	$InAFunction = false;
	echo '<div id="progress' . $File . '" class="centre" style="border-radius: 5px;width:100%;border:1px solid #157213;"></div>';
	echo '<div id="information' . $File . '" style="width"></div>';
	for ($i = 1; $i <= $ScriptFileEntries; $i++) {

		$SQLScriptFile[$i - 1] = trim($SQLScriptFile[$i - 1]);
		//ignore lines that start with -- or USE or /*

		$SQL .= ' ' . $SQLScriptFile[$i - 1];

		//check if this line kicks off a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i - 1], 0, 15) == 'CREATE FUNCTION') {
			$InAFunction = true;
		}
		//check if this line completes a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i - 1], 0, 8) == 'LANGUAGE') {
			$InAFunction = false;
		}
		if (mb_strpos($SQLScriptFile[$i - 1], ';') > 0 and !$InAFunction) {
			// Database created above with correct name.
			$Result = ($DBType == 'mysql') ? mysql_query($SQL, $DB) : mysqli_query($DB, $SQL);
			$SQL = '';
		}
		$percent = intval($i / $ScriptFileEntries * 100) . "%";
		echo '<script language="javascript">
						document.getElementById("progress' . $File . '").innerHTML="<div style=\"margin: 1px;border-radius: 5px; width:' . $percent . ';background-color:#157213;\">&nbsp;</div>";
						document.getElementById("information' . $File . '").innerHTML="' . ($i - 1) . ' row(s) processed.";
					</script>';
		echo str_repeat(' ', 1024 * 4);
		flush();

	} //end of for loop around the lines of the sql script
}

echo '<form id="DatabaseConfig" action="../index.php" method="post">';
echo '<fieldset>
			<legend>' . _('KwaMoja Installation Is Completed') . '</legend>
			<div class="page_help_text">
				<ul>
					<li>' . _('The KwaMoja installation has been successfully completed..') . '</li>
					<li>' . _('Click on the button below to start using KwaMoja.') . '</li>
					<li>' . _('When you first sign in you will be taken to a series of screens to help you set KwaMoja up.') . '</li>
					<li>' . _('Please enjoy using the software and hopefully contribute back to the project.') . '</li>
				</ul>
			</div>
		</fieldset>';

echo '<fieldset style="text-align:center">
		<button type="submit" name="end">' . _('Start KwaMoja') . '<img src="restart.png" style="float:right" /></button>
	</fieldset>
</form>';

?>