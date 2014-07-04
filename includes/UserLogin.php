<?php

/*  Performs login checks and $_SESSION initialisation */

define('UL_OK', 0);
/* User verified, session initialised */
define('UL_NOTVALID', 1);
/* User/password do not agree */
define('UL_BLOCKED', 2);
/* Account locked, too many failed logins */
define('UL_CONFIGERR', 3);
/* Configuration error in KwaMoja or server */
define('UL_SHOWLOGIN', 4);
define('UL_MAINTENANCE', 5);

/*	UserLogin
 *  Function to validate user name,  perform validity checks and initialise
 *  $_SESSION data.
 *  Returns:
 *	See define() statements above.
 */

function userLogin($Name, $Password, $SysAdminEmail = '') {

	global $debug;

	if (!isset($_SESSION['AccessLevel']) or $_SESSION['AccessLevel'] == '' or (isset($Name) and $Name != '')) {
		/* if not logged in */
		$_SESSION['AccessLevel'] = '';
		$_SESSION['CustomerID'] = '';
		$_SESSION['UserBranch'] = '';
		$_SESSION['SalesmanLogin'] = '';
		$_SESSION['Module'] = '';
		$_SESSION['PageSize'] = '';
		$_SESSION['UserStockLocation'] = '';
		$_SESSION['AttemptsCounter']++;
		// Show login screen
		if (!isset($Name) or $Name == '') {
			return UL_SHOWLOGIN;
		}
		$SQL = "SELECT *
				FROM www_users
				WHERE www_users.userid='" . $Name . "'
				AND (www_users.password='" . CryptPass($Password) . "'
				OR  www_users.password='" . $Password . "')";
		$ErrMsg = _('Could not retrieve user details on login because');
		$debug = 1;
		$Auth_Result = DB_query($SQL, $ErrMsg);
		// Populate session variables with data base results
		if (DB_num_rows($Auth_Result) > 0) {
			$MyRow = DB_fetch_array($Auth_Result);
			if ($MyRow['blocked'] == 1) {
				//the account is blocked
				return UL_BLOCKED;
			}
			/*reset the attempts counter on successful login */
			$_SESSION['UserID'] = $MyRow['userid'];
			$_SESSION['AttemptsCounter'] = 0;
			$_SESSION['AccessLevel'] = $MyRow['fullaccess'];
			$_SESSION['CustomerID'] = $MyRow['customerid'];
			$_SESSION['UserBranch'] = $MyRow['branchcode'];
			$_SESSION['DefaultPageSize'] = $MyRow['pagesize'];
			$_SESSION['UserStockLocation'] = $MyRow['defaultlocation'];
			$_SESSION['RestrictLocations'] = $MyRow['restrictlocations'];
			$_SESSION['UserEmail'] = $MyRow['email'];
			$_SESSION['ModulesEnabled'] = explode(",", $MyRow['modulesallowed']);
			$_SESSION['UsersRealName'] = $MyRow['realname'];
			$_SESSION['Theme'] = $MyRow['theme'];
			$_SESSION['Language'] = $MyRow['language'];
			$_SESSION['SalesmanLogin'] = $MyRow['salesman'];
			$_SESSION['CanCreateTender'] = $MyRow['cancreatetender'];
			$_SESSION['AllowedDepartment'] = $MyRow['department'];
			$_SESSION['ScreenFontSize'] = $MyRow['fontsize'];

			if (isset($MyRow['pdflanguage'])) {
				$_SESSION['PDFLanguage'] = $MyRow['pdflanguage'];
			} else {
				$_SESSION['PDFLanguage'] = '0'; //default to latin western languages
			}

			if ($MyRow['displayrecordsmax'] > 0) {
				$_SESSION['DisplayRecordsMax'] = $MyRow['displayrecordsmax'];
			} else {
				$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax']; // default comes from config.php
			}

			$SQL = "UPDATE www_users SET lastvisitdate='" . date('Y-m-d H:i:s') . "'
							WHERE www_users.userid='" . $Name . "'";
			$Auth_Result = DB_query($SQL);
			/*get the security tokens that the user has access to */
			$SQL = "SELECT tokenid
						FROM securitygroups
						WHERE secroleid =  '" . $_SESSION['AccessLevel'] . "'";
			$Sec_Result = DB_query($SQL);
			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($Sec_Result) == 0) {
				return UL_CONFIGERR;
			} else {
				$i = 0;
				$UserIsSysAdmin = FALSE;
				while ($MyRow = DB_fetch_row($Sec_Result)) {
					if ($MyRow[0] == 15) {
						$UserIsSysAdmin = TRUE;
					}
					$_SESSION['AllowedPageSecurityTokens'][$i] = $MyRow[0];
					$i++;
				}
			}
			// check if only maintenance users can access KwaMoja
			$SQL = "SELECT confvalue FROM config WHERE confname = 'DB_Maintenance'";
			$Maintenance_Result = DB_query($SQL);
			if (DB_num_rows($Maintenance_Result) == 0) {
				return UL_CONFIGERR;
			} else {
				$myMaintenanceRow = DB_fetch_row($Maintenance_Result);
				if (($myMaintenanceRow[0] == -1) AND ($UserIsSysAdmin == FALSE)) {
					// the configuration setting has been set to -1 ==> Allow SysAdmin Access Only
					// the user is NOT a SysAdmin
					return UL_MAINTENANCE;
				}
			}
		} else { // Incorrect password
			// 5 login attempts, show failed login screen
			if (!isset($_SESSION['AttemptsCounter'])) {
				$_SESSION['AttemptsCounter'] = 0;
			} elseif ($_SESSION['AttemptsCounter'] >= 5 and isset($Name)) {
				/*User blocked from future accesses until sysadmin releases */
				$SQL = "UPDATE www_users
							SET blocked=1
							WHERE www_users.userid='" . $Name . "'";
				$Auth_Result = DB_query($SQL);
				if ($SysAdminEmail != '') {
					$EmailSubject = _('User access blocked') . ' ' . $Name;
					$EmailText = _('User ID') . ' ' . $Name . ' - ' . $Password . ' - ' . _('has been blocked access at') . ' ' . Date('Y-m-d H:i:s') . ' ' . _('from IP') . ' ' . $_SERVER["REMOTE_ADDR"] . ' ' . _('due to too many failed attempts.');
					if ($_SESSION['SmtpSetting'] == 0) {
						mail($SysAdminEmail, $EmailSubject, $EmailText);

					} else {
						include('includes/htmlMimeMail.php');
						$mail = new htmlMimeMail();
						$mail->setSubject($EmailSubject);
						$mail->setText($EmailText);
						$Result = SendmailBySmtp($mail, array(
							$SysAdminEmail
						));
					}

				}
				return UL_BLOCKED;
			}

			return UL_NOTVALID;
		}
	} // End of userid/password check
	// Run with debugging messages for the system administrator(s) but not anyone else

	return UL_OK;
	/* All is well */
}

?>