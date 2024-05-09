-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 09, 2024 at 09:40 PM
-- Server version: 11.3.2-MariaDB
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `control`
--

-- --------------------------------------------------------

--
-- Table structure for table `globals`
--

CREATE TABLE `globals` (
  `name` varchar(16) NOT NULL,
  `int1` int(11) NOT NULL DEFAULT 0,
  `int2` int(11) NOT NULL DEFAULT 0,
  `int3` int(11) NOT NULL DEFAULT 0,
  `int4` int(11) NOT NULL DEFAULT 0,
  `double1` double NOT NULL DEFAULT 0,
  `double2` double NOT NULL DEFAULT 0,
  `double3` double NOT NULL DEFAULT 0,
  `double4` double NOT NULL DEFAULT 0,
  `string1` varchar(16) DEFAULT NULL,
  `string2` varchar(16) DEFAULT NULL,
  `string3` varchar(16) DEFAULT NULL,
  `string4` varchar(16) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `globals`
--

INSERT INTO `globals` (`name`, `int1`, `int2`, `int3`, `int4`, `double1`, `double2`, `double3`, `double4`, `string1`, `string2`, `string3`, `string4`) VALUES
('Master_alarm', 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', 'dnorcini'),
('Title', 0, 0, 0, 0, 0, 0, 0, 0, 'Test Chamber 1', NULL, NULL, NULL),
('have_TS', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('have_RGA', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('have_Cams', 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('have_LB', 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('web_bg_colour', 0, 0, 0, 0, 0, 0, 0, 0, 'white', NULL, NULL, NULL),
('web_text_colour', 0, 0, 0, 0, 0, 0, 0, 0, 'navy', NULL, NULL, NULL),
('have_HV_crate', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lug_categories`
--

CREATE TABLE `lug_categories` (
  `l_c_indx` int(11) NOT NULL,
  `category` varchar(40) NOT NULL,
  `subcategory` varchar(40) NOT NULL DEFAULT 'General'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `lug_categories`
--

INSERT INTO `lug_categories` (`l_c_indx`, `category`, `subcategory`) VALUES
(15, 'Disk', 'General'),
(3, 'System', 'General'),
(8, 'Calibrations', 'General');

-- --------------------------------------------------------

--
-- Table structure for table `lug_entries`
--

CREATE TABLE `lug_entries` (
  `entry_id` int(11) NOT NULL,
  `important_flag` tinyint(1) DEFAULT 0,
  `action_user` varchar(40) NOT NULL,
  `action_time` int(11) NOT NULL,
  `edit_user` varchar(40) DEFAULT NULL,
  `edit_time` int(11) DEFAULT NULL,
  `run_number` int(11) NOT NULL,
  `category` varchar(40) NOT NULL,
  `subcategory` varchar(40) NOT NULL,
  `entry_description` longtext NOT NULL,
  `entry_image` mediumblob DEFAULT NULL,
  `entry_image_thumb` blob DEFAULT NULL,
  `entry_file` mediumblob DEFAULT NULL,
  `filename` char(40) DEFAULT NULL,
  `source` varchar(40) DEFAULT NULL,
  `strikeme` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `lug_entries`
--

INSERT INTO `lug_entries` (`entry_id`, `important_flag`, `action_user`, `action_time`, `edit_user`, `edit_time`, `run_number`, `category`, `subcategory`, `entry_description`, `entry_image`, `entry_image_thumb`, `entry_file`, `filename`, `source`, `strikeme`) VALUES
(13, 0, 'guest', 1461523701, 'dnorcini', 1461523729, 2, 'Disk', 'General', 'This is also a thing that works.', NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `msg_log`
--

CREATE TABLE `msg_log` (
  `msg_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `ip_address` varchar(16) DEFAULT NULL,
  `subsys` varchar(16) DEFAULT NULL,
  `msgs` text DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `is_error` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `msg_log_types`
--

CREATE TABLE `msg_log_types` (
  `types` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `msg_log_types`
--

INSERT INTO `msg_log_types` (`types`) VALUES
('Alarm'),
('Alert'),
('Config.'),
('Error'),
('Setpoint'),
('Shifts');

-- --------------------------------------------------------

--
-- Table structure for table `runs`
--

CREATE TABLE `runs` (
  `num` int(11) NOT NULL,
  `start_t` int(11) NOT NULL,
  `end_t` int(11) NOT NULL DEFAULT 0,
  `file_path` varchar(128) DEFAULT NULL,
  `file_root` varchar(128) DEFAULT NULL,
  `note` char(128) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `runs`
--

INSERT INTO `runs` (`num`, `start_t`, `end_t`, `file_path`, `file_root`, `note`) VALUES
(1, 1342019232, 1457624232, '', '', 'Setup');

-- --------------------------------------------------------

--
-- Table structure for table `sc_insts`
--

CREATE TABLE `sc_insts` (
  `name` varchar(16) NOT NULL,
  `description` varchar(64) DEFAULT NULL,
  `subsys` varchar(16) DEFAULT NULL,
  `run` tinyint(1) NOT NULL DEFAULT 0,
  `restart` tinyint(1) NOT NULL DEFAULT 0,
  `WD_ctrl` tinyint(1) NOT NULL DEFAULT 1,
  `path` varchar(256) NOT NULL DEFAULT 'rel_path',
  `dev_type` varchar(16) DEFAULT NULL,
  `dev_address` varchar(24) DEFAULT NULL,
  `start_time` int(11) NOT NULL DEFAULT -1,
  `last_update_time` int(11) NOT NULL DEFAULT -1,
  `PID` int(11) NOT NULL DEFAULT -1,
  `user1` varchar(16) DEFAULT NULL,
  `user2` varchar(16) DEFAULT NULL,
  `parm1` double NOT NULL DEFAULT 0,
  `parm2` double NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `sc_insts`
--

INSERT INTO `sc_insts` (`name`, `description`, `subsys`, `run`, `restart`, `WD_ctrl`, `path`, `dev_type`, `dev_address`, `start_time`, `last_update_time`, `PID`, `user1`, `user2`, `parm1`, `parm2`, `notes`) VALUES
('alarm_trip_sys', 'Alarm trip monitor program', 'Alarm', 1, 0, 1, 'alarm_trip_system/alarm_trip_sys', 'daemon', '', -1, -1, -1, '', '', 0, 0, ''),
('alarm_alert_sys', 'Alarm alert monitor program', 'Alarm', 1, 0, 1, 'alarm_alert_system/alarm_alert_sys', 'daemon', '', -1, -1, -1, 'Alarm_Light', 'Alarm_Siren', 0, 0, ''),
('Watchdog', 'Overseeing watchdog program', 'Watchdog', 1, 0, 0, '/home/damicm/SC_backend/slow_control_code/', 'watchdog', '', 1714420406, 1715263478, 1056, '', '', 0, 0, 'This should always be running. If not, you must start it manually using the path above (i.e. /home/damic/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog). This requires that any old Watchdog processes were killed AND the web interface is not stuck on an old-issued PID (i.e. need to reset instrument if non-graceful shutdown). The Watchdog will start all other instruments/daemons automatically if their `run` flag is set.'),
('APC3000', 'Monitor power consumption of UPS', 'Test Chamber 1', 1, 0, 1, 'APC3000/APC3000', 'modbus', '', -1, -1, -1, '502', NULL, 0, 0, NULL),
('disk_free', 'Monitor free disk space', 'Sys', 1, 0, 1, 'disk_free/disk_free', 'daemon', NULL, -1, -1, -1, NULL, NULL, 0, 0, ''),
('Raritan_PX3', 'Monitor and control PDU', 'Test Chamber 1', 1, 0, 1, 'Raritan_PX3/Raritan_PX3', 'ethernet', '', -1, -1, -1, '5502', NULL, 0, 0, NULL),
('LS336', 'Monitor chamber temperature and control heater', 'Test Chamber 1', 1, 0, 1, 'LS336_eth/LS336', 'ethernet', '192.168.1.101', 1714425623, 1715263474, 20553, '7777', NULL, 0, 0, NULL),
('HiCube80', 'Monitor and run vacuum pump', 'Test Chamber 1', 1, 0, 1, 'HiCube80/HiCube80', 'ethernet', '', -1, -1, -1, '5300', NULL, 0, 0, NULL),
('CryoTelGT_AVC', 'Monitor and controll CryoTel GT with AVC', 'Test Chamber 1', 1, 0, 1, 'CryoTelGT_AVC/CryoTelGT_AVC', 'ethernet', '192.168.1.102', 1714775295, 1715263483, 1047233, '100', NULL, 0, 0, NULL),
('WIENER_VME', 'Monitor and control VME crate for ACMs', 'Test Chamber 1', 1, 0, 1, 'WIENER_VME/WIENER_VME', 'ethernet', '', -1, -1, -1, '161', NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sc_sensors`
--

CREATE TABLE `sc_sensors` (
  `name` varchar(16) NOT NULL,
  `description` varchar(64) DEFAULT NULL,
  `type` varchar(16) NOT NULL DEFAULT 'unknown',
  `subtype` varchar(16) DEFAULT NULL,
  `ctrl_priv` varchar(128) NOT NULL DEFAULT 'full',
  `num` int(11) NOT NULL DEFAULT 0,
  `instrument` varchar(16) NOT NULL DEFAULT 'inst_name',
  `units` varchar(16) NOT NULL DEFAULT 'units',
  `discrete_vals` varchar(128) DEFAULT NULL,
  `al_set_val_low` double NOT NULL DEFAULT 0,
  `al_set_val_high` double NOT NULL DEFAULT 0,
  `al_arm_val_low` tinyint(1) NOT NULL DEFAULT 0,
  `al_arm_val_high` tinyint(1) NOT NULL DEFAULT 0,
  `al_set_rate_low` double NOT NULL DEFAULT 0,
  `al_set_rate_high` double NOT NULL DEFAULT 0,
  `al_arm_rate_low` tinyint(1) NOT NULL DEFAULT 0,
  `al_arm_rate_high` tinyint(1) NOT NULL DEFAULT 0,
  `alarm_tripped` tinyint(1) NOT NULL DEFAULT 0,
  `grace` int(11) NOT NULL DEFAULT 0,
  `last_trip` int(11) NOT NULL DEFAULT -1,
  `settable` tinyint(1) NOT NULL DEFAULT 0,
  `show_rate` tinyint(1) NOT NULL DEFAULT 0,
  `hide_sensor` tinyint(1) NOT NULL DEFAULT 0,
  `update_period` int(11) NOT NULL DEFAULT 60,
  `num_format` varchar(16) DEFAULT NULL,
  `user1` varchar(16) DEFAULT NULL,
  `user2` varchar(16) DEFAULT NULL,
  `user3` varchar(16) DEFAULT NULL,
  `user4` varchar(16) DEFAULT NULL,
  `parm1` double NOT NULL DEFAULT 0,
  `parm2` double NOT NULL DEFAULT 0,
  `parm3` double NOT NULL DEFAULT 0,
  `parm4` double NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `sc_sensors`
--

INSERT INTO `sc_sensors` (`name`, `description`, `type`, `subtype`, `ctrl_priv`, `num`, `instrument`, `units`, `discrete_vals`, `al_set_val_low`, `al_set_val_high`, `al_arm_val_low`, `al_arm_val_high`, `al_set_rate_low`, `al_set_rate_high`, `al_arm_rate_low`, `al_arm_rate_high`, `alarm_tripped`, `grace`, `last_trip`, `settable`, `show_rate`, `hide_sensor`, `update_period`, `num_format`, `user1`, `user2`, `user3`, `user4`, `parm1`, `parm2`, `parm3`, `parm4`, `notes`) VALUES
('Inlet_Current', 'Monitor rms current of PDU inlet', '10', 'current', 'full', 0, 'Raritan_PX3', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Inlet_Power', 'Monitor active power of PDU inlet', '10', 'power', 'full', 0, 'Raritan_PX3', 'W', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('dsk_shm', 'Free space on /dev/shm', '2', 'disk', 'full', 0, 'disk_free', '%', NULL, 50, 99, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, '/dev/shm', NULL, NULL, NULL, 0, 0, 0, 0, ''),
('dsk_root', 'Free space on /', '2', 'disk', 'full', 0, 'disk_free', '%', NULL, 30, 70, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, '/', NULL, NULL, NULL, 0, 0, 0, 0, ''),
('Power_Output', 'Power load of UPS', '12', 'apppower', 'full', 0, 'APC3000', '%', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Battery_Charge', 'Battery Charge of UPS', '12', 'battcharge', 'full', 0, 'APC3000', '%', NULL, 98, 0, 1, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('PDU_1_OnOff', 'Set PDU Outlet 1 On/Off (cryo)', '10', 'outlet', 'full', 1, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Runtime', 'Apprx. runtime of UPS', '12', 'runtime', 'full', 0, 'APC3000', 'hours', NULL, 98, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Heater_A', 'Power Heater A', '8', 'Heater', 'full', 2, 'LS336', '%', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Heater_A_OnOff', 'Set Heater A on LS336 on/off', '8', 'Heateronoff', 'full', 2, 'LS336', 'discrete', '0:3;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Ramprate_A', 'Ramprate Heater A', '8', 'Ramprate', 'full', 2, 'LS336', 'K/min', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Setpoint_A', 'Setpoint Heater A', '8', 'Setpoint', 'full', 2, 'LS336', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Pump_OnOff', 'Set Pump to HiCube On/Off', '9', 'pumpgstatn', 'full', 0, 'HiCube80', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Rotation_Speed', 'Rotation Speed of Pump', '9', 'actualspd', 'full', 0, 'HiCube80', 'rpm', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Turbo_OnOff', 'Set Turbo Motor to HiCube Flag', '9', 'motorpump', 'full', 0, 'HiCube80', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Ramprate_A', 'Set Ramprate Heater A', '8', 'Setramprate', 'full', 2, 'LS336', 'K/min', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Temp_A', 'Temperature Input A', '8', 'Temp', 'full', 2, 'LS336', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('dsk_data', 'Free space on /data', '2', 'disk_free', 'full', 0, 'disk_free', '%', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, '/data', NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Heater_A', 'Setpoint Heater A', '8', 'Setsetpoint', 'full', 2, 'LS336', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Power_Output_AVC', 'Power output measured by AVC', '14', 'p', 'full', 0, 'CryoTelGT_AVC', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Reject_Temp', 'Reject Temperature of Cryotel', '14', 'reject', 'full', 0, 'CryoTelGT_AVC', 'C', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('PDU_2_OnOff', 'Set PDU Outlet 2 On/Off (TPG261)', '10', 'outlet', 'full', 2, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Temp_ColdHead', 'Temperature of Cold Head', '14', 'tc', 'full', 0, 'CryoTelGT_AVC', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Power_Output', 'Set power output controlled by AVC', '14', 'setwout', 'full', 0, 'CryoTelGT_AVC', 'W', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Cooler', 'Set the control mode of AVC', '14', 'setcooler', 'full', 0, 'CryoTelGT_AVC', 'discrete', '0:2;Off:Power', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u1', 'VME output voltage u1', '13', 'outvolt', 'full', 1, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u0', 'VME output voltage u0', '13', 'outvolt', 'full', 0, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u0', 'VME output current u0', '13', 'outcurr', 'full', 0, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u3', 'VME output voltage u3', '13', 'outvolt', 'full', 3, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u5', 'VME output voltage u5', '13', 'outvolt', 'full', 5, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u1', 'VME output current u1', '13', 'outcurr', 'full', 1, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u3', 'VME output current u3', '13', 'outcurr', 'full', 3, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u5', 'VME output current u5', '13', 'outcurr', 'full', 5, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Switch_Main', 'Set VME crate on/off', '13', 'setmain', 'full', 0, 'WIENER_VME', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Fan_Temp', 'VME fan air temperature', '13', 'fantemp', 'full', 0, 'WIENER_VME', 'degC', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sc_sensor_types`
--

CREATE TABLE `sc_sensor_types` (
  `num` int(11) NOT NULL,
  `name` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `sc_sensor_types`
--

INSERT INTO `sc_sensor_types` (`num`, `name`) VALUES
(9, 'Pressure'),
(2, 'Sys'),
(10, 'PDU'),
(8, 'Temperature'),
(14, 'CryoCooler'),
(12, 'UPS'),
(13, 'VMECrate');

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Battery_Charge`
--

CREATE TABLE `sc_sens_Battery_Charge` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u0`
--

CREATE TABLE `sc_sens_Current_u0` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u1`
--

CREATE TABLE `sc_sens_Current_u1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u3`
--

CREATE TABLE `sc_sens_Current_u3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u5`
--

CREATE TABLE `sc_sens_Current_u5` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_dsk_data`
--

CREATE TABLE `sc_sens_dsk_data` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_dsk_free`
--

CREATE TABLE `sc_sens_dsk_free` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_dsk_root`
--

CREATE TABLE `sc_sens_dsk_root` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_dsk_shm`
--

CREATE TABLE `sc_sens_dsk_shm` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Fan_Temp`
--

CREATE TABLE `sc_sens_Fan_Temp` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Heater_A`
--

CREATE TABLE `sc_sens_Heater_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Heater_A_OnOff`
--

CREATE TABLE `sc_sens_Heater_A_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Inlet_Current`
--

CREATE TABLE `sc_sens_Inlet_Current` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Inlet_Power`
--

CREATE TABLE `sc_sens_Inlet_Power` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_1_OnOff`
--

CREATE TABLE `sc_sens_PDU_1_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_2_OnOff`
--

CREATE TABLE `sc_sens_PDU_2_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Power_Output`
--

CREATE TABLE `sc_sens_Power_Output` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Power_Output_AVC`
--

CREATE TABLE `sc_sens_Power_Output_AVC` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Pump_OnOff`
--

CREATE TABLE `sc_sens_Pump_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Ramprate_A`
--

CREATE TABLE `sc_sens_Ramprate_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Reject_Temp`
--

CREATE TABLE `sc_sens_Reject_Temp` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Rotation_Speed`
--

CREATE TABLE `sc_sens_Rotation_Speed` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Runtime`
--

CREATE TABLE `sc_sens_Runtime` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Setpoint_A`
--

CREATE TABLE `sc_sens_Setpoint_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Cooler`
--

CREATE TABLE `sc_sens_Set_Cooler` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Heater_A`
--

CREATE TABLE `sc_sens_Set_Heater_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Power_Output`
--

CREATE TABLE `sc_sens_Set_Power_Output` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `sc_sens_Set_Power_Output`
--

INSERT INTO `sc_sens_Set_Power_Output` (`time`, `value`, `rate`) VALUES
(1714773433, 70, 0),
(1714773547, 70, 0),
(1714775324, 70, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Ramprate_A`
--

CREATE TABLE `sc_sens_Set_Ramprate_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Switch_Main`
--

CREATE TABLE `sc_sens_Set_Switch_Main` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_A`
--

CREATE TABLE `sc_sens_Temp_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_ColdHead`
--

CREATE TABLE `sc_sens_Temp_ColdHead` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Turbo_OnOff`
--

CREATE TABLE `sc_sens_Turbo_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u0`
--

CREATE TABLE `sc_sens_Voltage_u0` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u1`
--

CREATE TABLE `sc_sens_Voltage_u1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u3`
--

CREATE TABLE `sc_sens_Voltage_u3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u5`
--

CREATE TABLE `sc_sens_Voltage_u5` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_name` varchar(32) NOT NULL,
  `password` char(32) DEFAULT NULL,
  `full_name` varchar(32) DEFAULT NULL,
  `affiliation` varchar(64) DEFAULT NULL,
  `email` varchar(32) DEFAULT NULL,
  `sms` varchar(32) DEFAULT NULL,
  `phone` varchar(16) DEFAULT NULL,
  `on_call` tinyint(1) NOT NULL DEFAULT 0,
  `shift_status` varchar(16) DEFAULT NULL,
  `privileges` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_name`, `password`, `full_name`, `affiliation`, `email`, `sms`, `phone`, `on_call`, `shift_status`, `privileges`) VALUES
('guest', '084e0343a0486ff05530df6c705c8bb4', 'guest', 'none', '', '', '', 0, 'Off', 'basic,guest'),
('root', '63a9f0ea7bb98050796b649e85481845', 'root', 'root', 'root', 'root', 'root', 0, 'Off', 'admin,basic,config,full,lug,siren'),
('dnorcini', '30d3016ed2b8e3e62f1cdb9b124a1347', 'Danielle Norcini', 'Hopkins', 'dnorcini@jhu.edu', '6105059248@tmomail.net', '610 505 9248', 1, 'Shift Leader', 'admin,basic,config,full');

-- --------------------------------------------------------

--
-- Table structure for table `user_privileges`
--

CREATE TABLE `user_privileges` (
  `u_p_indx` int(11) NOT NULL,
  `name` varchar(16) NOT NULL,
  `allowed_host` varchar(16) NOT NULL DEFAULT 'all'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `user_privileges`
--

INSERT INTO `user_privileges` (`u_p_indx`, `name`, `allowed_host`) VALUES
(1, 'guest', 'all'),
(2, 'basic', 'all'),
(3, 'full', 'all'),
(4, 'admin', 'all'),
(5, 'config', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `user_shift_status`
--

CREATE TABLE `user_shift_status` (
  `status` varchar(16) NOT NULL,
  `can_manage` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `user_shift_status`
--

INSERT INTO `user_shift_status` (`status`, `can_manage`) VALUES
('Off', 0),
('Shift Leader', 1),
('Shift Manager', 1),
('On Shift', 0),
('System Manager', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `globals`
--
ALTER TABLE `globals`
  ADD UNIQUE KEY `names` (`name`);

--
-- Indexes for table `lug_categories`
--
ALTER TABLE `lug_categories`
  ADD PRIMARY KEY (`l_c_indx`);

--
-- Indexes for table `lug_entries`
--
ALTER TABLE `lug_entries`
  ADD PRIMARY KEY (`entry_id`);

--
-- Indexes for table `msg_log`
--
ALTER TABLE `msg_log`
  ADD PRIMARY KEY (`msg_id`);

--
-- Indexes for table `msg_log_types`
--
ALTER TABLE `msg_log_types`
  ADD UNIQUE KEY `name` (`types`);

--
-- Indexes for table `runs`
--
ALTER TABLE `runs`
  ADD UNIQUE KEY `num_2` (`num`),
  ADD KEY `num` (`num`);

--
-- Indexes for table `sc_insts`
--
ALTER TABLE `sc_insts`
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sc_sensors`
--
ALTER TABLE `sc_sensors`
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sc_sensor_types`
--
ALTER TABLE `sc_sensor_types`
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `num` (`num`);

--
-- Indexes for table `sc_sens_Battery_Charge`
--
ALTER TABLE `sc_sens_Battery_Charge`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u0`
--
ALTER TABLE `sc_sens_Current_u0`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u1`
--
ALTER TABLE `sc_sens_Current_u1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u3`
--
ALTER TABLE `sc_sens_Current_u3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u5`
--
ALTER TABLE `sc_sens_Current_u5`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_dsk_data`
--
ALTER TABLE `sc_sens_dsk_data`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_dsk_free`
--
ALTER TABLE `sc_sens_dsk_free`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_dsk_root`
--
ALTER TABLE `sc_sens_dsk_root`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_dsk_shm`
--
ALTER TABLE `sc_sens_dsk_shm`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Fan_Temp`
--
ALTER TABLE `sc_sens_Fan_Temp`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Heater_A`
--
ALTER TABLE `sc_sens_Heater_A`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Heater_A_OnOff`
--
ALTER TABLE `sc_sens_Heater_A_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Inlet_Current`
--
ALTER TABLE `sc_sens_Inlet_Current`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Inlet_Power`
--
ALTER TABLE `sc_sens_Inlet_Power`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_1_OnOff`
--
ALTER TABLE `sc_sens_PDU_1_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_2_OnOff`
--
ALTER TABLE `sc_sens_PDU_2_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Power_Output`
--
ALTER TABLE `sc_sens_Power_Output`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Power_Output_AVC`
--
ALTER TABLE `sc_sens_Power_Output_AVC`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Pump_OnOff`
--
ALTER TABLE `sc_sens_Pump_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Ramprate_A`
--
ALTER TABLE `sc_sens_Ramprate_A`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Reject_Temp`
--
ALTER TABLE `sc_sens_Reject_Temp`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Rotation_Speed`
--
ALTER TABLE `sc_sens_Rotation_Speed`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Runtime`
--
ALTER TABLE `sc_sens_Runtime`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Setpoint_A`
--
ALTER TABLE `sc_sens_Setpoint_A`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Cooler`
--
ALTER TABLE `sc_sens_Set_Cooler`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Heater_A`
--
ALTER TABLE `sc_sens_Set_Heater_A`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Power_Output`
--
ALTER TABLE `sc_sens_Set_Power_Output`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Ramprate_A`
--
ALTER TABLE `sc_sens_Set_Ramprate_A`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Switch_Main`
--
ALTER TABLE `sc_sens_Set_Switch_Main`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Temp_A`
--
ALTER TABLE `sc_sens_Temp_A`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Temp_ColdHead`
--
ALTER TABLE `sc_sens_Temp_ColdHead`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Turbo_OnOff`
--
ALTER TABLE `sc_sens_Turbo_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u0`
--
ALTER TABLE `sc_sens_Voltage_u0`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u1`
--
ALTER TABLE `sc_sens_Voltage_u1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u3`
--
ALTER TABLE `sc_sens_Voltage_u3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u5`
--
ALTER TABLE `sc_sens_Voltage_u5`
  ADD KEY `time` (`time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `username` (`user_name`);

--
-- Indexes for table `user_privileges`
--
ALTER TABLE `user_privileges`
  ADD UNIQUE KEY `index` (`u_p_indx`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `user_shift_status`
--
ALTER TABLE `user_shift_status`
  ADD UNIQUE KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lug_categories`
--
ALTER TABLE `lug_categories`
  MODIFY `l_c_indx` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `lug_entries`
--
ALTER TABLE `lug_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `msg_log`
--
ALTER TABLE `msg_log`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10428677;

--
-- AUTO_INCREMENT for table `runs`
--
ALTER TABLE `runs`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sc_sensor_types`
--
ALTER TABLE `sc_sensor_types`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_privileges`
--
ALTER TABLE `user_privileges`
  MODIFY `u_p_indx` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
