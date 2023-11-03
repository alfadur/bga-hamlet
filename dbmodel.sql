
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- HamletTheVillageBuildingGame implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `buildings`(
    `buildilng_id` TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY(`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE IF NOT EXISTS `board`(
    `x` TINYINT NOT NULL,
    `y` TINYINT NOT NULL,
    `z` TINYINT NOT NULL,
    `edge_x` TINYINT NOT NULL,
    `edge_y` TINYINT NOT NULL,
    `edge_z` TINYINT NOT NULL,
    `building_id` TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY(`x`, `y`, `z`),
    FOREIGN KEY(`building_id`) REFERENCES `buildings`(`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
