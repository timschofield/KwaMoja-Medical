<?php

function GetPrice($StockId, $DebtorNo, $BranchCode, $OrderLineQty, $ReportZeroPrice = 1) {

	$Price = 0;
	/*Search by branch and customer for a date specified price */
	$SQL = "SELECT prices.price
			FROM prices
			INNER JOIN debtorsmaster
				ON debtorsmaster.salestype=prices.typeabbrev
				AND prices.currabrev = debtorsmaster.currcode
				AND prices.debtorno=debtorsmaster.debtorno
			WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
				AND prices.stockid = '" . $StockId . "'
				AND prices.branchcode='" . $BranchCode . "'
				AND prices.startdate<=CURRENT_DATE
				AND prices.enddate>=CURRENT_DATE";

		$ErrMsg = _('There is a problem in retrieving the pricing information for part') . ' ' . $StockId . ' ' . _('and for Customer') . ' ' . $DebtorNo . ' ' . _('the error message returned by the SQL server was');
		$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		/*Need to try same specific search but for a default price with a zero end date */
		$SQL = "SELECT prices.price,
					prices.startdate
				FROM prices
				INNER JOIN debtorsmaster
					ON debtorsmaster.salestype=prices.typeabbrev
					AND prices.currabrev = debtorsmaster.currcode
					AND prices.debtorno=debtorsmaster.debtorno
				WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
					AND prices.stockid = '" . $StockId . "'
					AND prices.branchcode='" . $BranchCode . "'
					AND prices.startdate <=CURRENT_DATE
					AND prices.enddate ='0000-00-00'
				ORDER BY prices.startdate DESC";

		$Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Result) == 0) {

			/* No result returned for customer and branch search try for just a customer match */
			$SQL = "SELECT prices.price
					FROM prices
					INNER JOIN debtorsmaster
						ON debtorsmaster.salestype=prices.typeabbrev
						AND prices.currabrev = debtorsmaster.currcode
						AND prices.debtorno=debtorsmaster.debtorno
					WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
						AND prices.stockid = '" . $StockId . "'
						AND prices.branchcode=''
						AND prices.startdate <=CURRENT_DATE
						AND prices.enddate >=CURRENT_DATE";


			$Result = DB_query($SQL, $ErrMsg);
			if (DB_num_rows($Result) == 0) {
				//if no specific price between the dates maybe there is a default price with no end date specified
				$SQL = "SELECT prices.price,
							   prices.startdate
						FROM prices
						INNER JOIN debtorsmaster
							ON debtorsmaster.salestype=prices.typeabbrev
							AND prices.currabrev = debtorsmaster.currcode
							AND prices.debtorno=debtorsmaster.debtorno
						WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
							AND prices.stockid = '" . $StockId . "'
							AND prices.branchcode=''
							AND prices.startdate <=CURRENT_DATE
							AND prices.enddate >='0000-00-00'
						ORDER BY prices.startdate DESC";

				$Result = DB_query($SQL, $ErrMsg);

				if (DB_num_rows($Result) == 0) {

					/*No special customer specific pricing use the customers normal price list but look for special limited time prices with specific end date*/
					$SQL = "SELECT prices.price
							FROM prices
							INNER JOIN debtorsmaster
								ON debtorsmaster.salestype=prices.typeabbrev
								AND prices.currabrev = debtorsmaster.currcode
							WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
								AND prices.stockid = '" . $StockId . "'
								AND prices.debtorno=''
								AND prices.startdate <=CURRENT_DATE
								AND prices.enddate >=CURRENT_DATE";

					$Result = DB_query($SQL, $ErrMsg);

					if (DB_num_rows($Result) == 0) {
						/*No special customer specific pricing use the customers normal price list but look for default price with 0000-00-00 end date*/
						$SQL = "SELECT prices.price,
									   prices.startdate
								FROM prices
								INNER JOIN debtorsmaster
									ON debtorsmaster.salestype=prices.typeabbrev
									AND prices.currabrev = debtorsmaster.currcode
								WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
									AND prices.stockid = '" . $StockId . "'
									AND prices.debtorno=''
									AND prices.startdate <=CURRENT_DATE
									AND prices.enddate ='0000-00-00'
								ORDER BY prices.startdate DESC";

						$Result = DB_query($SQL, $ErrMsg);

						if (DB_num_rows($Result) == 0) {

							/* Now use the default salestype/price list cos all else has failed */
							$SQL = "SELECT prices.price
									FROM prices
									INNER JOIN debtorsmaster
										ON prices.currabrev = debtorsmaster.currcode
									WHERE prices.stockid = '" . $StockId . "'
										AND debtorsmaster.debtorno='" . $DebtorNo . "'
										AND prices.typeabbrev='" . $_SESSION['DefaultPriceList'] . "'
										AND prices.debtorno=''
										AND prices.startdate <=CURRENT_DATE
										AND prices.enddate >=CURRENT_DATE";

							$Result = DB_query($SQL, $ErrMsg);

							if (DB_num_rows($Result) == 0) {

								/* Now use the default salestype/price list cos all else has failed */
								$SQL = "SELECT prices.price,
											 prices.startdate
										FROM prices
										INNER JOIN debtorsmaster
											ON prices.currabrev = debtorsmaster.currcode
										WHERE prices.stockid = '" . $StockId . "'
											AND debtorsmaster.debtorno='" . $DebtorNo . "'
											AND prices.typeabbrev='" . $_SESSION['DefaultPriceList'] . "'
											AND prices.debtorno=''
											AND prices.startdate <=CURRENT_DATE
											AND prices.enddate ='0000-00-00'
										ORDER BY prices.startdate DESC";

								$Result = DB_query($SQL, $ErrMsg);

								if (DB_num_rows($Result) == 0) {
									/* Now check the price matrix */
									$SQL = "SELECT max(pricematrix.price)
											FROM pricematrix
											INNER JOIN debtorsmaster
												ON pricematrix.currabrev = debtorsmaster.currcode
												AND pricematrix.salestype = debtorsmaster.salestype
											WHERE pricematrix.stockid = '" . $StockId . "'
												AND pricematrix.quantitybreak >= '" . $OrderLineQty . "'
												AND pricematrix.startdate <=CURRENT_DATE
												AND pricematrix.enddate >=CURRENT_DATE";
									$ErrMsg = _('There is an error to retrieving the price from the price matrix for stock '.$StockId.' and the error message returned by SQL server is ');
									$Result = DB_query($SQL, $ErrMsg);
									$MaxPriceRow = DB_fetch_row($Result);
								}
								if ($MaxPriceRow[0] == NULL) {
									/*Not even a price set up in the default price list so return 0 */
									if ($ReportZeroPrice == 1) {
										prnMsg(_('There are no prices set up for') . ' ' . $StockId, 'warn');
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

	if (DB_num_rows($Result) != 0) {
		/*There is a price from one of the above so return that */
		$MyRow = DB_fetch_row($Result);
		return $MyRow[0];
	} else {
		return 0;
	}

}
?>