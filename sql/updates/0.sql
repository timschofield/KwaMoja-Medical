</php

CreateTable('accountgroups',
'CREATE TABLE accountgroups (
  groupname char(30) NOT NULL default '',
  sectioninaccounts smallint(6) NOT NULL default '0',
  pandl tinyint(4) NOT NULL default '1',
  sequenceintb smallint(6) NOT NULL default '0',
  PRIMARY KEY  (groupname),
  KEY sequenceintb (sequenceintb)
)',
$db);

CreateTable('areas',
'CREATE TABLE areas (
  areacode char(2) NOT NULL default '',
  areadescription varchar(25) NOT NULL default '',
  PRIMARY KEY  (areacode)
)',
$db);

CreateTable('bom',
'CREATE TABLE bom (
  parent char(20) NOT NULL default '',
  component char(20) NOT NULL default '',
  workcentreadded char(5) NOT NULL default '',
  loccode char(5) NOT NULL default '',
  effectiveafter date NOT NULL default '0000-00-00',
  effectiveto date NOT NULL default '9999-12-31',
  quantity double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (parent,component,workcentreadded,loccode),
  KEY component (component),
  KEY effectiveafter (effectiveafter),
  KEY effectiveto (effectiveto),
  KEY loccode (loccode),
  KEY parent (parent,effectiveafter,effectiveto,loccode),
  KEY parent_2 (parent),
  KEY workcentreadded (workcentreadded),
  CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('bankaccounts',
'CREATE TABLE bankaccounts (
  accountcode int(11) NOT NULL auto_increment,
  bankaccountname char(50) NOT NULL default '',
  bankaccountnumber char(50) NOT NULL default '',
  bankaddress char(50) default NULL,
  PRIMARY KEY  (accountcode),
  KEY bankaccountname (bankaccountname),
  KEY bankaccountnumber (bankaccountnumber),
  CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`)
)',
$db);

CreateTable('banktrans',
'CREATE TABLE banktrans (
  banktransid bigint(20) NOT NULL auto_increment,
  type smallint(6) NOT NULL default '0',
  transno bigint(20) NOT NULL default '0',
  bankact int(11) NOT NULL default '0',
  ref varchar(50) NOT NULL default '',
  amountcleared float NOT NULL default '0',
  exrate double NOT NULL default '1',
  transdate date NOT NULL default '0000-00-00',
  banktranstype varchar(30) NOT NULL default '',
  amount float NOT NULL default '0',
  currcode char(3) NOT NULL default '',
  PRIMARY KEY  (banktransid),
  KEY bankact (bankact,ref),
  KEY transdate (transdate),
  KEY transtype (banktranstype),
  KEY Type (type,transno),
  KEY currcode (currcode),
  CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`)
)',
$db);

CreateTable('buckets',
'CREATE TABLE buckets (
  workcentre char(5) NOT NULL default '',
  availdate datetime NOT NULL default '0000-00-00 00:00:00',
  capacity float(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (workcentre,availdate),
  KEY workcentre (workcentre),
  KEY availdate (availdate),
  CONSTRAINT `buckets_ibfk_1` FOREIGN KEY (`workcentre`) REFERENCES `workcentres` (`code`)
)',
$db);

CreateTable('cogsglpostings',
'CREATE TABLE cogsglpostings (
  id int(11) NOT NULL auto_increment,
  area char(2) NOT NULL default '',
  stkcat varchar(6) NOT NULL default '',
  glcode int(11) NOT NULL default '0',
  salestype char(2) NOT NULL default 'AN',
  PRIMARY KEY  (id),
  UNIQUE KEY area_stkcat (area,stkcat,salestype),
  KEY area (area),
  KEY stkcat (stkcat),
  KEY glcode (glcode),
  KEY salestype (salestype)
)',
$db);

CreateTable('chartdetails',
'CREATE TABLE chartdetails (
  accountcode int(11) NOT NULL default '0',
  period smallint(6) NOT NULL default '0',
  budget float NOT NULL default '0',
  actual float NOT NULL default '0',
  bfwd float NOT NULL default '0',
  bfwdbudget float NOT NULL default '0',
  PRIMARY KEY  (accountcode,period),
  KEY period (period),
  CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`periodno`),
  CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`)
)',
$db);

CreateTable('chartmaster',
'CREATE TABLE chartmaster (
  accountcode int(11) NOT NULL default '0',
  accountname char(50) NOT NULL default '',
  group_ char(30) NOT NULL default '',
  PRIMARY KEY  (accountcode),
  KEY accountcode (accountcode),
  KEY accountname (accountname),
  KEY group_ (group_),
  CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`)
)',
$db);

CreateTable('companies',
'CREATE TABLE companies (
  coycode int(11) NOT NULL default '1',
  coyname varchar(50) NOT NULL default '',
  gstno varchar(20) NOT NULL default '',
  companynumber varchar(20) NOT NULL default '0',
  postaladdress varchar(50) NOT NULL default '',
  regoffice1 varchar(50) NOT NULL default '',
  regoffice2 varchar(50) NOT NULL default '',
  regoffice3 varchar(50) NOT NULL default '',
  telephone varchar(25) NOT NULL default '',
  fax varchar(25) NOT NULL default '',
  email varchar(55) NOT NULL default '',
  currencydefault varchar(4) NOT NULL default '',
  debtorsact int(11) NOT NULL default '70000',
  pytdiscountact int(11) NOT NULL default '55000',
  creditorsact int(11) NOT NULL default '80000',
  payrollact int(11) NOT NULL default '84000',
  grnact int(11) NOT NULL default '72000',
  exchangediffact int(11) NOT NULL default '65000',
  purchasesexchangediffact int(11) NOT NULL default '0',
  retainedearnings int(11) NOT NULL default '90000',
  gllink_debtors tinyint(1) default '1',
  gllink_creditors tinyint(1) default '1',
  gllink_stock tinyint(1) default '1',
  freightact int(11) NOT NULL default '0',
  PRIMARY KEY  (coycode)
)',
$db);

