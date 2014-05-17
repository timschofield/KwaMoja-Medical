<?php

function GetPrice($StockID, $DebtorNo, $BranchCode, $OrderLineQty, $ReportZeroPrice = 1) {

	$Price = 0;
	/*Search by branch and customer for a date specified price */
	$sql = "SELECT prices.price
			FROM prices
			INNER JOIN debtorsmaster
				ON debtorsmaster.salestype=prices.typeabbrev
				AND prices.currabrev = debtorsmaster.currcode
				AND prices.debtorno=debtorsmaster.debtorno
			WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
				AND prices.stockid = '" . $StockID . "'
				AND prices.branchcode='" . $BranchCode . "'
				AND prices.startdate<=CURRENT_DATE
				AND prices.enddate>=CURRENT_DATE";

		$ErrMsg = _('There is a problem in retrieving the pricing information for part') . ' ' . $StockID . ' ' . _('and for Customer') . ' ' . $DebtorNo . ' ' . _('the error message returned by the SQL server was');
		$result = DB_query($sql, $ErrMsg);
	if (DB_num_rows($result) == 0) {
		/*Need to try same specific search but for a default price with a zero end date */
		$sql = "SELECT prices.price,
					prices.startdate
				FROM prices
				INNER JOIN debtorsmaster
					ON debtorsmaster.salestype=prices.typeabbrev
					AND prices.currabrev = debtorsmaster.currcode
					AND prices.debtorno=debtorsmaster.debtorno
				WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
					AND prices.stockid = '" . $StockID . "'
					AND prices.branchcode='" . $BranchCode . "'
					AND prices.startdate <=CURRENT_DATE
					AND prices.enddate ='0000-00-00'
				ORDER BY prices.startdate DESC";

		$result = DB_query($sql, $ErrMsg);

		if (DB_num_rows($result) == 0) {

			/* No result returned for customer and branch search try for just a customer match */
			$sql = "SELECT prices.price
					FROM prices
					INNER JOIN debtorsmaster
						ON debtorsmaster.salestype=prices.typeabbrev
						AND prices.currabrev = debtorsmaster.currcode
						AND prices.debtorno=debtorsmaster.debtorno
					WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
						AND prices.stockid = '" . $StockID . "'
						AND prices.branchcode=''
						AND prices.startdate <=CURRENT_DATE
						AND prices.enddate >=CURRENT_DATE";


			$result = DB_query($sql, $ErrMsg);
			if (DB_num_rows($result) == 0) {
				//if no specific price between the dates maybe there is a default price with no end date specified
				$sql = "SELECT prices.price,
							   prices.startdate
						FROM prices
						INNER JOIN debtorsmaster
							ON debtorsmaster.salestype=prices.typeabbrev
							AND prices.currabrev = debtorsmaster.currcode
							AND prices.debtorno=debtorsmaster.debtorno
						WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
							AND prices.stockid = '" . $StockID . "'
							AND prices.branchcode=''
							AND prices.startdate <=CURRENT_DATE
							AND prices.enddate >='0000-00-00'
						ORDER BY prices.startdate DESC";

				$result = DB_query($sql, $ErrMsg);

				if (DB_num_rows($result) == 0) {

					/*No special customer specific pricing use the customers normal price list but look for special limited time prices with specific end date*/
					$sql = "SELECT prices.price
							FROM prices
							INNER JOIN debtorsmaster
								ON debtorsmaster.salestype=prices.typeabbrev
								AND prices.currabrev = debtorsmaster.currcode
							WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
								AND prices.stockid = '" . $StockID . "'
								AND prices.debtorno=''
								AND prices.startdate <=CURRENT_DATE
								AND prices.enddate >=CURRENT_DATE";

					$result = DB_query($sql, $ErrMsg);

					if (DB_num_rows($result) == 0) {
						/*No special customer specific pricing use the customers normal price list but look for default price with 0000-00-00 end date*/
						$sql = "SELECT prices.price,
									   prices.startdate
								FROM prices
								INNER JOIN debtorsmaster
									ON debtorsmaster.salestype=prices.typeabbrev
									AND prices.currabrev = debtorsmaster.currcode
								WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
									AND prices.stockid = '" . $StockID . "'
									AND prices.debtorno=''
									AND prices.startdate <=CURRENT_DATE
									AND prices.enddate ='0000-00-00'
								ORDER BY prices.startdate DESC";

						$result = DB_query($sql, $ErrMsg);

						if (DB_num_rows($result) == 0) {

							/* Now use the default salestype/price list cos all else has failed */
							$sql = "SELECT prices.price
									FROM prices
									INNER JOIN debtorsmaster
										ON prices.currabrev = debtorsmaster.currcode
									WHERE prices.stockid = '" . $StockID . "'
										AND debtorsmaster.debtorno='" . $DebtorNo . "'
										AND prices.typeabbrev='" . $_SESSION['DefaultPriceList'] . "'
										AND prices.debtorno=''
										AND prices.startdate <=CURRENT_DATE
										AND prices.enddate >=CURRENT_DATE";

							$result = DB_query($sql, $ErrMsg);

							if (DB_num_rows($result) == 0) {

								/* Now use the default salestype/price list cos all else has failed */
								$sql = "SELECT prices.price,
											 prices.startdate
										FROM prices
										INNER JOIN debtorsmaster
											AND prices.currabrev = debtorsmaster.currcode
										WHERE prices.stockid = '" . $StockID . "'
											AND debtorsmaster.debtorno='" . $DebtorNo . "'
											AND prices.typeabbrev='" . $_SESSION['DefaultPriceList'] . "'
											AND prices.debtorno=''
											AND prices.startdate <=CURRENT_DATE
											AND prices.enddate ='0000-00-00'
										ORDER BY prices.startdate DESC";

								$result = DB_query($sql, $ErrMsg);

								if (DB_num_rows($result) == 0) {
									/* Now check the price matrix */
									$sql = "SELECT max(pricematrix.price)
											FROM pricematrix
											INNER JOIN debtorsmaster
												ON pricematrix.currabrev = debtorsmaster.currcode
												AND pricematrix.salestype = debtorsmaster.salestype
											WHERE pricematrix.stockid = '" . $StockID . "'
												AND pricematrix.quantitybreak >= '" . $OrderLineQty . "'
												AND pricematrix.startdate <=CURRENT_DATE
												AND pricematrix.enddate >=CURRENT_DATE";
									$ErrMsg = _('There is an error to retrieving the price from the price matrix for stock '.$StockID.' and the error message returned by SQL server is ');
									$result = DB_query($sql, $ErrMsg);
									$MaxPriceRow = DB_fetch_row($result);
								}
								if ($MaxPriceRow[0] == NULL) {
									/*Not even a price set up in the default price list so return 0 */
									if ($ReportZeroPrice == 1) {
										prnMsg(_('There are no prices set up for') . ' ' . $StockID, 'warn');
									}
									return 0;
								}
							}
						}
					}
				}
			}
		}
	}

	if (DB_num_rows($result) != 0) {
		/*There is a price from one of the above so return that */
		$myrow = DB_fetch_row($result);
		return $myrow[0];
	} else {
		return 0;
	}

}
?>