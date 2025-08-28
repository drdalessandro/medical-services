--
-- Table structure for table `form_tobacco_pack_year`
--

CREATE TABLE IF NOT EXISTS `form_tobacco_pack_year` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `cigarettes_per_day` int(11) DEFAULT NULL,
  `years_smoking` int(11) DEFAULT NULL,
  `pack_years` decimal(10,2) DEFAULT NULL,
  `risk_level` varchar(50) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
