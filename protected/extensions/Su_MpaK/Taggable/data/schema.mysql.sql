-- testdrive application example of User table
CREATE TABLE IF NOT EXISTS `tbl_user` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `username` VARCHAR(128) NOT NULL ,
    `password` VARCHAR(128) NOT NULL ,
    `email` VARCHAR(128) NOT NULL ,
    PRIMARY KEY (`id`)
);

-- tesdrive application example User rows
INSERT INTO tbl_user (username, password, email) VALUES ('test1', 'pass1', 'test1@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test2', 'pass2', 'test2@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test3', 'pass3', 'test3@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test4', 'pass4', 'test4@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test5', 'pass5', 'test5@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test6', 'pass6', 'test6@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test7', 'pass7', 'test7@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test8', 'pass8', 'test8@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test9', 'pass9', 'test9@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test10', 'pass10', 'test10@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test11', 'pass11', 'test11@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test12', 'pass12', 'test12@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test13', 'pass13', 'test13@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test14', 'pass14', 'test14@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test15', 'pass15', 'test15@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test16', 'pass16', 'test16@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test17', 'pass17', 'test17@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test18', 'pass18', 'test18@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test19', 'pass19', 'test19@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test20', 'pass20', 'test20@example.com');
INSERT INTO tbl_user (username, password, email) VALUES ('test21', 'pass21', 'test21@example.com');


CREATE  TABLE `tag` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
    `title` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`id`) ,
    UNIQUE INDEX `title_UNIQUE` (`title` ASC) 
);


CREATE  TABLE `tbl_user_tag` (
    `tagId` INT UNSIGNED NOT NULL ,
    `tbl_userId` INT NOT NULL ,
    INDEX `tbl_user_tag_tag_idx` (`tagId` ASC) ,
    INDEX `tbl_user_tag_tbl_user_idx` (`tbl_userId` ASC) ,
    CONSTRAINT `tbl_user_tag_tag`
        FOREIGN KEY (`tagId` )
        REFERENCES `taggable`.`tag` (`id` )
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    CONSTRAINT `tbl_user_tag_tbl_user`
        FOREIGN KEY (`tbl_userId` )
        REFERENCES `taggable`.`tbl_user` (`id` )
        ON DELETE CASCADE
        ON UPDATE NO ACTION
);

INSERT INTO tbl_user_tag VALUES ( 1, 1 );