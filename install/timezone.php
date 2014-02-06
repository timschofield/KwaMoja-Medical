<?php

$handle = fopen('timezone.csv', "r");
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	$num = count($data);
	for ($Row = 0; $Row < $num; $Row++) {
		$TimeZone = $data[$Row];
		if ($TimeZone == $_SESSION['Installer']['TimeZone']) {
			echo "<option selected='selected' value='" . $TimeZone . "'>" . $TimeZone . '</option>';
		} else {
			echo "<option value='" . $TimeZone . "'>" . $TimeZone . '</option>';
		}
	}
}
?>