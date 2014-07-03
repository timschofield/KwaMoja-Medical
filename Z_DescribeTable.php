<?php

include('includes/session.inc');
$Title = _('Database table details');
include('includes/header.inc');

$sql = 'DESCRIBE ' . $_GET['table'];
$result = DB_query($sql);

echo '<table>
		<tr>
			<th>' . _('Field name') . '</th>
			<th>' . _('Field type') . '</th>
			<th>' . _('Can field be null') . '</th>
			<th>' . _('Default') . '</th>
		</tr>';
while ($MyRow = DB_fetch_row($result)) {
	echo '<tr>
			<td>' . $MyRow[0] . '</td>
			<td>' . $MyRow[1] . '</td>
			<td>' . $MyRow[2] . '</td>
			<td>' . $MyRow[4] . '</td><
		/tr>';
}
echo '</table>';
include('includes/footer.inc');

?>