CreateTable('contractbom',
'CREATE TABLE contractbom (
  contractref char(20) NOT NULL default '',
  component char(20) NOT NULL default '',
  workcentreadded char(5) NOT NULL default '',
  loccode char(5) NOT NULL default '',
  quantity double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (contractref,component,workcentreadded,loccode),
  KEY component (component),
  KEY loccode (loccode),
  KEY contractref (contractref),
  KEY workcentreadded (workcentreadded),
  KEY workcentreadded_2 (workcentreadded),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `contractbom_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('contractreqts',
'CREATE TABLE contractreqts (
  contractreqid int(11) NOT NULL auto_increment,
  contract char(20) NOT NULL default '',
  component char(40) NOT NULL default '',
  quantity double(16,4) NOT NULL default '1.0000',
  priceperunit decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (contractreqid),
  KEY Contract (contract),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contract`) REFERENCES `contracts` (`contractref`)
)',
$db);

CreateTable('contracts',
'CREATE TABLE contracts (
  contractref varchar(20) NOT NULL default '',
  contractdescription varchar(50) NOT NULL default '',
  debtorno varchar(10) NOT NULL default '',
  branchcode varchar(10) NOT NULL default '',
  status varchar(10) NOT NULL default 'Quotation',
  categoryid varchar(6) NOT NULL default '',
  typeabbrev char(2) NOT NULL default '',
  orderno int(11) NOT NULL default '0',
  quotedpricefx decimal(20,4) NOT NULL default '0.0000',
  margin double(16,4) NOT NULL default '1.0000',
  woref varchar(20) NOT NULL default '',
  requireddate datetime NOT NULL default '0000-00-00 00:00:00',
  canceldate datetime NOT NULL default '0000-00-00 00:00:00',
  quantityreqd double(16,4) NOT NULL default '1.0000',
  specifications longblob NOT NULL,
  datequoted datetime NOT NULL default '0000-00-00 00:00:00',
  units varchar(15) NOT NULL default 'Each',
  drawing longblob NOT NULL,
  rate double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (contractref),
  KEY orderno (orderno),
  KEY categoryid (categoryid),
  KEY status (status),
  KEY typeabbrev (typeabbrev),
  KEY woref (woref),
  KEY debtorno (debtorno,branchcode),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`typeabbrev`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`)
)',
$db);

CreateTable('currencies',
'CREATE TABLE Currencies (
  currency char(20) NOT NULL default '',
  currabrev char(3) NOT NULL default '',
  country char(50) NOT NULL default '',
  hundredsname char(15) NOT NULL default 'Cents',
  rate double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (currabrev),
  KEY country (country)
)',
$db);

CreateTable('custallocns',
'CREATE TABLE custallocns (
  id int(11) NOT NULL auto_increment,
  amt decimal(20,4) NOT NULL default '0.0000',
  datealloc date NOT NULL default '0000-00-00',
  transid_allocfrom int(11) NOT NULL default '0',
  transid_allocto int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY datealloc (datealloc),
  KEY transid_allocfrom (transid_allocfrom),
  KEY transid_allocto (transid_allocto),
  CONSTRAINT `custallocns_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `debtortrans` (`id`),
  CONSTRAINT `custallocns_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `debtortrans` (`id`)
)',
$db);

createTable('custbranch',
'CREATE TABLE custbranch (
  branchcode varchar(10) NOT NULL default '',
  debtorno varchar(10) NOT NULL default '',
  brname varchar(40) NOT NULL default '',
  braddress1 varchar(40) NOT NULL default '',
  braddress2 varchar(40) NOT NULL default '',
  braddress3 varchar(40) NOT NULL default '',
  braddress4 varchar(50) NOT NULL default '',
  estdeliverydays smallint(6) NOT NULL default '1',
  area char(2) NOT NULL default '',
  salesman varchar(4) NOT NULL default '',
  fwddate smallint(6) NOT NULL default '0',
  phoneno varchar(20) NOT NULL default '',
  faxno varchar(20) NOT NULL default '',
  contactname varchar(30) NOT NULL default '',
  email varchar(55) NOT NULL default '',
  defaultlocation varchar(5) NOT NULL default '',
  taxauthority tinyint(4) NOT NULL default '1',
  defaultshipvia int(11) NOT NULL default '1',
  disabletrans tinyint(4) NOT NULL default '0',
  brpostaddr1 varchar(40) NOT NULL default '',
  brpostaddr2 varchar(40) NOT NULL default '',
  brpostaddr3 varchar(30) NOT NULL default '',
  brpostaddr4 varchar(20) NOT NULL default '',
  custbranchcode varchar(30) NOT NULL default '',
  PRIMARY KEY  (branchcode,debtorno),
  KEY BranchCode (branchcode),
  KEY BrName (brname),
  KEY DebtorNo (debtorno),
  KEY Salesman (salesman),
  KEY Area (area),
  KEY Area_2 (area),
  KEY DefaultLocation (defaultlocation),
  KEY TaxAuthority (taxauthority),
  KEY DefaultShipVia (defaultshipvia),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`salesmancode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `custbranch_ibfk_5` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`)
)',
$db);

CreateTable('debtortrans',
'CREATE TABLE debtortrans (
  id int(11) NOT NULL auto_increment,
  transno int(11) NOT NULL default '0',
  type smallint(6) NOT NULL default '0',
  debtorno varchar(10) NOT NULL default '',
  branchcode varchar(10) NOT NULL default '',
  trandate datetime NOT NULL default '0000-00-00 00:00:00',
  prd smallint(6) NOT NULL default '0',
  settled tinyint(4) NOT NULL default '0',
  reference varchar(20) NOT NULL default '',
  tpe char(2) NOT NULL default '',
  order_ int(11) NOT NULL default '0',
  rate double(16,6) NOT NULL default '0.000000',
  ovamount float NOT NULL default '0',
  ovgst float NOT NULL default '0',
  ovfreight float NOT NULL default '0',
  ovdiscount float NOT NULL default '0',
  diffonexch float NOT NULL default '0',
  alloc float NOT NULL default '0',
  invtext text,
  shipvia varchar(10) NOT NULL default '',
  edisent tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY DebtorNo (debtorno,branchcode),
  KEY order_ (order_),
  KEY prd (prd),
  KEY tpe (tpe),
  KEY type (type),
  KEY settled (settled),
  KEY trandate (trandate),
  KEY transno (transno),
  KEY type_2 (type,transno),
  KEY edisent (edisent),
  CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`),
  CONSTRAINT `debtortrans_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `custbranch` (`debtorno`),
  CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`)
)',
$db);

