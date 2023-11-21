<?php

interface Fsm {
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const OWN_DESCRIPTION = 'descriptionmyturn';
    const TYPE = 'type';
    const ACTION = 'action';
    const TRANSITIONS = 'transitions';
    const PROGRESSION = 'updateGameProgression';
    const POSSIBLE_ACTIONS = 'possibleactions';
    const ARGUMENTS = 'args';
}

interface FsmType {
    const MANAGER = 'manager';
    const GAME = 'game';
    const SINGLE_PLAYER = 'activeplayer';
    const MULTIPLE_PLAYERS = 'multipleactiveplayer';
}

interface State {
    const GAME_START = 1;

    const NEXT_TURN = 2;
    const MOVE_DONKEY = 3;
    const PLACE_BUILDING = 4;
    const VILLAGER_ACTION = 5;

    const GAME_END = 99;
}

interface Globals
{
    const MOVED_DONKEYS = 'movedDonkeys';
    const MOVED_DONKEYS_ID = 10;
    const CURRENT_BUILDING = 'currentBuilding';
    const CURRENT_BUILDING_ID = 11;
}

interface Building
{
    const CHURCH = 0;
    const WOODCUTTER = 1;
    const QUARRY = 2;
    const FARM = 3;
    const MARKET = 4;
    const TOWN_HALL = 5;
    const STONE_MASON = 6;
    const SAWMILL = 7;
    const TRADE_POST = 8;
    const FLOUR_MULL = 9;
    const BARN = 10;
    const WAREHOUSE = 11;

    const SETUP = [
        [self::CHURCH, [0, 0, 0], 0],
        [self::WOODCUTTER, [-3, 2, 1], 2],
        [self::QUARRY, [0, -1, 2], 1],
        [self::FARM, [3, 1, -3], 3],
        [self::MARKET, [4, 0, -4], 0],
        [self::TOWN_HALL, [0, 3, -3], 0]
    ];
}

interface Edge {
    const NONE = 0;
    const ROAD = 1;
    const FOREST = 2;
    const MOUNTAIN = 3;
}

