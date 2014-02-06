<?php

DropConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_3', $db);
DropConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_4', $db);
DropConstraint('internalstockcatrole', 'secroleid', $db);

DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_3', $db);
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_4', $db);
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_5', $db);
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_6', $db);

DropConstraint('stockmovestaxes', 'stockmovestaxes_ibfk_3', $db);
DropConstraint('stockmovestaxes', 'stockmovestaxes_ibfk_4', $db);

DropConstraint('stockrequest', 'stockrequest_ibfk_3', $db);
DropConstraint('stockrequest', 'stockrequest_ibfk_4', $db);

DropConstraint('stockrequestitems', 'dispatchid', $db);
DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_3', $db);
DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_4', $db);

DropConstraint('workorders', 'worksorders_ibfk_1', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>