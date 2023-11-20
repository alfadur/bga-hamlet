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
}

interface Building
{
    const CHURCH = 0;
    const TOWN_HALL = 1;
    const WOODCUTTER = 2;
    const QUARRY = 3;
    const FARM = 4;
    const MARKET = 5;

    const SETUP = [
        self::TOWN_HALL,
        self::WOODCUTTER,
        self::QUARRY,
        self::FARM,
        self::MARKET
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
        0, 1, 0, Edge::NONE, Edge::NONE, Edge::NONE,
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
];