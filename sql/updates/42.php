<?php

CreateTable('modules',
"CREATE TABLE `modules` (
	`secroleid` INT(11) NOT NULL DEFAULT 15,
	`modulelink` VARCHAR(10) NOT NULL DEFAULT '',
	`reportlink` VARCHAR(4) NOT NULL DEFAULT '',
	`modulename` VARCHAR(25) NOT NULL DEFAULT '',
	`sequence` INT(11) NOT NULL DEFAULT 1,
	PRIMARY KEY (`secroleid`, `modulelink`)
)",
$db);

CreateTable('menuitems',
"CREATE TABLE `menuitems` (
	`secroleid` int(11) NOT NULL DEFAULT 15,
	`modulelink` VARCHAR(10) NOT NULL DEFAULT '',
	`menusection` TINYINT NOT NULL DEFAULT 1,
	`caption` VARCHAR(60) NOT NULL DEFAULT '',
	`url` VARCHAR(30) NOT NULL DEFAULT '',
	`sequence` INT(11) NOT NULL DEFAULT 1,
	PRIMARY KEY (`secroleid`, `modulelink`, `menusection`, `caption`)
)",
$db);

NewModule('orders', 'ord', _('Sales'), 1, $db);
NewModule('AR', 'ar', _('Receivables'), 2, $db);
NewModule('AP', 'ap', _('Payables'), 3, $db);
NewModule('PO', 'prch', _('Purchases'), 4, $db);
NewModule('stock', 'inv', _('Inventory'), 5, $db);
NewModule('manuf', 'man', _('Manufacturing'), 6, $db);
NewModule('GL', 'gl', _('General Ledger'), 7, $db);
NewModule('FA', 'fa', _('Asset Manager'), 8, $db);
NewModule('PC', 'pc', _('Petty Cash'), 9, $db);
NewModule('system', 'sys', _('Setup'), 10, $db);
NewModule('Utilities', 'utils', _('Utilities'), 11, $db);

NewMenuItem('orders', 'Transactions', _('Enter An Order or Quotation'), '/SelectOrderItems.php?NewOrder=Yes', 1, $db);
NewMenuItem('orders', 'Transactions', _('Enter Counter Sales'), '/CounterSales.php', 2, $db);
NewMenuItem('orders', 'Transactions', _('Enter Counter Returns'), '/CounterReturns.php', 3, $db);
NewMenuItem('orders', 'Transactions', _('Print Picking Lists'), '/PDFPickingList.php', 4, $db);
NewMenuItem('orders', 'Transactions', _('Outstanding Sales Orders/Quotations'), '/SelectSalesOrder.php', 5, $db);
NewMenuItem('orders', 'Transactions', _('Special Order'), '/SpecialOrder.php', 6, $db);
NewMenuItem('orders', 'Transactions', _('Recurring Order Template'), '/SelectRecurringSalesOrder.php', 7, $db);
NewMenuItem('orders', 'Transactions', _('Process Recurring Orders'), '/RecurringSalesOrdersProcess.php', 8, $db);

NewMenuItem('orders', 'Reports', _('Order Inquiry'), '/SelectCompletedOrder.php', 1, $db);
NewMenuItem('orders', 'Reports', _('Print Price Lists'), '/PDFPriceList.php', 2, $db);
NewMenuItem('orders', 'Reports', _('Order Status Report'), '/PDFOrderStatus.php', 3, $db);
NewMenuItem('orders', 'Reports', _('Orders Invoiced Reports'), '/PDFOrdersInvoiced.php', 4, $db);
NewMenuItem('orders', 'Reports', _('Daily Sales Inquiry'), '/DailySalesInquiry.php', 5, $db);
NewMenuItem('orders', 'Reports', _('Sales By Sales Type Inquiry'), '/SalesByTypePeriodInquiry.php', 6, $db);
NewMenuItem('orders', 'Reports', _('Sales By Category Inquiry'), '/SalesCategoryPeriodInquiry.php', 7, $db);
NewMenuItem('orders', 'Reports', _('Top Sellers Inquiry'), '/SalesTopItemsInquiry.php', 8, $db);
NewMenuItem('orders', 'Reports', _('Order Delivery Differences Report'), '/PDFDeliveryDifferences.php', 9, $db);
NewMenuItem('orders', 'Reports', _('Delivery In Full On Time (DIFOT) Report'), '/PDFDIFOT.php', 10, $db);
NewMenuItem('orders', 'Reports', _('Sales Order Detail Or Summary Inquiries'), '/SalesInquiry.php', 11, $db);
NewMenuItem('orders', 'Reports', _('Top Sales Items Report'), '/TopItems.php', 12, $db);
NewMenuItem('orders', 'Reports', _('Worst Sales Items Report'), '/NoSalesItems.php', 13, $db);
NewMenuItem('orders', 'Reports', _('Sales With Low Gross Profit Report'), '/PDFLowGP.php', 14, $db);
NewMenuItem('orders', 'Reports', _('Sell Through Support Claims Report'), '/PDFSellThroughSupportClaim.php', 15, $db);

NewMenuItem('orders', 'Maintenance', _('Select Contract'), '/SelectContract.php', 1, $db);
NewMenuItem('orders', 'Maintenance', _('Create Contract'), '/Contracts.php', 2, $db);
NewMenuItem('orders', 'Maintenance', _('Sell Through Support Deals'), '/SellThroughSupport.php', 3, $db);

NewMenuItem('AR', 'Transactions', _('Select Order to Invoice'), '/SelectSalesOrder.php', 1, $db);
NewMenuItem('AR', 'Transactions', _('Create A Credit Note'), '/SelectCreditItems.php?NewCredit=Yes', 2, $db);
NewMenuItem('AR', 'Transactions', _('Enter Receipts'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer', 3, $db);
NewMenuItem('AR', 'Transactions', _('Allocate Receipts or Credit Notes'), '/CustomerAllocations.php', 4, $db);





$MenuItems['AR']['Reports']['Caption'] = array (_('Where Allocated Inquiry'),
												_('Print Invoices or Credit Notes'),
												_('Print Statements'),
												_('Sales Analysis Reports'),
												_('Aged Customer Balances/Overdues Report'),
												_('Re-Print A Deposit Listing'),
												_('Debtor Balances At A Prior Month End'),
												_('Customer Listing By Area/Salesperson'),
												_('Sales Graphs'),
												_('List Daily Transactions'),
												_('Customer Transaction Inquiries')	);


UpdateDBNo(basename(__FILE__, '.php'), $db);

?>