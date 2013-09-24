<?php

if (isset($_POST['DBExt'])) {
	$_SESSION['Installer']['DBExt'] = $_POST['DBExt'];
}

if (isset($_POST['Email'])) {
	$_SESSION['Installer']['Email'] = $_POST['Email'];
}

if (isset($_POST['CompanyName'])) {
	$_SESSION['CompanyRecord']['coyname'] = $_POST['CompanyName'];
}

if (isset($_POST['Database'])) {
	$_SESSION['Installer']['Database'] = $_POST['Database'];
}

if (isset($_POST['COA'])) {
	$_SESSION['Installer']['COA'] = $_POST['COA'];
}

if (isset($_POST['adminaccount'])) {
	$_SESSION['Installer']['AdminAccount'] = $_POST['adminaccount'];
}

if (isset($_POST['KwaMojaPassword'])) {
	$_SESSION['Installer']['KwaMojaPassword'] = $_POST['KwaMojaPassword'];
}


echo '<body>
		<div id="CanvasDiv">';

/* Set error reprting level
 * = -1 for development
 * = 1 for production
 */
error_reporting(-1);

include('../includes/MiscFunctions.php');

//prevent the installation file from running again

if (isset($_POST['Install'])) { //confirm the final install data, the last validation step before we submit the data

	echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	//first do necessary validation
	//Since user may have changed the DatabaseName so we need check it again
	$InputError = 0;
	if (!empty($_POST['CompanyName'])) {
		//validate the Database name setting
		//The database name cannot contains illegal characters such as "/","\","." etc
		//and it should not contains illegal characters as file name such as "?""%"<"">"" " etc

		$DatabaseName = strtolower($_SESSION['Installer']['Database']);
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('The database name should not be empty') . '</div>';
	}
	if (!empty($_POST['TimeZone'])) {
		if (preg_match(',(Etc|Pacific|India|Europe|Australia|Atlantic|Asia|America|Africa)/[A-Z]{1}[a-zA-Z\-_/]+,', $_POST['TimeZone'])) {
			$TimeZone = $_POST['TimeZone'];
		} else {
			$InputError = 1;
			echo '<div class="error">' . _('The timezone must be legal') . '</div>';
		}
	}
	if (!empty($_POST['Demo']) and $_POST['Demo'] == 'on') {
		if (strtolower($DatabaseName) == 'kwamojademo') { //user select to install the kwamojademo
			$OnlyDemo = 1;
		} else {
			$DualCompany = 1; //user choose to install the demo company and production environment
		}
	} else { //user only choose to install the new kwamoja company
		$NewCompany = 1;
	}
	if (!empty($_POST['Email']) and IsEmailAddress($_POST['Email'])) {
		$Email = trim($_POST['Email']);
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('You must enter a valid email address for the Administrator.') . '</div>';
	}
	if (!empty($_POST['KwaMojaPassword']) and !empty($_POST['PasswordConfirm']) and $_POST['KwaMojaPassword'] == $_POST['PasswordConfirm']) {
		$AdminPassword = $_POST['KwaMojaPassword'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('Please correct the password. The password is either blank, or the password check does not match.') . '</div>';

	}
	if (!empty($_POST['HostName'])) {
		// As HTTP_HOST is user input, ensure it only contains characters allowed
		// in hostnames. See RFC 952 (and RFC 2181).
		// $_SERVER['HTTP_HOST'] is lowercased here per specifications.
		$_POST['HostName'] = strtolower($_POST['HostName']);
		$HostValid = preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $_POST['HostName']);
		if ($HostValid) {
			$host = $_POST['HostName'];
		} else {
			echo '<div class="error">' . _('The Host Name is not a valid name.') . '</div>';
			exit;
		}

	} else {
		$InputError = 1;
		echo '<div class="error">' . _('The Host Name must not be empty.') . '</div>';
	}
	if (!empty($_POST['UserName']) and strlen($_POST['UserName']) <= 16) { // dbms user
		$DBUser = $_POST['UserName'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . ('The user name cannot be empty and length must not be over 16 characters.') . '</div>';
	}
	if (isset($_POST['Password'])) { // dbms password
		$DBPassword = $_POST['Password'];
	}
	if (!empty($_POST['DBMS'])) { //get the dbms connect extension
		$_SESSION['Installer']['DBMS'] = $_POST['DBMS'];
	}
	if (!empty($_POST['UserLanguage'])) {
		if (preg_match(',^[a-z]{2}_[A-Z]{2}.utf8$,', $_POST['UserLanguage'])) {
			$UserLanguage = $_POST['UserLanguage'];
		} else {
			$InputError = 1;
			echo '<div class="error">' . _('The user language defintion is not in the correct format') . '</div>';
		}
	}
	if (!empty($_FILES['LogoFile'])) { //We check the file upload situation
		if ($_FILES['LogoFile']['error'] == UPLOAD_ERR_INI_SIZE || $_FILES['LogoFile']['error'] == UPLOAD_ERR_FORM_SIZE) { //the file is over the php.ini limit or over the from limit
			$InputError = 1;
			if (upload_max_filesize < 0.01) {
				echo '<div class="error">' . ('The company logo file failed to upload due to it\'s size. The file was over the upload_max_filesize set in your php.ini configuration.') . '</div>';

			} else {
				echo '<div class="error">' . ('The logo file failed to upload as it was over 10KB size limit.') . '</div>';
			}

		} elseif ($_FILES['LogoFile']['error'] == UPLOAD_ERR_OK) { //The file has been successfully uploaded
			$File_Temp_Name = $_FILES['LogoFile']['tmp_name'];
		} elseif ($_FILES['LogoFile']['error'] == UPLOAD_ERR_NO_FILE) { //There are no file uploaded by users
			$File_To_Copy = 1;
		}

	}
	if (!empty($_POST['COA'])) {
		$COA = $_POST['COA'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . _('There is no COA file selected. Please select a file.') . '</div>';

	}
	if ($InputError == 1) { //return to the company configuration stage
		CompanySetup($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName);
	} else {
		//start to installation

		//Now it is the time to create the mysql data
		//Just get the data from $COA and read data from this file
		//At the mean time, we should check the user need demo database or not
		$sql = 'CREATE DATABASE IF NOT EXISTS `' . $DatabaseName . '`';
		if ($_SESSION['Installer']['DBMS'] == 'mysqli') {
			$db = mysqli_connect($host, $DBUser, $DBPassword);
			$result = mysqli_query($db, $sql);
			if (!$db) {
				echo '<div class="error">' . _('Failed to connect the database, the error is ') . mysqli_connect_error() . '</div>';
			}
		}
		if ($_SESSION['Installer']['DBMS'] == 'mysql') {
			$db = mysql_connect($host, $DBUser, $DBPassword);
			$result = mysql_query($db, $sql);
			if (!$db) {
				echo '<div class="error">' . _('Failed to connect the database, the error is ') . mysql_connect_error() . '</div>';
			}
		}
		if ($_SESSION['Installer']['DBMS'] == 'mariadb') {
			$db = mysqli_connect($host, $DBUser, $DBPassword);
			$result = mysqli_query($db, $sql);
			if (!$db) {
				echo '<div class="error">' . _('Failed to connect the database, the error is ') . mysql_connect_error() . '</div>';
			}
		}
		$NewSQLFile = $Path_To_Root . '/install/coa/' . $COA;
		if (!$result) {
			if ($_SESSION['Installer']['DBMS'] == 'mysqli') {
				echo '<div class="error">' . _('Failed to create database kwamojademo and the error is ' . ' ' . mysqli_error($db)) . '</div>';
			} else {
				echo '<div class="error">' . _('Failed to create database kwamojademo and the error is ' . ' ' . mysql_error($db)) . '</div>';
			}
		}
		if (!isset($DBPort)) {
			$DBPort = 3306;
		}
		$_SESSION['DatabaseName'] = $DatabaseName;
		include('../includes/ConnectDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
		include('../includes/UpgradeDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
		PopulateSQLData($NewSQLFile, false, $db, $_SESSION['Installer']['DBMS'], $DatabaseName);
		DBUpdate($db, $DatabaseName, $_SESSION['Installer']['DBMS'], $AdminPassword, $Email, $UserLanguage, $DatabaseName);

		session_unset();

	} //end of the installation
	echo '<fieldset>
			<input type="hidden" name="SystemValid" value="1" />
			<button type="submit">' . _('Next Step') . '</button>
		</fieldset>';
	echo '</form>';

	exit;
}
//Handle the database configuration data. We'd like to check if the database information has been input correctly
//First try mysqli configuration

if (isset($_POST['DbConfig'])) {

	//validate those data first
	$InputError = 0; //Assume the best first
	if (!empty($_POST['HostName'])) {
		// As HTTP_HOST is user input, ensure it only contains characters allowed
		// in hostnames. See RFC 952 (and RFC 2181).
		// $_SERVER['HTTP_HOST'] is lowercased here per specifications.
		$_POST['HostName'] = strtolower($_POST['HostName']);
		$HostValid = preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $_POST['HostName']);
		if ($HostValid) {
			$host = $_POST['HostName'];
		} else {
			echo '<div class="error">' . _('The Host Name is illegal') . '</div>';
			exit;
		}


	} else {
		$InputError = 1;
		echo '<div class="error">' . _('The Host Name should not be empty') . '</div>';
	}
	if (!empty($_POST['Database'])) {
		//validate the Database name setting
		//The mysql database name cannot contains illegal characters such as "/","\","." etc
		//and it should not contains illegal characters as file name such as "?""%"<"">"" " etc
		//if prefix is set it should be added to database name
		if (!empty($_POST['Prefix'])) {
			$_POST['Database'] = $_POST['Prefix'] . $_POST['Database'];
		}
		if (preg_match(',[/\\\?%:\|<>\.\s"]+,', $_POST['Database'])) {
			$InputError = 1;
			echo '<div class="error">' . _('The database name should not contains illegal characters such as') . ' ' . '"/\?%:|<>"' . ' ' . _('or blank spaces') . '</div>';

		}
		$DatabaseName = $_POST['Database'];
	} else {
		$InputError = 1;
		echo '<div class="error">' . _('The database name should not be empty') . '</div>';
	}

	if (!empty($_POST['Password'])) {
		$DBPassword = $_POST['Password'];
	} else {
		$DBPassword = '';
	}
	if (!empty($_POST['UserLanguage'])) {
		$UserLanguage = $_POST['UserLanguage'];
	}
	if (!empty($_POST['UserName']) and mb_strlen($_POST['UserName']) <= 16) {
		$DBUser = trim($_POST['UserName']);
	}
	if ($InputError == 0) {
		DbCheck($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName, $_SESSION['Installer']['DBPort']);
		exit;
	} else {
		echo '<div class="error">' . _('Please correct the displayed error first') . '</div>';
		DbConfig($_POST['UserLanguage']);
		exit;
	}
	//	$db = mysqli_connect
	//if everything is OK, then we try to connect the DB, the database should be connect by two types of method, if there is no mysqli
} //end of users has submit the database configuration data

echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';

if (!isset($_POST['LanguageSet'])) {
	Installation();
} else { //The locale has been set, it's time to check the settings item.
}


//This function used to display the first screen for users to select they preferred langauage
//And at the mean time to check if the php configuration has meet requirements.
function Installation() {
}

//@para Language used to determine user's preferred language
//@para MysqlExt use to mark if mysql extension has been used by users
//The function used to provide a screen for users to input mysql server parameters data
function DbConfig($Language, $MysqlExt = FALSE) { //The screen for users to input mysql database information

	echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';
	exit;
}

//@para $UserLanguage is the language select by users and will be used as a default language
//@para $host is the Host of mysql server
//@para $DBUser is the name of the mysql user
//@para $DBPassword is the user's password which is stored in plain text in config.php
//@DatabaseName is the database used by kwamoja
//@$MysqlExt to check if it's use mysql extension in php instead of mysqli
//The function used to check if mysql parameters have been set correctly and can connect correctly

function DbCheck($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName, $DBPort) { //Check if the users have input the correct password
	$_SESSION['DatabaseName'] = $DatabaseName;
	$PathPrefix = '../';
	include('../includes/ConnectDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
	$Con = DB_connect($host, $DBUser, $DBPassword, $DBPort);
	if (!$Con) {
		echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';
		echo '<div class="error">' . _('Failed to connect to the database. Please correct the following error:') . '<br/>' . mysqli_connect_error() . '<br/> ' . ('This error is usually caused by entry of an incorrect database password or user name.') . '</div>';
		DbConfig($UserLanguage);

	} else {
		unset($_SESSION['DatabaseName']);
		CompanySetup($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName);
	}

}
//@para $UsersLanguage the language select by the user it will be used as the default langauge in config.php
//@para $host is the host for mysql server
//@para $DBUser is the name of mysql user
//@para $DBPassword is the password for mysql server
//@para $DatabaseName is the name of the database of KwaMoja and also the same name of company
//@para $MysqlEx is refer to the php mysql extention if it's false, it means the php configuration only support mysql instead of mysqli
//The purpose of this function is to display the final screen for users to input company, admin user accounts etc informatioin
function CompanySetup($UserLanguage, $host, $DBUser, $DBPassword, $DatabaseName) { //display the company setup for users

	echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';
	echo '<form id="companyset" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';


	echo '</form>
		</div>';
}
//@para $NewSQL is the kwamoja new sql file which contains the COA file
//@para $Demo is the kwamoja demo sql file
//@para $db refer to the database connection reference
//@para $DBType refer to the database connection type mysqli or mysql
//@para $NewDB is the new database name
//The purpose of this function is populate database with data from the sql file by mysqli
function PopulateSQLData($NewSQL = false, $Demo = false, $db, $DBType, $NewDB = false) {
	ob_implicit_flush();
	echo '<legend>' . _('Building your database.') . ' ' . _('This may take some time, please be patient') . '</legend>';
	echo '<div id="progress" class="centre" style="border-radius: 15px;width:95%;border:1px solid #373F67;"></div>';
	echo '<div id="information" style="width"></div>';
	if ($NewSQL) {
		$PathPrefix = '../';
		$StartingUpdate = 0;
		$EndingUpdate = HighestFileName('../sql/updates/');
		unset($_SESSION['Updates']);
		$_SESSION['Updates']['Errors'] = 0;
		$_SESSION['Updates']['Successes'] = 0;
		$_SESSION['Updates']['Warnings'] = 0;
		for ($UpdateNumber = $StartingUpdate; $UpdateNumber <= $EndingUpdate; $UpdateNumber++) {
			if (file_exists('../sql/updates/' . $UpdateNumber . '.php')) {
				$percent = intval($UpdateNumber/$EndingUpdate * 100)."%";
				echo '<script language="javascript">
						document.getElementById("progress").innerHTML="<div style=\"margin: 1px;border-radius: 15px; width:'.$percent.';background-color:#e9ffcf;\">&nbsp;</div>";
						document.getElementById("information").innerHTML="'.$UpdateNumber.' row(s) processed.";
					</script>';
				echo str_repeat(' ',1024*64);
				$sql = "SET foreign_key_checks=0";
				$result = executeSQL($sql, $db, False);
				if ($result == 0) {
					include('../sql/updates/' . $UpdateNumber . '.php');
				}
				ob_flush();
				sleep(1);
			}
		}
		if ($_SESSION['Updates']['Errors'] > 0) {
			echo 'Number of Errors ' . $_SESSION['Updates']['Errors'];
			foreach ($_SESSION['Updates']['Messages'] as $Message) {
				echo '<div class="error">' . $Message . '</div>';
			}
			exit;
		}
		/* Now we uploade the chosen chart of accounts */
		$sql = "SET foreign_key_checks=0";
		$result = executeSQL($sql, $db, False);
		include('coa/' . $_SESSION['Installer']['COA']);
		echo '<div class="success">' . _('Your chosen chart of accounts has been uploaded') . '</div>';
		ob_flush();
		/* Create the admin user */
		InsertRecord('www_users',
					array('userid'),
					array('admin'),
					array('userid' ,'password' ,'realname' ,'email', 'displayrecordsmax' ,'fullaccess' ,'cancreatetender' ,'modulesallowed' ,'blocked' ,'theme' ,'language' ,'pdflanguage' ,'fontsize'),
					array($_SESSION['Installer']['AdminAccount'],
						sha1($_SESSION['Installer']['KwaMojaPassword']),
						$_SESSION['Installer']['AdminAccount'],
						$_SESSION['Installer']['Email'],
						50,
						8,
						1,
						'1,1,1,1,1,1,1,1,1,1,1,',
						0,
						'aguapop',
						$_SESSION['Installer']['Language'],
						0,
						0),
						$db);
	}
	if ($Demo) {

		if ($DBType == 'mysqli') {
			mysqli_select_db($db, $NewDB);
		} else {
			mysql_select_db($NewDB, $db);
		}
		PopulateSQLDataBySQL($Demo, $db, $DBType, false, $NewDB);
		//we can let users wait instead of changing the my.cnf file
		//It is a non affordable challenge for them since wamp set the max_allowed_packet 1M
		//and kwamojademo.sql is 1.4M so at least it cannot install in wamp
		//so we not use the multi query here


		/*	$SQLFile = fopen($Demo);

		$sql = file_get_contents($Demo);
		if(!$sql){
		die(_('Failed to open the demo sql file'));
		}

		$result = mysqli_multi_query($db,$sql);

		if(!$result){
		echo '<div class="error">' . _('Failed to populate the database'.' '.$NewDB.' and the error is').' '.mysqli_error($db) . '</div>';
		}
		//clear the bufferred result
		do {
		if($result = mysqli_store_result($db)){
		mysqli_free_result($result);
		}
		} while (mysqli_more_results($db)?mysqli_next_result($db):false); */


		/*	}else{
		mysqli_select_db($db,$NewDB);
		PopulateSQLDataBySQL($Demo,$db,$DBType,false,$NewDB);
		}*/
	}





}
//@para $File is the sql file name
//@para $db is the DB connect reference
//@para $DBType refer to mysqli or mysql connection
//@para $NewDB is the new database name
//@para $DemoDB is the demo database name
//The purpose of this function is populate the database with mysql extention
function PopulateSQLDataBySQL($File, $db, $DBType, $NewDB = false, $DemoDB = 'kwamojademo') {
	$dbName = ($NewDB) ? $NewDB : $DemoDB;
	($DBType == 'mysqli') ? mysqli_select_db($db, $dbName) : mysql_select_db($dbName, $db);
	$SQLScriptFile = file($File);
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$SQL = '';
	$InAFunction = false;
	for ($i = 0; $i < $ScriptFileEntries; $i++) {

		$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
		//ignore lines that start with -- or USE or /*
		if (mb_substr($SQLScriptFile[$i], 0, 2) != '--' AND mb_strstr($SQLScriptFile[$i], '/*') == FALSE AND mb_strlen($SQLScriptFile[$i]) > 1) {

			$SQL .= ' ' . $SQLScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i], 0, 15) == 'CREATE FUNCTION') {
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i], 0, 8) == 'LANGUAGE') {
				$InAFunction = false;
			}
			if (mb_strpos($SQLScriptFile[$i], ';') > 0 AND !$InAFunction) {
				// Database created above with correct name.
				if (strncasecmp($SQL, ' CREATE DATABASE ', 17) AND strncasecmp($SQL, ' USE ', 5)) {
					$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1);

					$result = ($DBType == 'mysqli') ? mysqli_query($db, $SQL) : mysql_query($SQL, $db);
				}
				$SQL = '';
			}

		} //end if its a valid sql line not a comment
	} //end of for loop around the lines of the sql script
}

