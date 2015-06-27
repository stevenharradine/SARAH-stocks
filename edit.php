<?php
	require_once '../../views/_secureHead.php';
	require_once '../../libs/simple_html_dom.php';
	require_once '../../models/_edit.php';

	if( isset ($sessionManager) && $sessionManager->isAuthorized () ) {
		$STOCK_ID = request_isset ('id');

		$stockManager = new StockManager ();
		
		$record = $stockManager->getRecord ($STOCK_ID);

		$page_title = 'Edit | Stocks';

		// build edit view
		$editView = new EditView ('Edit', 'update_by_id', $STOCK_ID);
		$editView->addRow ('stock', 'Stock', $record->getStock () );
		$editView->addRow ('ticker', 'Ticker', $record->getSymbol () );

		$views_to_load = array();
		$views_to_load[] = '../../views/_edit.php';

		include '../../views/_generic.php';
	}
?>