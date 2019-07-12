CREATE TABLE IF NOT EXISTS `patterns` (`pattern` varchar(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `results` (`word` varchar(2500) NOT NULL, `result_for` varchar(2500) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `valid_patterns` (`pattern` varchar(2500) NOT NULL,`valid_for` varchar(2500) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `words` (`word` varchar(250) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `patterns` ADD UNIQUE KEY `pattern` (`pattern`);
ALTER TABLE `results` ADD UNIQUE KEY `word` (`word`), ADD UNIQUE KEY `result_for` (`result_for`);
ALTER TABLE `words` ADD UNIQUE KEY `word` (`word`);