CreateTable('debtorsmaster',
'CREATE TABLE debtorsmaster (
  debtorno varchar(10) NOT NULL default '',
  name varchar(40) NOT NULL default '',
  address1 varchar(40) NOT NULL default '',
  address2 varchar(40) NOT NULL default '',
  address3 varchar(40) NOT NULL default '',
  address4 varchar(50) NOT NULL default '',
  currcode char(3) NOT NULL default '',
  salestype char(2) NOT NULL default '',
  clientsince datetime NOT NULL default '0000-00-00 00:00:00',
  holdreason smallint(6) NOT NULL default '0',
  paymentterms char(2) NOT NULL default 'f',
  discount double(16,4) NOT NULL default '0.0000',
  pymtdiscount double(16,4) NOT NULL default '0.0000',
  lastpaid double(16,4) NOT NULL default '0.0000',
  lastpaiddate datetime default NULL,
  creditlimit float NOT NULL default '1000',
  invaddrbranch tinyint(4) NOT NULL default '0',
  discountcode char(2) NOT NULL default '',
  ediinvoices tinyint(4) NOT NULL default '0',
  ediorders tinyint(4) NOT NULL default '0',
  edireference varchar(20) NOT NULL default '',
  editransport varchar(5) NOT NULL default 'email',
  ediaddress varchar(50) NOT NULL default '',
  ediserverUser varchar(20) NOT NULL default '',
  ediserverPwd varchar(20) NOT NULL default '',
  PRIMARY KEY  (debtorno),
  KEY currency (currcode),
  KEY holdreason (holdreason),
  KEY name (name),
  KEY paymentterms (paymentterms),
  KEY salestype (salestype),
  KEY ediinvoices (ediinvoices),
  KEY ediorders (ediorders),
  CONSTRAINT `debtorsmaster_ibfk_4` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`),
  CONSTRAINT `debtorsmaster_ibfk_1` FOREIGN KEY (`holdreason`) REFERENCES `holdreasons` (`reasoncode`),
  CONSTRAINT `debtorsmaster_ibfk_2` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `debtorsmaster_ibfk_3` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`)
)',
$db);

CreateTable('discountmatrix',
'CREATE TABLE discountmatrix (
  salestype char(2) NOT NULL default '',
  discountcategory char(2) NOT NULL default '',
  quantitybreak int(11) NOT NULL default '1',
  discountrate double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (salestype,discountcategory,quantitybreak),
  KEY quantitybreak (quantitybreak),
  KEY discountcategory (discountcategory),
  KEY salestype (salestype),
  CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`)
)',
$db);

CreateTable('ediitemmapping',
'CREATE TABLE ediitemmapping (
  supporcust varchar(4) NOT NULL default '',
  partnercode varchar(10) NOT NULL default '',
  stockid varchar(20) NOT NULL default '',
  partnertockid varchar(50) NOT NULL default '',
  PRIMARY KEY  (supporcust,partnercode,stockid),
  KEY partnercode (partnercode),
  KEY stockid (stockid),
  KEY partnerstockid (partnerstockid),
  KEY supporcust (supporcust)
)',
$db);

CreateTable('edimessageformat',
'CREATE TABLE edimessageformat (
  id int(11) NOT NULL auto_increment,
  partnercode varchar(10) NOT NULL default '',
  messagetype varchar(6) NOT NULL default '',
  section varchar(7) NOT NULL default '',
  sequenceno int(11) NOT NULL default '0',
  linetext varchar(70) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY partnercode (partnercode,messagetype,sequenceno),
  KEY section (section)
)',
$db);

CreateTable('freightcosts',
'CREATE TABLE freightcosts (
  shipcostfromid int(11) NOT NULL auto_increment,
  locationfrom varchar(5) NOT NULL default '',
  destination varchar(40) NOT NULL default '',
  shipperid int(11) NOT NULL default '0',
  cubrate double(16,2) NOT NULL default '0.00',
  kgrate double(16,2) NOT NULL default '0.00',
  maxkgs double(16,2) NOT NULL default '999999.00',
  maxcub double(16,2) NOT NULL default '999999.00',
  fixedprice double(16,2) NOT NULL default '0.00',
  minimumchg double(16,2) NOT NULL default '0.00',
  PRIMARY KEY  (shipcostfromid),
  KEY destination (destination),
  KEY locationfrom (locationfrom),
  KEY shipperid (shipperid),
  KEY destination_2 (destination,locationfrom,shipperid),
  CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperID`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('gltrans',
'CREATE TABLE gltrans (
  counterindex int(11) NOT NULL auto_increment,
  type smallint(6) NOT NULL default '0',
  typeno bigint(16) NOT NULL default '1',
  chequeno int(11) NOT NULL default '0',
  trandate date NOT NULL default '0000-00-00',
  periodno smallint(6) NOT NULL default '0',
  account int(11) NOT NULL default '0',
  narrative varchar(200) NOT NULL default '',
  amount float NOT NULL default '0',
  posted tinyint(4) NOT NULL default '0',
  jobref varchar(20) NOT NULL default '',
  PRIMARY KEY  (counterindex),
  KEY account (account),
  KEY chequeno (chequeno),
  KEY periodno (periodno),
  KEY posted (posted),
  KEY trandate (trandate),
  KEY typeno (typeno),
  KEY type_and_number (type,typeno),
  KEY jobref (jobref),
  CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`),
  CONSTRAINT `gltrans_ibfk_1` FOREIGN KEY (`account`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`)
)',
$db);

