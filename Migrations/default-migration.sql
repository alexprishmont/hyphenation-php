CREATE TABLE IF NOT EXISTS `patterns` (
                            `id` int(11) NOT NULL,
                            `pattern` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `results` (
                           `id` int(11) NOT NULL,
                           `result` varchar(250) DEFAULT NULL,
                           `wordID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `valid_patterns` (
                                  `patternID` int(11) NOT NULL,
                                  `wordID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `words` (
                         `id` int(11) NOT NULL,
                         `word` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--

ALTER TABLE `patterns`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `results`
    ADD PRIMARY KEY (`id`),
    ADD KEY `wordID` (`wordID`);

ALTER TABLE `valid_patterns`
    ADD KEY `wordID` (`wordID`),
    ADD KEY `patternID` (`patternID`);

ALTER TABLE `words`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `patterns`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4448;

ALTER TABLE `results`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `words`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `results`
    ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`wordID`) REFERENCES `words` (`id`);

ALTER TABLE `valid_patterns`
    ADD CONSTRAINT `valid_patterns_ibfk_1` FOREIGN KEY (`wordID`) REFERENCES `words` (`id`),
    ADD CONSTRAINT `valid_patterns_ibfk_2` FOREIGN KEY (`patternID`) REFERENCES `patterns` (`id`);
