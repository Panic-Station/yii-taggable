-- testdrive application example of User table
CREATE TABLE IF NOT EXISTS `tbl_user` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `username` VARCHAR(128) NOT NULL ,
    `password` VARCHAR(128) NOT NULL ,
    `email` VARCHAR(128) NOT NULL ,
    PRIMARY KEY (`id`)
);

CREATE  TABLE `tag` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
    `title` VARCHAR(255) NOT NULL ,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 0 ,
    PRIMARY KEY (`id`) ,
    UNIQUE INDEX `title_UNIQUE` (`title` ASC) 
);

CREATE  TABLE `user_tag` (
    `tagId` INT UNSIGNED NOT NULL ,
    `userId` INT NOT NULL ,
    INDEX `user_tag_tag_idx` (`tagId` ASC) ,
    INDEX `user_tag_user_idx` (`userId` ASC) ,
    CONSTRAINT `user_tag_tag`
        FOREIGN KEY (`tagId` )
        REFERENCES `taggable`.`tag` (`id` )
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    CONSTRAINT `user_tag_user`
        FOREIGN KEY (`userId` )
        REFERENCES `taggable`.`tbl_user` (`id` )
        ON DELETE CASCADE
        ON UPDATE NO ACTION
);