CreateTable('grns',
'CREATE TABLE grns (
  grnbatch smallint(6) NOT NULL default '0',
  grnno int(11) NOT NULL auto_increment,
  podetailitem int(11) NOT NULL default '0',
  itemcode varchar(20) NOT NULL default '',
  deliverydate date NOT NULL default '0000-00-00',
  itemdescription varchar(100) NOT NULL default '',
  qtyrecd double(16,4) NOT NULL default '0.0000',
  quantityinv double(16,4) NOT NULL default '0.0000',
  supplierid varchar(10) NOT NULL default '',
  PRIMARY KEY  (grnno),
  KEY deliverydate (deliverydate),
  KEY itemcode (itemcode),
  KEY podetailitem (podetailitem),
  KEY supplierid (supplierid),
  CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`podetailitem`),
  CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
)',
$db);

CreateTable('holdreasons',
'CREATE TABLE holdreasons (
  reasoncode smallint(6) NOT NULL default '1',
  reasondescription char(30) NOT NULL default '',
  dissallowinvoices tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (reasoncode),
  KEY reasoncode (reasoncode),
  KEY reasondescription (reasondescription)
)',
$db);

CreateTable('lastcostrollup',
'CREATE TABLE lastcostrollup (
  stockid char(20) NOT NULL default '',
  totalonhand double(16,4) NOT NULL default '0.0000',
  matcost decimal(20,4) NOT NULL default '0.0000',
  labcost decimal(20,4) NOT NULL default '0.0000',
  oheadcost decimal(20,4) NOT NULL default '0.0000',
  categoryid char(6) NOT NULL default '',
  stockact int(11) NOT NULL default '0',
  adjGLact int(11) NOT NULL default '0',
  newmatcost decimal(20,4) NOT NULL default '0.0000',
  newlabcost decimal(20,4) NOT NULL default '0.0000',
  newoheadcost decimal(20,4) NOT NULL default '0.0000'
)',
$db);

CreateTable('locstock',
'CREATE TABLE locstock (
  loccode char(5) NOT NULL default '',
  stockid char(20) NOT NULL default '',
  quantity double(16,1) NOT NULL default '0.0',
  reorderlevel bigint(20) NOT NULL default '0',
  PRIMARY KEY  (loccode,stockid),
  KEY StockID (stockid),
  CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('locations',
'CREATE TABLE locations (
  loccode varchar(5) NOT NULL default '',
  locationname varchar(50) NOT NULL default '',
  deladd1 varchar(40) NOT NULL default '',
  deladd2 varchar(40) NOT NULL default '',
  deladd3 varchar(40) NOT NULL default '',
  tel varchar(30) NOT NULL default '',
  fax varchar(30) NOT NULL default '',
  email varchar(55) NOT NULL default '',
  contact varchar(30) NOT NULL default '',
  taxauthority tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (loccode)
)',
$db);

CreateTable('orderdeliverydifferenceslog',
'CREATE TABLE orderdeliverydifferenceslog (
  orderno int(11) NOT NULL default '0',
  invoiceno int(11) NOT NULL default '0',
  stockid varchar(20) NOT NULL default '',
  quantitydiff double(16,4) NOT NULL default '0.0000',
  debtorno varchar(10) NOT NULL default '',
  branch varchar(10) NOT NULL default '',
  can_or_bo char(3) NOT NULL default 'CAN',
  PRIMARY KEY  (orderno,invoiceno,stockid),
  KEY stockid (stockid),
  KEY debtorno (debtorno,branch),
  KEY can_or_boo (can_or_bo),
  KEY orderno (orderno),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_3` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`debtorno`, `branch`) REFERENCES `custbranch` (`debtorno`, `branchcode`)
)',
$db);

CreateTable('paymentterms',
'CREATE TABLE paymentterms (
  termsindicator char(2) NOT NULL default '',
  terms char(40) NOT NULL default '',
  daysbeforedue smallint(6) NOT NULL default '0',
  dayinfollowingmonth smallint(6) NOT NULL default '0',
  PRIMARY KEY  (termsindicator),
  KEY daysbeforedue (daysbeforedue),
  KEY dayinfollowingmonth (dayinfollowingmonth)
)',
$db);

CreateTable('periods',
'CREATE TABLE periods (
  periodno smallint(6) NOT NULL default '0',
  lastdate_in_period date NOT NULL default '0000-00-00',
  PRIMARY KEY  (periodno),
  KEY lastdate_in_period (lastdate_in_period)
)',
$db);

CreateTable('prices',
'CREATE TABLE prices (
  stockid varchar(20) NOT NULL default '',
  typeabbrev char(2) NOT NULL default '',
  currabrev char(3) NOT NULL default '',
  debtorno varchar(10) NOT NULL default '',
  price decimal(20,4) NOT NULL default '0.0000',
  branchcode varchar(10) NOT NULL default '',
  PRIMARY KEY  (stockid,typeabbrev,currabrev,debtorno),
  KEY currabrev (currabrev),
  KEY debtorno (debtorno),
  KEY stockid (stockid),
  KEY typeabbrev (typeabbrev),
  CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`typeabbrev`),
  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`)
)',
$db);

CreateTable('purchdata',
'CREATE TABLE purchdata (
  supplierno char(10) NOT NULL default '',
  stockid char(20) NOT NULL default '',
  price decimal(20,4) NOT NULL default '0.0000',
  suppliersuom char(50) NOT NULL default '',
  conversionfactor double(16,4) NOT NULL default '1.0000',
  supplierdescription char(50) NOT NULL default '',
  leadtime smallint(6) NOT NULL default '1',
  preferred tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (supplierno,stockid),
  KEY stockid (stockid),
  KEY supplierno (supplierno),
  KEY preferred (preferred),
  CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)',
$db);