const BUILDING_CELLS = [
    Building::CHURCH => [
        0, 0, 0, Edge::NONE, Edge::NONE, Edge::ROAD,
        1, 0, 0, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        1, 0, -1, Edge::FOREST, Edge::NONE, Edge::NONE,

        -2, 1, 1, Edge::NONE, Edge::NONE, Edge::FOREST,
        -1, 1, 1, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        -1, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        1, 1, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        1, 1, -2, Edge::NONE, Edge::NONE, Edge::NONE,
        2, 1, -2, Edge::NONE, Edge::FOREST, Edge::NONE,
        2, 1, -3, Edge::ROAD, Edge::NONE, Edge::NONE,

        -2, 2, 1, Edge::ROAD, Edge::NONE, Edge::NONE,
        -2, 2, 0, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        -1, 2, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        -1, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -2, Edge::NONE, Edge::NONE, Edge::NONE,
        1, 2, -2, Edge::NONE, Edge::NONE, Edge::NONE,
        1, 2, -3, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        2, 2, -3, Edge::NONE, Edge::NONE, Edge::ROAD,

        -1, 3, -1, Edge::FOREST, Edge::NONE, Edge::NONE,
        -1, 3, -2, Edge::NONE, Edge::FOREST, Edge::NONE,
        0, 3, -2, Edge::NONE, Edge::NONE, Edge::ROAD],
    Building::TOWN_HALL => [
        0, 0, 0, Edge::NONE, Edge::NONE, Edge::ROAD,
        1, 0, 0, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        1, 0, -1, Edge::FOREST, Edge::NONE, Edge::NONE,

        0, 1, 0, Edge::FOREST, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        1, 1, -1, Edge::NONE, Edge::NONE, Edge::FOREST,

        -1, 2, -1, Edge::NONE, Edge::NONE, Edge::FOREST,
        0, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -2, Edge::MOUNTAIN, Edge::NONE, Edge::NONE,

        -1, 3, -1, Edge::MOUNTAIN, Edge::NONE, Edge::NONE,
        -1, 3, -2, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        0, 3, -2, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
    ],
    Building::WOODCUTTER => [
        0, 0, 0, Edge::FOREST, Edge::NONE, Edge::ROAD,

        -1, 1, 0, Edge::NONE, Edge::NONE, Edge::FOREST,
        0, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::FOREST, Edge::NONE, Edge::NONE,

        -1, 2, 0, Edge::FOREST, Edge::NONE, Edge::NONE,
        -1, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -1, Edge::NONE, Edge::NONE, Edge::FOREST,

        -1, 3, -1, Edge::FOREST, Edge::NONE, Edge::FOREST,
    ],
    Building::QUARRY => [
        0, 0, 0, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
        1, 0, 0, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        1, 0, -1, Edge::ROAD, Edge::NONE, Edge::NONE,

        0, 1, 0, Edge::MOUNTAIN, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        1, 1, -1, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
    ],
    Building::FARM => [
        0, 0, 0, Edge::ROAD, Edge::NONE, Edge::FOREST,

        -1, 1, 0, Edge::NONE, Edge::FOREST, Edge::FOREST,
        0, 1, 0, Edge::NONE, Edge::FOREST, Edge::NONE,
        0, 1, -1, Edge::FOREST, Edge::FOREST, Edge::NONE,
    ],
    Building::MARKET => [
        0, 0, 0, Edge::ROAD, Edge::NONE, Edge::FOREST,

        -1, 1, 0, Edge::NONE, Edge::NONE, Edge::FOREST,
        0, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::FOREST, Edge::NONE, Edge::NONE,

        -2, 2, 0, Edge::NONE, Edge::FOREST, Edge::ROAD,
        -1, 2, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        -1, 2, -1, Edge::NONE, Edge::FOREST, Edge::NONE,
        0, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -2, Edge::FOREST, Edge::FOREST, Edge::NONE
    ],
    Building::STONE_MASON => [
        0, 0, 0, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
        1, 0, 0, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        1, 0, -1, Edge::ROAD, Edge::NONE, Edge::NONE,

        0, 1, 0, Edge::ROAD, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        1, 1, -1, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,

        -1, 2, -1, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
        0, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -2, Edge::MOUNTAIN, Edge::NONE, Edge::NONE,

        -1, 3, -1, Edge::MOUNTAIN, Edge::NONE, Edge::NONE,
        -1, 3, -2, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        0, 3, -2, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
    ],
    Building::SAWMILL => [
        0, 0, 0, Edge::ROAD, Edge::NONE, Edge::FOREST,

        -1, 1, 0, Edge::NONE, Edge::NONE, Edge::FOREST,
        0, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::FOREST, Edge::FOREST, Edge::NONE,

        -2, 2, 0, Edge::NONE, Edge::NONE, Edge::FOREST,
        -1, 2, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        -1, 2, -1, Edge::ROAD, Edge::NONE, Edge::NONE,

        -2, 3, 0, Edge::FOREST, Edge::NONE, Edge::NONE,
        -2, 3, -1, Edge::NONE, Edge::FOREST, Edge::NONE,
        -1, 3, -1, Edge::NONE, Edge::NONE, Edge::FOREST,
    ],
    Building::TRADE_POST => [
        0, 0, 0, Edge::MOUNTAIN, Edge::NONE, Edge::MOUNTAIN,

        -1, 1, 0, Edge::NONE, Edge::FOREST, Edge::FOREST,
        0, 1, 0, Edge::NONE, Edge::FOREST, Edge::NONE,
        0, 1, -1, Edge::MOUNTAIN, Edge::FOREST, Edge::NONE,
    ],
    Building::FLOUR_MULL => [
        0, 0, 0, Edge::FOREST, Edge::NONE, Edge::ROAD,

        -1, 1, 0, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
        0, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::FOREST, Edge::NONE, Edge::NONE,

        -1, 2, 0, Edge::MOUNTAIN, Edge::NONE, Edge::NONE,
        -1, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -1, Edge::NONE, Edge::NONE, Edge::FOREST,

        -1, 3, -1, Edge::ROAD, Edge::NONE, Edge::MOUNTAIN,
    ],
    Building::BARN => [
        0, 0, 0, Edge::NONE, Edge::NONE, Edge::MOUNTAIN,
        1, 0, 0, Edge::NONE, Edge::MOUNTAIN, Edge::NONE,
        1, 0, -1, Edge::FOREST, Edge::NONE, Edge::NONE,

        0, 1, 0, Edge::ROAD, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::NONE, Edge::FOREST, Edge::NONE,
        1, 1, -1, Edge::NONE, Edge::NONE, Edge::ROAD,
    ],
    Building::WAREHOUSE => [
        0, 0, 0, Edge::FOREST, Edge::NONE, Edge::FOREST,

        -1, 1, 0, Edge::NONE, Edge::NONE, Edge::FOREST,
        0, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 1, -1, Edge::FOREST, Edge::NONE, Edge::NONE,

        -1, 2, 0, Edge::MOUNTAIN, Edge::NONE, Edge::NONE,
        -1, 2, -1, Edge::NONE, Edge::NONE, Edge::NONE,
        0, 2, -1, Edge::NONE, Edge::NONE, Edge::ROAD,

        -1, 3, -1, Edge::ROAD, Edge::NONE, Edge::MOUNTAIN,
    ]
];