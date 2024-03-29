<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HamletTheVillageBuildingGame implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * HamletTheVillageBuildingGame game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = [
    // The initial state. Please do not modify.
    State::GAME_START => [
        Fsm::NAME => "gameSetup",
        Fsm::TYPE => FsmType::MANAGER,
        Fsm::DESCRIPTION => "",
        Fsm::ACTION => 'stGameSetup',
        Fsm::TRANSITIONS => ['' => State::MOVE_DONKEY]
    ],

    State::MOVE_DONKEY => [
        Fsm::NAME => 'moveDonkey',
        Fsm::TYPE => FsmType::SINGLE_PLAYER,
        Fsm::DESCRIPTION => clienttranslate('${actplayer} must move donkeys'),
        Fsm::OWN_DESCRIPTION => clienttranslate('${you} must move donkeys'),
        Fsm::ARGUMENTS => 'argMoveDonkey',
        Fsm::POSSIBLE_ACTIONS => ['move', 'skip'],
        Fsm::TRANSITIONS => ['move' => State::MOVE_DONKEY, 'end' => State::VILLAGER_ACTION]
    ],

    State::VILLAGER_ACTION => [
        Fsm::NAME => 'villagerAction',
        Fsm::TYPE => FsmType::SINGLE_PLAYER,
        Fsm::DESCRIPTION => clienttranslate('${actplayer} must place a villager'),
        Fsm::OWN_DESCRIPTION => clienttranslate('${you} must place a villager'),
        Fsm::POSSIBLE_ACTIONS => ['work'],
        Fsm::TRANSITIONS => [
            '' => State::VILLAGER_ACTION,
        ]
    ],

    State::PLACE_BUILDING => [
        Fsm::NAME => 'placeBuilding',
        Fsm::TYPE => FsmType::SINGLE_PLAYER,
        Fsm::DESCRIPTION => clienttranslate('${actplayer} must place a building'),
        Fsm::OWN_DESCRIPTION => clienttranslate('${you} must place a building'),
        Fsm::POSSIBLE_ACTIONS => ['build'],
        Fsm::ARGUMENTS => 'argPlaceBuilding',
        Fsm::TRANSITIONS => ['' => State::NEXT_TURN]
    ],

    State::NEXT_TURN => [
        Fsm::NAME => 'nextTurn',
        Fsm::TYPE => FsmType::GAME,
        Fsm::ACTION => 'stNextTurn',
        Fsm::TRANSITIONS => ['next' => State::MOVE_DONKEY, 'end' => State::NEXT_ROUND],
    ],

    State::NEXT_ROUND => [
        Fsm::NAME => 'nextRound',
        Fsm::TYPE => FsmType::GAME,
        Fsm::ACTION => 'stNextRound',
        Fsm::TRANSITIONS => ['next' => State::MOVE_DONKEY, 'end' => State::NEXT_ROUND],
    ],

    State::GAME_END => [
        Fsm::NAME => 'gameEnd',
        Fsm::TYPE => FsmType::SINGLE_PLAYER,
        Fsm::DESCRIPTION => clienttranslate('End of game'),
        Fsm::ACTION => 'stGameEnd',
        Fsm::ARGUMENTS => 'argGameEnd'
    ]
];