CreateTable('purchorderdetails',
'CREATE TABLE purchorderdetails (
  podetailitem int(11) NOT NULL auto_increment,
  orderno int(11) NOT NULL default '0',
  itemcode varchar(20) NOT NULL default '',
  deliverydate date NOT NULL default '0000-00-00',
  itemdescription varchar(100) NOT NULL default '',
  glcode int(11) NOT NULL default '0',
  qtyinvoiced double(16,4) NOT NULL default '0.0000',
  unitprice double(16,4) NOT NULL default '0.0000',
  actprice double(16,4) NOT NULL default '0.0000',
  stdcostunit double(16,4) NOT NULL default '0.0000',
  quantityord double(16,4) NOT NULL default '0.0000',
  quantityrecd double(16,4) NOT NULL default '0.0000',
  shiptref int(1) NOT NULL default '0',
  jobref varchar(20) NOT NULL default '',
  completed tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (podetailitem),
  KEY deliverydate (deliverydate),
  KEY glcode (glcode),
  KEY itemcode (itemcode),
  KEY jobref (jobref),
  KEY orderno (orderno),
  KEY shiptref (shiptref),
  KEY completed (completed),
  CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`orderno`)
)',
$db);

CreateTable('purchorders',
'CREATE TABLE purchorders (
  orderno int(11) NOT NULL auto_increment,
  supplierno varchar(10) NOT NULL default '',
  comments longblob,
  orddate datetime NOT NULL default '0000-00-00 00:00:00',
  rate double(16,4) NOT NULL default '1.0000',
  dateprinted datetime default NULL,
  allowprint tinyint(4) NOT NULL default '1',
  initiator varchar(10) default NULL,
  requisitionno varchar(15) default NULL,
  intostocklocation varchar(5) NOT NULL default '',
  deladd1 varchar(40) NOT NULL default '',
  deladd2 varchar(40) NOT NULL default '',
  deladd3 varchar(40) NOT NULL default '',
  deladd4 varchar(40) NOT NULL default '',
  PRIMARY KEY  (orderno),
  KEY orddate (orddate),
  KEY supplierno (supplierno),
  KEY intostocklocation (intostocklocation),
  KEY allowprintPO (allowprint),
  CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
)',
$db);

CreateTable('reportcolumns',
'CREATE TABLE reportcolumns (
  reportid smallint(6) NOT NULL default '0',
  colno smallint(6) NOT NULL default '0',
  heading1 varchar(15) NOT NULL default '',
  heading2 varchar(15) default NULL,
  calculation tinyint(1) NOT NULL default '0',
  periodfrom smallint(6) default NULL,
  periodto smallint(6) default NULL,
  datatype varchar(15) default NULL,
  colnumerator tinyint(4) default NULL,
  coldenominator tinyint(4) default NULL,
  calcoperator char(1) default NULL,
  budgetoractual tinyint(1) NOT NULL default '0',
  valformat char(1) NOT NULL default 'N',
  constant float NOT NULL default '0',
  PRIMARY KEY  (reportid,colno),
  CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`reportid`)
)',
$db);

CreateTable('reportheaders',
'CREATE TABLE reportheaders (
  reportid smallint(6) NOT NULL auto_increment,
  reportheading varchar(80) NOT NULL default '',
  groupbydata1 varchar(15) NOT NULL default '',
  newpageafter1 tinyint(1) NOT NULL default '0',
  lower1 varchar(10) NOT NULL default '',
  upper1 varchar(10) NOT NULL default '',
  groupbydata2 varchar(15) default NULL,
  newpageafter2 tinyint(1) NOT NULL default '0',
  lower2 varchar(10) default NULL,
  upper2 varchar(10) default NULL,
  groupbydata3 varchar(15) default NULL,
  newpageafter3 tinyint(1) NOT NULL default '0',
  lower3 varchar(10) default NULL,
  upper3 varchar(10) default NULL,
  groupbydata4 varchar(15) NOT NULL default '',
  newpageafter4 tinyint(1) NOT NULL default '0',
  upper4 varchar(10) NOT NULL default '',
  lower4 varchar(10) NOT NULL default '',
  PRIMARY KEY  (reportid),
  KEY reportheading (reportheading)
)',
$db);

CreateTable('salesanalysis',
'CREATE TABLE salesanalysis (
  typeabbrev char(2) NOT NULL default '',
  periodno smallint(6) NOT NULL default '0',
  amt double(16,4) NOT NULL default '0.0000',
  cost double(16,4) NOT NULL default '0.0000',
  cust varchar(10) NOT NULL default '',
  custbranch varchar(10) NOT NULL default '',
  qty double(16,4) NOT NULL default '0.0000',
  disc double(16,4) NOT NULL default '0.0000',
  stockid varchar(20) NOT NULL default '',
  area char(2) NOT NULL default '',
  budgetoractual tinyint(1) NOT NULL default '0',
  salesperson char(3) NOT NULL default '',
  stkcategory varchar(6) NOT NULL default '',
  ID int(11) NOT NULL auto_increment,
  PRIMARY KEY  (ID),
  KEY custbranch (custbranch),
  KEY cust (cust),
  KEY periodno (periodno),
  KEY stkcategory (stkcategory),
  KEY stockid (stockid),
  KEY typeabbrev (typeabbrev),
  KEY area (area),
  KEY budgetoractual (budgetoractual),
  KEY salesperson (salesperson),
  CONSTRAINT `salesanalysis_ibfk_1` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
)',
$db);

CreateTable('salesglpostings',
'CREATE TABLE salesglpostings (
  id int(11) NOT NULL auto_increment,
  area char(2) NOT NULL default '',
  stkcat varchar(6) NOT NULL default '',
  discountglcode int(11) NOT NULL default '0',
  salesglcode int(11) NOT NULL default '0',
  salestype char(2) NOT NULL default 'AN',
  PRIMARY KEY  (id),
  UNIQUE KEY area_stkcat (area,stkcat,salestype),
  KEY area (area),
  KEY stkcat (stkcat),
  KEY salestype (salestype)
)',
$db);

