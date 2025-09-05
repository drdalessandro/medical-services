--
-- Table structure for table `form_cardiovascular_risk_hearts`
--

CREATE TABLE IF NOT EXISTS `form_cardiovascular_risk_hearts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `smoker` varchar(5) DEFAULT NULL,
  `systolic_pressure` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `cv_disease_history` varchar(5) DEFAULT NULL,
  `chronic_kidney_disease` varchar(5) DEFAULT NULL,
  `diabetes_mellitus` varchar(5) DEFAULT NULL,
  `know_cholesterol` varchar(5) DEFAULT NULL,
  `total_cholesterol` decimal(5,2) DEFAULT NULL,
  `calculated_risk` decimal(5,2) DEFAULT NULL,
  `risk_category` varchar(50) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
