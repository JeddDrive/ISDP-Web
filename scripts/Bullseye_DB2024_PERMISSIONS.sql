
-- Bullseye DB SQL PERMISSIONS Script 2024
-- version 1.0
-- January 16, 2024
-- Chris London

-- ********************************************
-- *** Add Permission Tables to Database    ***
-- ********************************************
-- 
use bullseyedb2024;
SET SQL_SAFE_UPDATES = 0;

-- ********************************************
-- ****       Create permission table      ****
-- ********************************************
-- category, province, position, site, txnstatus, txntype, employee, vehicle, supplier

--
-- Create table `permission`
--
CREATE TABLE `permission` (
  `permissionID` varchar(20) NOT NULL PRIMARY KEY
);


--
-- Insert data for table `permission`
--
INSERT INTO `permission` (`permissionID`) VALUES
('ADDUSER'),
('EDITUSER'),
('DELETEUSER'),
('READUSER'),
('CREATEPERMISSION'),
('SETPERMISSION'),
('MOVEINVENTORY'),
('CREATESTOREORDER'),
('RECEIVESTOREORDER'),
('PREPARESTOREORDER'),
('FULFILSTOREORDER'),
('ADDITEMTOBACKORDER'),
('CREATEBACKORDER'),
('EDITSITE'),
('ADDSITE'),
('ADDSUPPLIER'),
('VIEWORDERS'),
('DELETELOCATION'),
('EDITINVENTORY'),
('EDITITEM'),
('DELIVERY'),
('ACCEPTSTOREORDER'),
('MODIFYRECORD'),
('CREATELOSS'),
('PROCESSRETURN'),
('ADDNEWPRODUCT'),
('EDITPRODUCT'),
('CREATESUPPLIERORDER'),
('CREATEREPORT');

--
-- Create table `user_permissions`
--
CREATE TABLE `user_permission` (
  `employeeID`int(11), 
  `permissionID` varchar(20),
  PRIMARY KEY (`employeeID`, `permissionID`), 
  FOREIGN KEY(`employeeID`) REFERENCES employee(`employeeID`),
  FOREIGN KEY(`permissionID`) REFERENCES permission(`permissionID`)
);

--
-- Insert data for table `user_permission`
-- Give ADMIN user all permissions to start
--
INSERT INTO `user_permission` (`employeeID`, `permissionID`) VALUES
(1, 'ADDUSER'),
(1, 'EDITUSER'),
(1, 'DELETEUSER'),
(1, 'READUSER'),
(1, 'SETPERMISSION'),
(1, 'MOVEINVENTORY'),
(1, 'CREATESTOREORDER'),
(1, 'RECEIVESTOREORDER'),
(1, 'PREPARESTOREORDER'),
(1, 'FULFILSTOREORDER'),
(1, 'ADDITEMTOBACKORDER'),
(1, 'CREATEBACKORDER'),
(1, 'EDITSITE'),
(1, 'ADDSITE'),
(1, 'VIEWORDERS'),
(1, 'DELETELOCATION'),
(1, 'EDITINVENTORY'),
(1, 'EDITITEM'),
(1, 'DELIVERY'),
(1, 'ACCEPTSTOREORDER'),
(1, 'MODIFYRECORD'),
(1, 'CREATELOSS'),
(1, 'PROCESSRETURN'),
(1, 'ADDNEWPRODUCT'),
(1, 'EDITPRODUCT'),
(1, 'CREATESUPPLIERORDER'),
(1, 'CREATEREPORT');