CreateTable('salesorderdetails',
'CREATE TABLE salesorderdetails (
  orderno int(11) NOT NULL default '0',
  stkcode char(20) NOT NULL default '',
  qtyinvoiced double(16,4) NOT NULL default '0.0000',
  unitprice double(16,4) NOT NULL default '0.0000',
  quantity double(16,4) NOT NULL default '0.0000',
  estimate tinyint(4) NOT NULL default '0',
  discountpercent double(16,4) NOT NULL default '0.0000',
  actualdispatchdate datetime NOT NULL default '0000-00-00 00:00:00',
  completed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (orderno,stkcode),
  KEY orderno (orderno),
  KEY stkcode (stkcode),
  KEY completed (completed),
  CONSTRAINT `salesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salesorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
)',
$db);

CreateTable('salesorders',
'CREATE TABLE salesorders (
  orderno int(11) NOT NULL auto_increment,
  debtorno varchar(10) NOT NULL default '',
  branchcode varchar(10) NOT NULL default '',
  customerref varchar(50) NOT NULL default '',
  buyername varchar(50) default NULL,
  comments longblob,
  orddate date NOT NULL default '0000-00-00',
  ordertype char(2) NOT NULL default '',
  shipvia int(11) NOT NULL default '0',
  deladd1 varchar(40) NOT NULL default '',
  deladd2 varchar(20) NOT NULL default '',
  deladd3 varchar(15) NOT NULL default '',
  deladd4 varchar(15) default NULL,
  contactphone varchar(25) default NULL,
  contactemail varchar(25) default NULL,
  deliverto varchar(40) NOT NULL default '',
  freightcost float(10,2) NOT NULL default '0.00',
  fromstkloc varchar(5) NOT NULL default '',
  deliverydate date NOT NULL default '0000-00-00',
  printedpackingslip tinyint(4) NOT NULL default '0',
  datepackingslipprinted date NOT NULL default '0000-00-00',
  PRIMARY KEY  (orderno),
  KEY debtorno (debtorno),
  KEY orddate (orddate),
  KEY ordertype (ordertype),
  KEY locationindex (fromstkLoc),
  KEY branchcode (branchcode,debtorno),
  KEY shipvia (shipvia),
  CONSTRAINT `salesorders_ibfk_3` FOREIGN KEY (`fromstkloc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `salesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `salesorders_ibfk_2` FOREIGN KEY (`shipvia`) REFERENCES `shippers` (`shipper_id`)
)',
$db);

CreateTable('salestypes',
'CREATE TABLE salestypes (
  typeabbrev char(2) NOT NULL default '',
  sales_type char(20) NOT NULL default '',
  PRIMARY KEY  (typeabbrev),
  KEY sales_type (sales_type)
)',
$db);

Createtable('salesman',
'CREATE TABLE salesman (
  salesmancode char(3) NOT NULL default '',
  salesmanname char(30) NOT NULL default '',
  smantel char(20) NOT NULL default '',
  smanfax char(20) NOT NULL default '',
  commissionrate1 double(16,4) NOT NULL default '0.0000',
  breakpoint decimal(20,4) NOT NULL default '0.0000',
  commissionrate2 double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (salesmancode)
)',
$db);

CreateTable('shipmentcharges',
'CREATE TABLE shipmentcharges (
  shiptchgid int(11) NOT NULL auto_increment,
  shiptref int(11) NOT NULL default '0',
  transtype smallint(6) NOT NULL default '0',
  transno int(11) NOT NULL default '0',
  stockid varchar(20) NOT NULL default '',
  value float NOT NULL default '0',
  PRIMARY KEY  (shiptchgid),
  KEY transtype (transtype,transno),
  KEY shiptref (shiptref),
  KEY stockid (stockid),
  KEY transtype_2 (transtype),
  CONSTRAINT `shipmentcharges_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`shiptref`),
  CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
)',
$db);

CreateTable('shipments',
'CREATE TABLE shipments (
  shiptref int(11) NOT NULL default '0',
  voyageref varchar(20) NOT NULL default '0',
  vessel varchar(50) NOT NULL default '',
  eta datetime NOT NULL default '0000-00-00 00:00:00',
  accumvalue double(16,4) NOT NULL default '0.0000',
  supplierid varchar(10) NOT NULL default '',
  closed tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (shiptref),
  KEY eta (eta),
  KEY supplierid (supplierid),
  KEY shipperref (voyageref),
  KEY vessel (vessel),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
)',
$db);

CreateTable('shippers',
'CREATE TABLE shippers (
  shipper_id int(11) NOT NULL auto_increment,
  shippername char(40) NOT NULL default '',
  mincharge double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (shipper_id)
)',
$db);

CreateTable('stockcategory',
'CREATE TABLE stockcategory (
  categoryid char(6) NOT NULL default '',
  categorydescription char(20) NOT NULL default '',
  stocktype char(1) NOT NULL default 'F',
  stockact int(11) NOT NULL default '0',
  adjglact int(11) NOT NULL default '0',
  purchpricevaract int(11) NOT NULL default '80000',
  materialuseagevarac int(11) NOT NULL default '80000',
  wipact int(11) NOT NULL default '0',
  PRIMARY KEY  (categoryid),
  KEY categorydescription (categorydescription),
  KEY stocktype (stocktype)
)',
$db);

CreateTable('stockcheckfreeze',
'CREATE TABLE stockcheckfreeze (
  stockid varchar(20) NOT NULL default '',
  loccode varchar(5) NOT NULL default '',
  qoh float NOT NULL default '0',
  PRIMARY KEY  (stockid),
  KEY LocCode (loccode),
  CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)',
$db);

CreateTable('stockcounts',
'CREATE TABLE stockcounts (
  id int(11) NOT NULL auto_increment,
  stockid varchar(20) NOT NULL default '',
  loccode varchar(5) NOT NULL default '',
  qtycounted float NOT NULL default '0',
  reference varchar(20) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY StockID (stockid),
  KEY LocCode (loccode),
  CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)',
$db);

