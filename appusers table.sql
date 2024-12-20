use usjr;

CREATE TABLE `usjr`.`appusers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);
select * from `usjr`.`appusers`;
select * from `usjr`.`students`;
select * from `usjr`.`departments`;
select * from `usjr`.`colleges`;
select * from `usjr`.`programs`;


