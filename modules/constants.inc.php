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

    const MOVE_DONKEY = 2;
    const VILLAGER_ACTION = 3;
    const PLACE_BUILDING = 4;
    const PLACE_ROAD = 5;
    const SELL_GOODS = 6;
    const NEXT_TURN = 7;
    const NEXT_ROUND = 8;

    const GAME_END = 99;
}

interface Globals
{
    const ROUND_NUMBER = 'roundNumber';
    const ROUND_NUMBER_ID = 10;
    const MOVED_DONKEYS = 'movedDonkeys';
    const MOVED_DONKEYS_ID = 11;
    const CURRENT_BUILDING = 'currentBuilding';
    const CURRENT_BUILDING_ID = 12;
}

const MAX_DONKEYS = 6;
const DONKEY_BITS = 5; //npow2(MAX_DONKEYS * 4)

interface Building
{
    const CHURCH = 0;
    const MARKET = 1;
    const SHRINE = 2;
    const FARM = 3;
    const TRADE_POST = 4;
    const MASTER_STONEMASON = 5;
    const WAREHOUSE = 6;
    const WOODCUTTER = 7;
    const FLOUR_MILL = 8;
    const SMALL_WOODLAND = 9;
    const LARGE_WOODLAND = 10;
    const WINDMILL = 11;
    const TAVERN = 12;
    const DAIRY_FARM = 13;
    const OUTPOST_1 = 14;
    const OUTPOST_2 = 15;
    const STABLES = 16;
    const COW_CONSERVATORY = 17;
    const SAWMILL = 18;
    const STRAIGHT_BARN = 19;
    const CURVED_BARN = 20;
    const QUARRY = 21;
    const FOREST_POND = 22;
    const MOUNTAIN_POND = 23;
    const FARRIER = 24;
    const SMALL_MOUNTAIN_RANGE = 25;
    const LARGE_MOUNTAIN_RANGE = 26;
    const SQUARE = 27;
    const MONUMENT = 28;
    const STONEMASON = 29;
    const LUMBER_MILL = 30;
    const TOWN_HALL = 31;

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

interface BuildingShape {
    const CHURCH = 0;
    const LARGE_TRIANGLE = 1;
    const SMALL_TRIANGLE = 2;
    const DIAMOND = 3;
    const CUT_DIAMOND = 4;
    const FLASK = 5;
    const FLAG = 6;
    const HEX = 7;
    const HEX_HALF = 8;
    const DOUBLE_HEX = 9;
}

/*x, y, z, hasXEdge, hasYEdge, hasZEdge*/
const SHAPES = [
    BUildingShape::CHURCH => [
        0, 0, 0, 0, 0, 1,
        1, 0, 0, 0, 1, 0,
        1, 0, -1, 1, 0, 0,

        -2, 1, 1, 0, 0, 1,
        -1, 1, 1, 0, 1, 0,
        -1, 1, 0, 0, 0, 0,
        0, 1, 0, 0, 0, 0,
        0, 1, -1, 0, 0, 0,
        1, 1, -1, 0, 0, 0,
        1, 1, -2, 0, 0, 0,
        2, 1, -2, 0, 1, 0,
        2, 1, -3, 1, 0, 0,

        -2, 2, 1, 1, 0, 0,
        -2, 2, 0, 0, 1, 0,
        -1, 2, 0, 0, 0, 0,
        -1, 2, -1, 0, 0, 0,
        0, 2, -1, 0, 0, 0,
        0, 2, -2, 0, 0, 0,
        1, 2, -2, 0, 0, 0,
        1, 2, -3, 0, 1, 0,
        2, 2, -3, 0, 0, 1,

        -1, 3, -1, 1, 0, 0,
        -1, 3, -2, 0, 1, 0,
        0, 3, -2, 0, 0, 1],
    BuildingShape::LARGE_TRIANGLE => [
        0, 0, 0, 1, 0, 1,

        -1, 1, 0, 0, 0, 1,
        0, 1, 0, 0, 0, 0,
        0, 1, -1, 1, 0, 0,

        -2, 2, 0, 0, 1, 1,
        -1, 2, 0, 0, 0, 0,
        -1, 2, -1, 0, 1, 0,
        0, 2, -1, 0, 0, 0,
        0, 2, -2, 1, 1, 0],
    BuildingShape::SMALL_TRIANGLE => [
        0, 0, 0, 1, 0, 1,

        -1, 1, 0, 0, 1, 1,
        0, 1, 0, 0, 0, 0,
        0, 1, -1, 1, 1, 0],
    BuildingShape::DIAMOND => [
        0, 0, 0, 1, 0, 1,

        -1, 1, 0, 0, 0, 1,
        0, 1, 0, 0, 0, 0,
        0, 1, -1, 1, 0, 0,

        -1, 2, 0, 1, 0, 0,
        -1, 2, -1, 0, 0, 0,
        0, 2, -1, 0, 0, 1,

        -1, 3, -1, 1, 0, 1],
    BuildingShape::CUT_DIAMOND => [
        0, 0, 0, 1, 0, 1,

        -1, 1, 0, 0, 0, 1,
        0, 1, 0, 0, 0, 0,
        0, 1, -1, 1, 0, 0,

        -1, 2, 0, 1, 0, 0,
        -1, 2, -1, 0, 1, 0,
        0, 2, -1, 0, 0, 1],
    BuildingShape::FLASK => [
        0, 0, 0, 0, 0, 1,
        1, 0, 0, 0, 1, 1,

        -1, -1, 0, 0, 0, 1,
        0, -1, 0, 0, 0, 0,
        0, -1, -1, 1, 0, 0,

        -1, -2, 0, 1, 0, 0,
        -1, -2, -1, 0, 1, 0,
        0, -2, -1, 0, 0, 1],
    BuildingShape::FLAG => [
        0, 0, 0, 1, 0, 1,

        -1, 1, 0, 0, 0, 1,
        0, 1, 0, 0, 0, 0,
        0, 1, -1, 1, 1, 0,

        -2, 2, 0, 0, 0, 1,
        -1, 2, 0, 0, 0, 0,
        -1, 2, -1, 1, 0, 0,

        -2, 3, 0, 1, 0, 0,
        -2, 3, -1, 0, 1, 0,
        -1, 3, -1, 0, 0, 1],
    BuildingShape::HEX => [
        0, 0, 0, 0, 0, 1,
        1, 0, 0, 0, 1, 0,
        1, 0, -1, 1, 0, 0,

        0, 1, 0, 1, 0, 0,
        0, 1, -1, 0, 1, 0,
        1, 1, -1, 0, 0, 1],
    BuildingShape::HEX_HALF => [
        0, 0, 0, 0, 0, 1,
        1, 0, 0, 0, 1, 0,
        1, 0, -1, 1, 0, 0,

        0, 1, 0, 1, 0, 0,
        0, 1, -1, 0, 0, 0,
        1, 1, -1, 0, 0, 1,

        -1, 2, -1, 0, 0, 1,
        0, 2, -1, 0, 1, 0,
        0, 2, -2, 1, 0, 0],
    BuildingShape::DOUBLE_HEX => [
        0, 0, 0, 0, 0, 1,
        1, 0, 0, 0, 1, 0,
        1, 0, -1, 1, 0, 0,

        0, 1, 0, 1, 0, 0,
        0, 1, -1, 0, 0, 0,
        1, 1, -1, 0, 0, 1,

        -1, 2, -1, 0, 0, 1,
        0, 2, -1, 0, 0, 0,
        0, 2, -2, 1, 0, 0,

        -1, 3, -1, 1, 0, 0,
        -1, 3, -2, 0, 1, 0,
        0, 3, -2, 0, 0, 1]
];

const BUILDING_PALETTES = [
    BUilding::CHURCH => [BuildingShape::CHURCH, [
        Edge::ROAD, Edge::MOUNTAIN, Edge::FOREST,
        Edge::FOREST, Edge::MOUNTAIN, Edge::FOREST, Edge::ROAD,
        Edge::ROAD, Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::ROAD,
        Edge::FOREST, Edge::FOREST, Edge::ROAD
    ]],
    Building::MARKET => [BuildingShape::LARGE_TRIANGLE, [
        Edge::ROAD, Edge::FOREST,
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::ROAD, Edge::FOREST, Edge::FOREST, Edge::FOREST
    ]],
    Building::SHRINE => [BuildingShape::SMALL_TRIANGLE, [
        Edge::FOREST, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::FOREST, Edge::FOREST
    ]],
    Building::FARM => [BuildingShape::SMALL_TRIANGLE, [
        Edge::ROAD, Edge::FOREST,
        Edge::FOREST, Edge::FOREST, Edge::FOREST, Edge::FOREST
    ]],
    Building::TRADE_POST => [BuildingShape::SMALL_TRIANGLE, [
        Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::FOREST, Edge::FOREST, Edge::MOUNTAIN, Edge::FOREST
    ]],
    Building::MASTER_STONEMASON => [BuildingShape::DIAMOND, [
        Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::ROAD, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::ROAD
    ]],
    Building::WAREHOUSE => [BuildingShape::DIAMOND, [
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST,
        Edge::MOUNTAIN, Edge::ROAD,
        Edge::ROAD, Edge::MOUNTAIN
    ]],
    Building::WOODCUTTER => [BuildingShape::DIAMOND, [
        Edge::FOREST, Edge::ROAD,
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST
    ]],
    Building::FLOUR_MILL => [BuildingShape::DIAMOND, [
        Edge::FOREST, Edge::ROAD,
        Edge::MOUNTAIN, Edge::FOREST,
        Edge::MOUNTAIN, Edge::FOREST,
        Edge::ROAD, Edge::MOUNTAIN
    ]],
    Building::SMALL_WOODLAND => [BuildingShape::CUT_DIAMOND, [
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST, Edge::FOREST
    ]],
    Building::LARGE_WOODLAND => [BuildingShape::CUT_DIAMOND, [
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST, Edge::FOREST
    ]],
    Building::WINDMILL => [BuildingShape::CUT_DIAMOND, [
        Edge::ROAD, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::FOREST,
        Edge::ROAD, Edge::MOUNTAIN, Edge::FOREST
    ]],
    Building::TAVERN => [BuildingShape::FLAG, [
        Edge::FOREST, Edge::FOREST, Edge::ROAD,
        Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::ROAD, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::DAIRY_FARM => [BuildingShape::FLASK, [
        Edge::FOREST, Edge::FOREST, Edge::MOUNTAIN,
        Edge::ROAD, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::ROAD
    ]],
    Building::OUTPOST_1 => [BuildingShape::FLASK, [
        Edge::MOUNTAIN, Edge::ROAD, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::OUTPOST_2 => [BuildingShape::FLASK, [
        Edge::MOUNTAIN, Edge::ROAD, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::STABLES => [BuildingShape::FLAG, [
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::ROAD, Edge::FOREST,
        Edge::ROAD, Edge::FOREST,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN,
    ]],
    Building::COW_CONSERVATORY => [BuildingShape::FLAG, [
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::ROAD, Edge::FOREST,
        Edge::ROAD, Edge::FOREST,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN,
    ]],
    Building::SAWMILL => [BuildingShape::FLAG, [
        Edge::ROAD, Edge::FOREST,
        Edge::FOREST, Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::ROAD,
        Edge::FOREST, Edge::FOREST, Edge::FOREST,
    ]],
    Building::STRAIGHT_BARN => [BuildingShape::HEX, [
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::FOREST,
        Edge::ROAD, Edge::FOREST, Edge::ROAD
    ]],
    Building::CURVED_BARN => [BuildingShape::HEX, [
        Edge::FOREST, Edge::FOREST, Edge::ROAD,
        Edge::ROAD, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::QUARRY => [BuildingShape::HEX, [
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::ROAD,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::FOREST_POND => [BuildingShape::HEX, [
        Edge::FOREST, Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST, Edge::FOREST
    ]],
    Building::MOUNTAIN_POND => [BuildingShape::HEX, [
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::FARRIER => [BuildingShape::HEX, [
        Edge::ROAD, Edge::FOREST, Edge::MOUNTAIN,
        Edge::ROAD, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::SMALL_MOUNTAIN_RANGE => [BuildingShape::HEX, [
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::LARGE_MOUNTAIN_RANGE => [BuildingShape::HEX, [
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::SQUARE => [BuildingShape::HEX_HALF, [
        Edge::MOUNTAIN, Edge::ROAD, Edge::FOREST,
        Edge::MOUNTAIN, Edge::FOREST,
        Edge::ROAD, Edge::MOUNTAIN, Edge::FOREST, Edge::ROAD
    ]],
    Building::MONUMENT => [BuildingShape::HEX_HALF, [
        Edge::FOREST, Edge::ROAD, Edge::MOUNTAIN,
        Edge::FOREST, Edge::MOUNTAIN,
        Edge::ROAD, Edge::FOREST, Edge::MOUNTAIN, Edge::ROAD
    ]],
    Building::STONEMASON => [BuildingShape::DOUBLE_HEX, [
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::ROAD,
        Edge::ROAD, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]],
    Building::LUMBER_MILL => [BuildingShape::DOUBLE_HEX, [
        Edge::FOREST, Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::FOREST,
        Edge::ROAD, Edge::FOREST,
        Edge::FOREST, Edge::FOREST, Edge::ROAD
    ]],
    Building::TOWN_HALL => [BuildingShape::DOUBLE_HEX, [
        Edge::ROAD, Edge::MOUNTAIN, Edge::FOREST,
        Edge::FOREST, Edge::FOREST,
        Edge::FOREST, Edge::MOUNTAIN,
        Edge::MOUNTAIN, Edge::MOUNTAIN, Edge::MOUNTAIN
    ]]
];