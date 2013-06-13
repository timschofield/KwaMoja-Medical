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

NewMenuItem('AR', 'Reports', _('Where Allocated Inquiry'), '/CustWhereAlloc.php', 1, $db);
NewMenuItem('AR', 'Reports', _('Print Invoices or Credit Notes'), '/PrintCustTrans.php', 2, $db);
NewMenuItem('AR', 'Reports', _('Print Statements'), '/PrintCustStatements.php', 3, $db);
NewMenuItem('AR', 'Reports', _('Sales Analysis Reports'), '/SalesAnalRepts.php', 4, $db);
NewMenuItem('AR', 'Reports', _('Aged Customer Balances/Overdues Report'), '/AgedDebtors.php', 5, $db);
NewMenuItem('AR', 'Reports', _('Re-Print A Deposit Listing'), '/PDFBankingSummary.php', 6, $db);
NewMenuItem('AR', 'Reports', _('Debtor Balances At A Prior Month End'), '/DebtorsAtPeriodEnd.php', 7, $db);
NewMenuItem('AR', 'Reports', _('Customer Listing By Area/Salesperson'), '/PDFCustomerList.php', 8, $db);
NewMenuItem('AR', 'Reports', _('Sales Graphs'), '/SalesGraph.php', 9, $db);
NewMenuItem('AR', 'Reports', _('List Daily Transactions'), '/PDFCustTransListing.php', 10, $db);
NewMenuItem('AR', 'Reports', _('Customer Transaction Inquiries'), '/CustomerTransInquiry.php', 11, $db);

NewMenuItem('AR', 'Maintenance', _('Add Customer'), '/Customers.php', 1, $db);
NewMenuItem('AR', 'Maintenance', _('Select Customer'), '/SelectCustomer.php', 2, $db);

NewMenuItem('AP', 'Transactions', _('Select Supplier'), '/SelectSupplier.php', 1, $db);
NewMenuItem('AP', 'Transactions', _('Supplier Allocations'), '/SupplierAllocations.php', 2, $db);

NewMenuItem('AP', 'Reports', _('Aged Supplier Report'), '/AgedSuppliers.php', 1, $db);
NewMenuItem('AP', 'Reports', _('Payment Run Report'), '/SuppPaymentRun.php', 2, $db);
NewMenuItem('AP', 'Reports', _('Remittance Advices'), '/PDFRemittanceAdvice.php', 3, $db);
NewMenuItem('AP', 'Reports', _('Outstanding GRNs Report'), '/OutstandingGRNs.php', 4, $db);
NewMenuItem('AP', 'Reports', _('Supplier Balances At A Prior Month End'), '/SupplierBalsAtPeriodEnd.php', 5, $db);
NewMenuItem('AP', 'Reports', _('List Daily Transactions'), '/PDFSuppTransListing.php', 6, $db);
NewMenuItem('AP', 'Reports', _('Supplier Transaction Inquiries'), '/SupplierTransInquiry.php', 7, $db);

NewMenuItem('AP', 'Maintenance', _('Add Supplier'), '/Suppliers.php', 1, $db);
NewMenuItem('AP', 'Maintenance', _('Select Supplier'), '/SelectSupplier.php', 2, $db);
NewMenuItem('AP', 'Maintenance', _('Maintain Factor Companies'), '/Factors.php', 3, $db);

NewMenuItem('PO', 'Transactions', _('Purchase Orders'), '/PO_SelectOSPurchOrder.php', 1, $db);
NewMenuItem('PO', 'Transactions', _('Add Purchase Order'), '/PO_Header.php?NewOrder=Yes', 2, $db);
NewMenuItem('PO', 'Transactions', _('Create a New Tender'), '/SupplierTenderCreate.php?New=Yes', 3, $db);
NewMenuItem('PO', 'Transactions', _('Edit Existing Tenders'), '/SupplierTenderCreate.php?Edit=Yes', 4, $db);
NewMenuItem('PO', 'Transactions', _('Process Tenders and Offers'), '/OffersReceived.php', 5, $db);
NewMenuItem('PO', 'Transactions', _('Orders to Authorise'), '/PO_AuthoriseMyOrders.php', 6, $db);
NewMenuItem('PO', 'Transactions', _('Shipment Entry'), '/SelectSupplier.php', 7, $db);
NewMenuItem('PO', 'Transactions', _('Select A Shipment'), '/Shipt_Select.php', 8, $db);

NewMenuItem('PO', 'Reports', _('Purchase Order Inquiry'), '/PO_SelectPurchOrder.php', 1, $db);
NewMenuItem('PO', 'Reports', _('Purchase Order Detail Or Summary Inquiries'), '/POReport.php', 2, $db);
NewMenuItem('PO', 'Reports', _('Supplier Price List'), '/SuppPriceList.php', 3, $db);

NewMenuItem('PO', 'Maintenance', _('Maintain Supplier Price Lists'), '/SupplierPriceList.php', 1, $db);
NewMenuItem('PO', 'Maintenance', _('Clear Orders with Quantity on Back Orders'), '/POClearBackOrders.php', 2, $db);

NewMenuItem('stock', 'Transactions', _('Receive Purchase Orders'), '/PO_SelectOSPurchOrder.php', 1, $db);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Dispatch'), '/StockLocTransfer.php', 2, $db);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Receive'), '/StockLocTransferReceive.php', 3, $db);
NewMenuItem('stock', 'Transactions', _('Inventory Location Transfers'), '/StockTransfers.php?New=Yes', 4, $db);
NewMenuItem('stock', 'Transactions', _('Inventory Adjustments'), '/StockAdjustments.php?NewAdjustment=Yes', 5, $db);
NewMenuItem('stock', 'Transactions', _('Reverse Goods Received'), '/ReverseGRN.php', 6, $db);
NewMenuItem('stock', 'Transactions', _('Enter Stock Counts'), '/StockCounts.php', 7, $db);
NewMenuItem('stock', 'Transactions', _('Create a New Internal Stock Request'), '/InternalStockRequest.php?New=Yes', 8, $db);
NewMenuItem('stock', 'Transactions', _('Authorise Internal Stock Requests'), '/InternalStockRequestAuthorisation.php', 9, $db);
NewMenuItem('stock', 'Transactions', _('Fulfil Internal Stock Requests'), '/InternalStockRequestFulfill.php', 10, $db);







UpdateDBNo(basename(__FILE__, '.php'), $db);

?>