CreateTable('stockmaster',
'CREATE TABLE stockmaster (
  stockid varchar(20) NOT NULL default '',
  categoryid varchar(6) NOT NULL default '',
  description varchar(50) NOT NULL default '',
  longdescription text NOT NULL,
  units varchar(20) NOT NULL default 'each',
  mbflag char(1) NOT NULL default 'B',
  lastcurcostdate date NOT NULL default '1800-01-01',
  actualcost decimal(20,4) NOT NULL default '0.0000',
  lastcost decimal(20,4) NOT NULL default '0.0000',
  materialcost decimal(20,4) NOT NULL default '0.0000',
  labourcost decimal(20,4) NOT NULL default '0.0000',
  overheadcost decimal(20,4) NOT NULL default '0.0000',
  lowestlevel smallint(6) NOT NULL default '0',
  discontinued tinyint(4) NOT NULL default '0',
  controlled tinyint(4) NOT NULL default '0',
  eoq double(10,2) NOT NULL default '0.00',
  volume decimal(20,4) NOT NULL default '0.0000',
  kgs decimal(20,4) NOT NULL default '0.0000',
  barcode varchar(50) NOT NULL default '',
  discountcategory char(2) NOT NULL default '',
  taxlevel tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (stockid),
  KEY categoryid (categoryid),
  KEY description (description),
  KEY lastcurcostdate (lastcurcostdate),
  KEY mbflag (mbflag),
  KEY stockid (stockid,categoryid),
  KEY controlled (controlled),
  KEY discountcategory (discountcategory),
  CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`)
)',
$db);

CreateTable('stockmoves',
'CREATE TABLE stockmoves (
  stkmoveno int(11) NOT NULL auto_increment,
  stockid char(20) NOT NULL default '',
  type smallint(6) NOT NULL default '0',
  transno int(11) NOT NULL default '0',
  loccode char(5) NOT NULL default '',
  bundle char(8) NOT NULL default '1',
  trandate date NOT NULL default '0000-00-00',
  debtorno char(10) NOT NULL default '',
  branchcode char(10) NOT NULL default '',
  price decimal(20,4) NOT NULL default '0.0000',
  prd smallint(6) NOT NULL default '0',
  reference char(40) NOT NULL default '',
  qty double(16,4) NOT NULL default '1.0000',
  discountpercent double(16,4) NOT NULL default '0.0000',
  standardcost double(16,4) NOT NULL default '0.0000',
  show_on_inv_crds tinyint(4) NOT NULL default '1',
  newqoh double NOT NULL default '0',
  hidemovt tinyint(4) NOT NULL default '0',
  taxrate float NOT NULL default '0',
  PRIMARY KEY  (stkmoveno),
  KEY bundle (bundle),
  KEY debtorno (debtorno),
  KEY loccode (loccode),
  KEY prd (prd),
  KEY stockid (stockid,loccode),
  KEY stockid_2 (stockid),
  KEY trandate (trandate),
  KEY transno (transno),
  KEY type (type),
  KEY show_on_inv_crds (show_on_inv_crds),
  KEY hide (hidemovt),
  CONSTRAINT `stockmoves_ibfk_4` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`),
  CONSTRAINT `stockmoves_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockmoves_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `stockmoves_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('suppallocs',
'CREATE TABLE suppallocs (
  id int(11) NOT NULL auto_increment,
  amt float(20,2) NOT NULL default '0.00',
  datealloc date NOT NULL default '0000-00-00',
  transid_allocfrom int(11) NOT NULL default '0',
  transid_allocto int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY transid_allocfrom (transid_allocfrom),
  KEY transid_allocto (transid_allocto),
  KEY datealloc (datealloc),
  CONSTRAINT `suppallocs_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `SuppTrans` (`id`),
  CONSTRAINT `suppallocs_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `SuppTrans` (`id`)
)',
$db);

CreateTable('supptrans',
'CREATE TABLE supptrans (
  transno int(11) NOT NULL default '0',
  type smallint(6) NOT NULL default '0',
  supplierno varchar(10) NOT NULL default '',
  suppreference varchar(20) NOT NULL default '',
  trandate date NOT NULL default '0000-00-00',
  duedate date NOT NULL default '0000-00-00',
  settled tinyint(4) NOT NULL default '0',
  rate double(16,6) NOT NULL default '1.000000',
  ovamount double(16,4) NOT NULL default '0.0000',
  ovgst double(16,4) NOT NULL default '0.0000',
  diffonexch double(16,4) NOT NULL default '0.0000',
  alloc double(16,4) NOT NULL default '0.0000',
  transtext longblob,
  hold tinyint(4) NOT NULL default '0',
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id),
  UNIQUE KEY typetransno (transno,type),
  KEY duedate (duedate),
  KEY hold (hold),
  KEY supplierno (supplierno),
  KEY settled (settled),
  KEY supplierno_2 (supplierno,suppreference),
  KEY suppreference (suppreference),
  KEY trandate (trandate),
  KEY transno (transno),
  KEY type (type),
  CONSTRAINT `supptrans_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `supptrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`)
)',
$db);

CreateTable('suppliercontacts',
'CREATE TABLE suppliercontacts (
  supplierid varchar(10) NOT NULL default '',
  contact varchar(30) NOT NULL default '',
  position varchar(30) NOT NULL default '',
  tel varchar(30) NOT NULL default '',
  fax varchar(30) NOT NULL default '',
  mobile varchar(30) NOT NULL default '',
  email varchar(55) NOT NULL default '',
  ordercontact tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (supplierid,contact),
  KEY contact (contact),
  KEY supplierid (supplierid),
  CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
)',
$db);

CreateTable('suppliers',
'CREATE TABLE suppliers (
  supplierid char(10) NOT NULL default '',
  suppname char(40) NOT NULL default '',
  address1 char(40) NOT NULL default '',
  address2 char(40) NOT NULL default '',
  address3 char(40) NOT NULL default '',
  address4 char(50) NOT NULL default '',
  currcode char(3) NOT NULL default '',
  suppliersince date NOT NULL default '0000-00-00',
  paymentterms char(2) NOT NULL default '',
  lastpaid double(16,4) NOT NULL default '0.0000',
  lastpaiddate datetime default NULL,
  bankact char(16) NOT NULL default '',
  bankref char(12) NOT NULL default '',
  bankpartics char(12) NOT NULL default '',
  remittance tinyint(4) NOT NULL default '1',
  taxauthority tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (supplierid),
  KEY currcode (currcode),
  KEY paymentterms (paymentterms),
  KEY supplierid (supplierid),
  KEY suppname (suppname),
  KEY taxauthority (taxauthority),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`)
)',
$db);

