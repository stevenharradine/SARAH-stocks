<?php
	require_once '../../views/_secureHead.php';
	require_once '../../libs/simple_html_dom.php';
	require_once '../../models/_header.php';
	require_once '../../models/_add.php';
	require_once '../../models/_table.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		$STOCK_ID = request_isset ('id');
		$ticker = request_isset ('ticker');

		$stockManager = new StockManager ();
		
		if ($sessionManager->getUserType() != 'ADMIN') {
			switch ($page_action) {
				case ('update_by_id') :
					$db_update_success = $stockManager->updateRecord ($STOCK_ID, $USER_ID, $ticker);
					break;
				case ('add_stock') :
					$db_add_success = $stockManager->addRecord ($USER_ID, $ticker);
					break;
				case ('delete_by_id') :
					$db_delete_success = $stockManager->deleteRecord ($STOCK_ID, $USER_ID);
					break;
			}
		}

		$stock_records = $stockManager->getAllRecords();

		// build header view
		$headerView = new HeaderView (( $sessionManager->getUserType() == 'ADMIN' ? 'Indexer | ' : '' ) . 'Stocks');
		$headerView->setLink ('<link rel="stylesheet" type="text/css" href="css/styles.css" />');
		if ($sessionManager->getUserType() == 'ADMIN') {
			$headerView->setMeta ('<meta http-equiv="refresh" content="1800;url=#" />');
		} else {
			$headerView->setAltMenu ('<a class="add" href="#">Add</a>');

			// build add view
			$addView = new AddView ('Add', 'add_stock');
			$addView->addRow ('ticker', 'Ticker');
		}

		// build table view
		if ($sessionManager->getUserType() == 'ADMIN') {
			$tableView = new TableView ( array ('Stock', 'Price', 'Change') );
		} else {
			$tableView = new TableView ( array ('Stock', 'Price', 'Change', '') );
		}

		foreach ($stock_records as $record) {
			if ($sessionManager->getUserType() == 'ADMIN') {
				$tableView->addRow ( array ( TableView::createCell ('stock', $record->getStock() ),
											 TableView::createCell ('price', $record->getPrice( true ) ),
											 TableView::createCell ('change', $record->getChangePoints( true ) . ' (' . $record->getChangePercent( true ) . ')' )
										   )
								   );
				StockManager::updateHistory($record->getStock(), $record->getTicker(), $record->getPrice(), $record->getChangePoints(), $record->getChangePercent(), $record->getUpdateTime() );
			} else {
				$tableView->addRow ( array ( TableView::createCell ('stock', $record->getStock() ),
											 TableView::createCell ('price', $record->getPrice( true ) ),
											 TableView::createCell ('change ' . ($record->getChangePoints() < 0 ? 'negitive' : 'positive'), $record->getChangePoints( true ) . ' (' . $record->getChangePercent( true ) . ')' ),
											 TableView::createEdit ($record->getTicker())
										   )
								   );
			}

			$updated = $record->getUpdateTime();
		}

		$views_to_load = array();
		$views_to_load[] = '../../views/_add.php';
		$views_to_load[] = '../../views/_table.php';
		$views_to_load[] = '_update.php';
		
		include '../../views/_generic.php';
	}