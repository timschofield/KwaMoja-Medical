<?php

$sql = "SELECT `modulelink`,
				`reportlink` ,
				`modulename`
			FROM modules
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`";
$result = DB_query($sql);

while ($myrow = DB_fetch_array($result)) {
	$_SESSION['ModuleLink'][] = $myrow['modulelink'];
	$_SESSION['ReportList'][$myrow['modulelink']] = $myrow['reportlink'];
	$_SESSION['ModuleList'][] = _($myrow['modulename']);
}

$sql = "SELECT `modulelink`,
				`menusection` ,
				`caption` ,
				`url`
			FROM menuitems
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`, `menusection`";
$result = DB_query($sql);

while ($myrow = DB_fetch_array($result)) {
	$_SESSION['MenuItems'][$myrow['modulelink']][$myrow['menusection']]['Caption'][] = _($myrow['caption']);
	$_SESSION['MenuItems'][$myrow['modulelink']][$myrow['menusection']]['URL'][] = $myrow['url'];
}

include('includes/PluginMenuLinksArray.php');

?>