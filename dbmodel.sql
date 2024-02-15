
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- HamletTheVillageBuildingGame implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

ALTER TABLE `player` ADD `coins` TINYINT UNSIGNED NOT NULL DEFAULT 2;

CREATE TABLE IF NOT EXISTS `building`(
    `building_id` TINYINT UNSIGNED NOT NULL,
    `x` TINYINT NOT NULL,
    `y` TINYINT NOT NULL,
    `z` TINYINT NOT NULL,
    `orientation` TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY(`building_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `adjacency`(
    `building1_id` TINYINT NOT NULL,
    `building2_id` TINYINT NOT NULL,
    `road` TINYINT NOT NULL,
    `owner_id` INTEGER UNSIGNED NULL,
    PRIMARY KEY(`building1_id`, `building2_id`),
    FOREIGN KEY(`owner_id`) REFERENCES `player`(`player_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `product`(
    `building_id` TINYINT UNSIGNED NOT NULL,
    `owner_id` INTEGER UNSIGNED NULL,
    `product_type` TINYINT UNSIGNED NOT NULL,
    `count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY(`building_id`, `product_type`),
    FOREIGN KEY(`building_id`) REFERENCES `building`(`building_id`),
    FOREIGN KEY(`owner_id`) REFERENCES `player`(`player_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `board`(
    `x` TINYINT NOT NULL,
    `y` TINYINT NOT NULL,
    `z` TINYINT NOT NULL,
    `edge_x` TINYINT NOT NULL,
    `edge_y` TINYINT NOT NULL,
    `edge_z` TINYINT NOT NULL,
    `building_id` TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY(`x`, `y`, `z`),
    FOREIGN KEY(`building_id`) REFERENCES `building`(`building_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `donkey`(
    `donkey_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `building_id` TINYINT UNSIGNED NOT NULL,
    `player_id` INTEGER UNSIGNED NOT NULL,
    PRIMARY KEY(`donkey_id`),
    FOREIGN KEY(`player_id`) REFERENCES `player`(`player_id`),
    FOREIGN KEY(`building_id`) REFERENCES `building`(`building_id`)
) ENGINE = InnoDB DEFAULT CHAR SET = utf8 AUTO_INCREMENT = 1;