CreateTable('systypes',
'CREATE TABLE systypes (
  typeid smallint(6) NOT NULL default '0',
  typename char(50) NOT NULL default '',
  typeno int(11) NOT NULL default '1',
  PRIMARY KEY  (typeid),
  KEY typeno (typeno)
)',
$db);

CreateTable('taxauthlevels',
'CREATE TABLE taxauthlevels (
  taxauthority tinyint(4) NOT NULL default '1',
  dispatchtaxauthority tinyint(4) NOT NULL default '1',
  level tinyint(4) NOT NULL default '0',
  taxrate double NOT NULL default '0',
  PRIMARY KEY  (taxauthority,dispatchtaxauthority,level)
)',
$db);

CreateTable('taxauthorities',
'CREATE TABLE taxauthorities (
  taxid tinyint(4) NOT NULL default '0',
  description char(20) NOT NULL default '',
  taxglcode int(11) NOT NULL default '0',
  purchtaxglaccount int(11) NOT NULL default '0',
  PRIMARY KEY  (taxid)
)',
$db);

CreateTable('woissues',
'CREATE TABLE woissues (
  issueno int(11) NOT NULL default '0',
  woref char(20) NOT NULL default '',
  stockid char(20) NOT NULL default '',
  issuetype char(1) NOT NULL default 'M',
  workcentre char(5) NOT NULL default '',
  qtyissued double(16,4) NOT NULL default '0.0000',
  stdcost decimal(20,4) NOT NULL default '0.0000',
  KEY workcentre (workcentre),
  KEY issueno (issueno),
  KEY issueno_2 (issueno,woref,stockid),
  KEY StockID (stockid),
  KEY IssueType (issuetype),
  KEY woref (woref),
  CONSTRAINT `woissues_ibfk_3` FOREIGN KEY (`workcentre`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `woissues_ibfk_1` FOREIGN KEY (`woref`) REFERENCES `worksorders` (`woref`),
  CONSTRAINT `woissues_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)',
$db);

CreateTable('woreqirements',
'CREATE TABLE woreqirements (
  id int(11) NOT NULL auto_increment,
  woref char(20) NOT NULL default '',
  stockid char(20) NOT NULL default '',
  wrkcentre char(5) NOT NULL default '',
  unitsreq double(16,4) NOT NULL default '1.0000',
  stdcost decimal(20,4) NOT NULL default '0.0000',
  resourcetype char(1) NOT NULL default 'M',
  PRIMARY KEY  (id),
  KEY wrkcentre (wrkcentre),
  KEY resourcetype (resourcetype),
  KEY woref (woref,stockid),
  KEY stockid (stockid),
  KEY woref_2 (woref),
  CONSTRAINT `worequirements_ibfk_3` FOREIGN KEY (`wrkcentre`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `worequirements_ibfk_1` FOREIGN KEY (`woref`) REFERENCES `worksorders` (`woref`),
  CONSTRAINT `worequirements_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)',
$db);

CreateTable('www_users',
'CREATE TABLE www_users (
  userid varchar(20) NOT NULL default '',
  password varchar(20) NOT NULL default '',
  realname varchar(35) NOT NULL default '',
  customerid varchar(10) NOT NULL default '',
  phone varchar(30) NOT NULL default '',
  email varchar(55) default NULL,
  defaultlocation varchar(5) NOT NULL default '',
  fullaccess int(11) NOT NULL default '1',
  lastvisitdate datetime default NULL,
  branchcode varchar(10) NOT NULL default '',
  pagesize varchar(20) NOT NULL default 'A4',
  modulesallowed varchar(20) NOT NULL default '',
  blocked tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (userid),
  KEY customerid (customerid),
  KEY defaultlocation (defaultlocation),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('workcentres',
'CREATE TABLE workcentres (
  code char(5) NOT NULL default '',
  location char(5) NOT NULL default '',
  description char(20) NOT NULL default '',
  capacity double(16,4) NOT NULL default '1.0000',
  overheadperhour decimal(20,4) NOT NULL default '0.0000',
  overheadrecoveryact int(11) NOT NULL default '0',
  setuphrs decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (code),
  KEY description (description),
  KEY location (location),
  CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('worksorders',
'CREATE TABLE worksorders (
  woref char(20) NOT NULL default '',
  loccode char(5) NOT NULL default '',
  unitsreqd smallint(6) NOT NULL default '1',
  stockid char(20) NOT NULL default '',
  stdcost decimal(20,4) NOT NULL default '0.0000',
  requiredby date NOT NULL default '0000-00-00',
  releaseddate date NOT NULL default '1800-01-01',
  accumvalueissued decimal(20,4) NOT NULL default '0.0000',
  accumvaluetrfd decimal(20,4) NOT NULL default '0.0000',
  closed tinyint(4) NOT NULL default '0',
  released tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (woref),
  KEY stockid (stockid),
  KEY loccode (loccode),
  KEY releaseddate (releaseddate),
  KEY requiredby (requiredby),
  KEY woref (woref,loccode),
  CONSTRAINT `worksorders_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)',
$db);

CreateTable('config',
'CREATE TABLE config (
  confname varchar(35) NOT NULL DEFAULT '',
  confvalue text NOT NULL,
  PRIMARY KEY (`confname`)
)',
$db);

NewConfigValue('DBUpdateNumber', 0, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>+