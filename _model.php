<?php
	class StockRecord {
		private $STOCK_ID;
		private $USER_ID;
		private $stock;
		private $ticker;
		private $price;
		private $change_points;
		private $change_percent;
		private $update_time;
		private $chart;

		public function __construct ($STOCK_ID, $ticker) {
			global $sessionManager;

			$this->STOCK_ID = $STOCK_ID;
			$this->USER_ID	= $sessionManager->getUserId();
			$this->ticker = $ticker;

			if ($sessionManager->getUserType() == 'ADMIN') {

				// download DOM from the data source
				$html = file_get_html ('http://web.tmxmoney.com/quote.php?qm_symbol=' . $ticker);

				// get stock name
				$temp = $html->find ("#mainContent .qmCompanyName", 0);
				$temp = trim ( explode ('Exchange:', ($temp != null) ? $temp->plaintext : $temp) [0] );
				$this->stock = trim ( explode ('Market:', $temp) [0] );

				// get stock price
				$temp = $html->find('#mainContent span', 0);
				$this->price = ($temp != null) ? $temp->plaintext : $temp;
				// remove , in formated prices
				$this->price = str_replace (',', '', $this->price);

				// get stocks change (dollar and percent)
				$temp = $html->find ('#mainContent .qm-quote-data .change', 0);
				$temp = str_replace('Change:', '', ($temp != null) ? $temp->plaintext : $temp);
				$this->change_points = trim (explode ('(', $temp)[0]);
				$this->change_percent = trim ( str_replace( ')', '', explode ( '(', $temp )[1] ) );

				// get the updated time from (this is from the data source site, not when loaded into local database)
				$temp = $html->find('#mainContent .qmDataTop .date', 0);
				$this->update_time = ($temp != null) ? $temp->plaintext : $temp;
			} else {	// USER account
				// possible security issue here, but the data is not sensitive, programatic validation that the current user has this stock in their portfolio should be done
				$sql = <<<EOD
	SELECT *
	FROM `stocks_history`
	WHERE `last_updated` IN (
		SELECT MAX(`last_updated`)
		FROM `stocks_history`
		WHERE `ticker` = '$ticker'
	) AND `ticker` = '$ticker';
EOD;

				$data = mysql_query($sql) or die(mysql_error());
				$row = mysql_fetch_array( $data ); // should only return one record

				$this->stock = $row['stock'];
				$this->price = $row['price'];
				$this->change_points = $row['change_points'];
				$this->change_percent = $row['change_percent'];
				$this->update_time = $row['date'];
			}
		}

		public function getStockId () {
			return $this->STOCK_ID;
		}
		public function getUserId () {
			return $this->USER_ID;
		}
		public function getStock () {
			return $this->stock;
		}
		public function getSymbol () {	// deprecated
			return $this->ticker;
		}
		public function getTicker () {
			return $this->ticker;
		}
		public function getPrice ( $formated=false ) {
			return $formated ? format_currency ( $this->price ) : $this->price;
		}
		public function getChangePoints ( $formated=false ) {
			return $formated ? format_currency ( $this->change_points ) : $this->change_points;
		}
		public function getChangePercent ( $formated=false ) {
			return $formated ? format_percent ( $this->change_percent ) : $this->change_percent;
		}
		public function getUpdateTime () {
			return $this->update_time;
		}
		public function getChart () {
			return $this->chart;
		}
	}

	class StockManager {
		public function getAllRecords () {
			global $sessionManager;

			$records = array ();
			if ($sessionManager->getUserType() == 'ADMIN') {
				$sql = <<<EOD
	SELECT DISTINCT upper(`ticker`) AS `ticker`
	FROM `stocks`;
EOD;
			} else {	// USER Account
				$USER_ID = $sessionManager->getUserId();
				$sql = <<<EOD
	SELECT `ticker`
	FROM `stocks`
	WHERE `USER_ID` = $USER_ID;
EOD;
			}

			$data = mysql_query( $sql ) or die (mysql_error () );

			while ( ( $row = mysql_fetch_array( $data ) ) != null) {
				$STOCK_ID = isset ($row['STOCK_ID']) ? $row['STOCK_ID'] : -1;
				$ticker = $row['ticker'];

				$records[] = new StockRecord ($STOCK_ID, $ticker);
			}

			return $records;
		}

		public function updateRecord ($STOCK_ID, $USER_ID, $ticker) {
			$sql = <<<EOD
	UPDATE `sarah`.`stocks`
	SET `ticker` = '$ticker'
	WHERE `ticker`='$STOCK_ID';
EOD;
			
			return mysql_query($sql) or die(mysql_error());
		}

		public function addRecord ($USER_ID, $ticker) {
			return mysql_query("INSERT INTO `sarah`.`stocks` (`USER_ID`, `ticker`) VALUES ('$USER_ID', '$ticker');") or die(mysql_error());
		}

		public function deleteRecord ($STOCK_ID, $USER_ID) {
			return mysql_query("DELETE FROM `sarah`.`stocks` WHERE `ticker`='$STOCK_ID' AND `USER_ID`='$USER_ID';") or die(mysql_error());
		}

		public function getRecord ($STOCK_ID) {
			global $sessionManager;
			$USER_ID = $sessionManager->getUserId();

			if ($sessionManager->getUserType() == 'ADMIN') {
				$sql = <<<EOD
	SELECT *
	FROM `stocks`
	WHERE `STOCK_ID`=$STOCK_ID
EOD;
			} else {
				$sql = <<<EOD
	SELECT *
	FROM `stocks_history`
	WHERE `ticker`='$STOCK_ID'
EOD;
			}

			$data = mysql_query($sql) or die(mysql_error());
			$row = mysql_fetch_array( $data );

			$ticker = $row['ticker'];

			return new StockRecord ($STOCK_ID, $ticker);
		}

		public function updateHistory ($stock, $ticker, $price, $change_points, $change_percent, $date) {
			global $sessionManager;

			if ($sessionManager->getUserType() == 'ADMIN') {
				$sql = <<<EOD
	INSERT INTO `sarah`.`stocks_history` (`stock`, `ticker`, `price`, `change_points`, `change_percent`, `date`)
	VALUES ('$stock', '$ticker', '$price', '$change_points', '$change_percent', '$date');
EOD;
				return mysql_query($sql) or die(mysql_error());
			} else {
				return null;
			}
		}
	}