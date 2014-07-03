<?php
$PageSecurity=1;
include('includes/session.inc');

$SQL = "SELECT caption
			FROM favourites
			WHERE userid='" . $_SESSION['UserID'] . "'
				AND href='" . $_GET['Script'] . "'";
$result = DB_query($SQL);

if (DB_num_rows($result) == 0) {
	$SQL = "INSERT INTO favourites ( userid,
									caption,
									href
								) VALUES (
									'" . $_SESSION['UserID'] . "',
									'" . $_GET['Title'] . "',
									'" . $_GET['Script'] . "'
								)";
	$result = DB_query($SQL);
} else {
	$SQL = "DELETE FROM favourites
					WHERE userid='" . $_SESSION['UserID'] . "'
						AND href='" . $_GET['Script'] . "'";
	$result = DB_query($SQL);
}

?>