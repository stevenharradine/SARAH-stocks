-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 30, 2015 at 09:47 PM
-- Server version: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `sarah`
--

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE IF NOT EXISTS `stocks` (
  `STOCK_ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `ticker` text NOT NULL,
  PRIMARY KEY (`STOCK_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `stocks_history`
--

CREATE TABLE IF NOT EXISTS `stocks_history` (
  `STOCK_HISTORY_ID` int(11) NOT NULL AUTO_INCREMENT,
  `stock` text NOT NULL,
  `ticker` text NOT NULL,
  `price` double NOT NULL,
  `change_points` float NOT NULL,
  `change_percent` float NOT NULL,
  `date` text NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`STOCK_HISTORY_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8501 ;

