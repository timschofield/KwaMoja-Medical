<?php

/* Input Serial Items - used for inputing serial numbers or batch/roll/bundle
 * referencesfor controlled items - used in:
 * - ConfirmDispatchControlledInvoice.php
 * - GoodsReceivedControlled.php
 * - StockAdjustments.php
 * - StockTransfers.php
 * - CreditItemsControlled.php
 */

/* If the User has selected Keyed Entry, show them this special select list...
 * it is just in the way if they are doing file imports it also would not
 * be applicable in a PO and possible other situations...
 */

if ($_POST['EntryType'] == 'KEYED'){
        /*Also a multi select box for adding bundles to the dispatch without keying */
     $SQL = "SELECT serialno, quantity
			FROM stockserialitems
			WHERE stockid='" . $StockID . "'
			AND loccode ='" . $LocationOut."'
			AND quantity > 0";

	$ErrMsg = '<br />'. _('Could not retrieve the items for'). ' ' . $StockID;
    $Bundles = DB_query($SQL, $ErrMsg );
	echo '<table class="selection"><tr>';
	if (DB_num_rows($Bundles)>0){
		$AllSerials=array();

		foreach ($LineItem->SerialItems as $Itm){
			$AllSerials[$Itm->BundleRef] = $Itm->BundleQty;
		}

		echo '<td valign="top"><b>'. _('Select Existing Items'). '</b><br />';

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier=' . $identifier . '" method="post" class="noPrint">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="LineNo" value="' . $LineNo . '">
			<input type="hidden" name="StockID" value="' . $StockID . '">
			<input type="hidden" name="EntryType" value="KEYED">
			<input type="hidden" name="identifier" value="' . $identifier . '">
			<input type="hidden" name="EditControlled" value="true">
			<select name=Bundles[] multiple="multiple">';

		$id=0;
		$ItemsAvailable=0;
		while ($MyRow=DB_fetch_array($Bundles)){
			if ($LineItem->Serialised==1){
				if ( !array_key_exists($MyRow['serialno'], $AllSerials) ){
					echo '<option value="' . $MyRow['serialno'] . '">' . $MyRow['serialno'].'</option>';
					$ItemsAvailable++;
				}
			} else {
				if ( !array_key_exists($MyRow['serialno'], $AllSerials)  or
					($MyRow['quantity'] - $AllSerials[$MyRow['serialno']] >= 0) ) {
					//Use the $InOutModifier to ajust the negative or postive direction of the quantity. Otherwise the calculated quantity is wrong.
					if (isset($AllSerials[$MyRow['serialno']])) {
						$RecvQty = $MyRow['quantity'] - $InOutModifier*$AllSerials[$MyRow['serialno']];
					} else {
						$RecvQty = $MyRow['quantity'];
					}
					echo '<option value="' . $MyRow['serialno'] . '/|/'. $RecvQty .'">' . $MyRow['serialno'].' - ' . _('Qty left'). ': ' . $RecvQty . '</option>';
					$ItemsAvailable += $RecvQty;
				}
			}
		}
		echo '</select>
			<br />';
		echo '<br /><div class="centre"><input type="submit" name="AddBatches" value="'. _('Enter'). '"></div>
			<br />';
		echo '</form>';
		echo $ItemsAvailable . ' ' . _('items available');
		echo '</td>';
	} else {
		echo '<td>'. prnMsg( _('There does not appear to be any of') . ' ' . $StockID . ' ' . _('left in'). ' '. $LocationOut , 'warn') . '</td>';
	}
	echo '</tr></table>';
}