//@para $db the database connection
//@para $DatabaseName the database to update
//@para $_SESSION['Installer']['DBMS'] if it is mysql extention or not
//@para $AdminPasswd the kwamoja administrator's password
//@para $AdminEmail the kwamoja administrators' email
//@para $AdminLangauge the administrator's language for login
//@para $CompanyName the company
//The purpose of this function is to update the admin accounts and company name information

function DBUpdate($db, $DatabaseName, $DBConnectType, $AdminPasswd, $AdminEmail, $AdminLanguage, $CompanyName) {
	$MysqlExt = ($DBConnectType == 'mysql') ? true : false;
	//select the database to connect
	$Result = (!$MysqlExt) ? mysqli_select_db($db, $DatabaseName) : mysql_select_db($DatabaseName, $db);

	$sql = "UPDATE www_users
				SET password = '" . sha1($AdminPasswd) . "',
					email = '" . $AdminEmail . "',
					language = '" . $AdminLanguage . "'
				WHERE userid = 'admin'";
	$Result = (!$MysqlExt) ? mysqli_query($db, $sql) : mysql_query($sql, $db);
	if (!$Result) {

		echo '<div class="error">' . _('Failed to update the email address and password of the administrator and the error is') . ((!$MysqlExt) ? mysqli_error($db) : mysql_error($db)) . '</div>';
	}

	$sql = "UPDATE companies
			SET coyname = '" . ((!$MysqlExt) ? mysqli_real_escape_string($db, $CompanyName) : mysql_real_escape_string($CompanyName, $db)) . "'
			WHERE coycode = 1";
	$Result = (!$MysqlExt) ? mysqli_query($db, $sql) : mysql_query($sql, $db);
	if (!$Result) {
		echo '<div class="error">' . _('Failed to update the company name and the erroris') . ((!$MysqlExt) ? mysqli_error($db) : mysql_error($db)) . '</div>';
	}


}

function HighestFileName($PathPrefix) {
	if ($handle = opendir('../sql/updates')) {
		$i = 0;
		// Go through directory:
		while (false !== ($file = readdir($handle))) {
			// filter unnecessary file/dir paths:
			if (substr($file, -3) == 'php') {
				$FileNameLength = strlen($file) - 4;
				$UpdateNames[$i] = substr($file, 0, $FileNameLength);
				$i++;
			}
		}
		closedir($handle);
	}
	return max($UpdateNames);
}

echo '</body>
	</html>';

?>