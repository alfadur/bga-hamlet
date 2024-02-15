<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * HamletTheVillageBuildingGame implementation : © <Your name here> <Your email address here>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
    */


require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');
require_once('modules/constants.inc.php');

class HamletTheVillageBuildingGame extends Table
{
	function __construct( )
	{
        parent::__construct();
        
        self::initGameStateLabels( [
            Globals::ROUND_NUMBER => Globals::ROUND_NUMBER_ID,
            Globals::MOVED_DONKEYS => Globals::MOVED_DONKEYS_ID,
            Globals::CURRENT_BUILDING => Globals::CURRENT_BUILDING_ID
        ]);
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "hamletthevillagebuildinggame";
    }	

    protected function setupNewGame($players, $options = [])
    {    
        $data = self::getGameinfos();
        $defaultColors = $data['player_colors'];
        $playerValues = [];

        foreach ($players as $playerId => $player) {
            $color = array_shift($defaultColors);

            $name = addslashes($player['player_name']);
            $avatar = addslashes($player['player_avatar']);
            $playerValues[] = "('$playerId','$color','$player[player_canal]','$name','$avatar')";
        }

        $args = implode(',', $playerValues);
        $query = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES $args";
        self::DbQuery($query);

        self::reattributeColorsBasedOnPreferences($players, $data['player_colors']);
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        self::setGameStateInitialValue(Globals::CURRENT_BUILDING, Building::TRADE_POST);

        self::setupBuildings();
        self::setupPlayers($players);

        $this->activeNextPlayer();
    }

    static function getBuildingData(int $buildingId): array {
        [$shapeId, $palette] = BUILDING_PALETTES[$buildingId];

        $buildingData = SHAPES[$shapeId];
        $size = count($buildingData) / 6;

        $paletteIndex = 0;
        for ($i = 0; $i < $size; ++$i) {
            for ($j = 0; $j < 3; ++$j) {
                $index = $i * 6 + 3 + $j;
                $buildingData[$index] = $buildingData[$index] ?
                    $palette[$paletteIndex++] : Edge::NONE;
            }
        }

        return $buildingData;
    }

    static function placeBuilding(int $buildingId, array $position, int $orientation, bool $setup = false): array
    {
        [$x, $y, $z] = $position;

        if ($x + $y + $z !== ($orientation & 0b1)) {
            throw new BgaUserException('Invalid orientation');
        }

        $query = <<<EOF
            INSERT INTO building(building_id, x, y, z, orientation) 
            VALUES ($buildingId, $x, $y, $z, $orientation)
            EOF;
        self::DbQuery($query);

        $sign = 1 - ($orientation & 0b1) * 2;
        $indices = [
            $orientation % 3,
            ($orientation + 1) % 3,
            ($orientation + 2) % 3
        ];

        $buildingData = self::getBuildingData($buildingId);
        $size = count($buildingData) / 6;

        $values = [];
        $spaceChecks = [];
        $connectionChecks = [];
        $roadChecks = [];
        $none = Edge::NONE;
        $road = Edge::ROAD;

        $coords = [];

        for ($i = 0; $i < $size; ++$i) {
            $cell = array_slice($buildingData, $i * 6, 6);

            $cellX = $x + $cell[$indices[0]] * $sign;
            $cellY = $y + $cell[$indices[1]] * $sign;
            $cellZ = $z + $cell[$indices[2]] * $sign;

            $edgeX = $cell[$indices[0] + 3];
            $edgeY = $cell[$indices[1] + 3];
            $edgeZ = $cell[$indices[2] + 3];

            $values[] = "($cellX, $cellY, $cellZ, $edgeX, $edgeY, $edgeZ, $buildingId)";

            $coords[] = [$cellX, $cellY, $cellZ, $edgeX, $edgeY, $edgeZ];

            $spaceChecks[] =
                "x = $cellX AND y = $cellY AND z = $cellZ";

            $cellSign = 1 - 2 * ($cellX + $cellY + $cellZ);

            if ($edgeX <> Edge::NONE) {
                $neighborX = $cellX + $cellSign;
                $connectionChecks[] = "x = $neighborX AND y = $cellY AND z = $cellZ AND edge_x <> $none";

                $check = $edgeX === Edge::ROAD ? '<>' : '=';
                $roadChecks[] = "x = $neighborX AND y = $cellY AND z = $cellZ AND edge_x $check $road";
            }

            if ($edgeY <> Edge::NONE) {
                $neighborY = $cellY + $cellSign;
                $connectionChecks[] = "x = $cellX AND y = $neighborY AND z = $cellZ AND edge_y <> $none";

                $check = $edgeY === Edge::ROAD ? '<>' : '=';
                $roadChecks[] = "x = $cellX AND y = $neighborY AND z = $cellZ AND edge_y $check $road";
            }

            if ($edgeZ <> Edge::NONE) {
                $neighborZ = $cellZ + $cellSign;
                $connectionChecks[] = "x = $cellX AND y = $cellY AND z = $neighborZ AND edge_z <> $none";

                $check = $edgeZ === Edge::ROAD ? '<>' : '=';
                $roadChecks[] = "x = $cellX AND y = $cellY AND z = $neighborZ AND edge_z $check $road";
            }
        }

        if (!$setup) {
            $spaceArgs = implode(' OR ', $spaceChecks);
            $connectionArgs = implode(' OR ', $connectionChecks);
            $roadArgs = implode(' OR ', $roadChecks);

            $roadChecks = self::getObjectFromDb(<<<EOF
                SELECT 
                    (SELECT COUNT(*) FROM board WHERE $spaceArgs) AS occupied,
                    (SELECT COUNT(*) FROM board WHERE $connectionArgs) AS connections,
                    (SELECT COUNT(*) FROM board WHERE $roadArgs) AS roads
                EOF);

            if ((int)$roadChecks['occupied'] > 0) {
                throw new BgaUserException('Occupied space');
            }
            if ((int)$roadChecks['connections'] === 0) {
                throw new BgaUserException('No building connection');
            }
            if ((int)$roadChecks['roads'] > 0) {
                throw new BgaUserException('Invalid road connection');
            }
        }

        $args = implode(',', $values);
        self::DbQuery("INSERT INTO board(x, y, z, edge_x, edge_y, edge_z, building_id) VALUES $args");

        if (!$setup) {
            self::DbQuery("INSERT INTO adjacency(building1_id, building2_id, road) VALUES $args");
        }

        return $coords;
    }

    static function setupBuildings(): void
    {
        $church = Building::CHURCH;
        $connections = [];
        foreach (Building::SETUP as [$building, $position, $orientation]) {
            self::placeBuilding($building, $position, $orientation, true);
            if ($building !== Building::CHURCH) {
                $connections[] = "($building,$church,2)";
            }
        }
        $args = implode(",", $connections);
        self::DbQuery("INSERT INTO adjacency(building1_id, building2_id, road) VALUES $args");
    }

    static function setupPlayers(array $players): void
    {
        $church = Building::CHURCH;
        $donkeys = [];
        foreach ($players as $playerId => $_) {
            $donkeys[] = "($playerId,$church)";
        }
        $args = implode(',', $donkeys);
        self::DbQuery("INSERT INTO donkey(player_id, building_id) VALUES $args");
    }

    protected function getAllDatas()
    {
        $result = [];

        $result['round'] = self::getGameStateValue(Globals::ROUND_NUMBER);
        $result['movedDonkeys'] = self::getGameStateValue(Globals::MOVED_DONKEYS);
        $result['players'] = self::getCollectionFromDb(
            'SELECT player_id AS id, player_score AS score, player_color AS color, player_no AS no FROM player ');
        $result['buildings'] = self::getObjectListFromDb(
            'SELECT building_id AS id, x, y, z, orientation FROM building');
        $result['adjacency'] = self::getObjectListFromDb(
            'SELECT building1_id AS `from`, building2_id AS `to`, road FROM adjacency');
        $result['donkeys'] = self::getObjectListFromDb(
            "SELECT donkey_id AS id, building_id AS buildingId, player_id AS playerId FROM donkey");
        $result['board'] = self::getObjectListFromDb('SELECT * FROM board');

        return $result;
    }

    function getGameProgression()
    {
        return 0;
    }

    function build(int $x, int $y, int $z, int $orientation): void
    {
        self::checkAction('build');
        $buildingId = (int)self::getGameStateValue(Globals::CURRENT_BUILDING);
        self::placeBuilding($buildingId, [$x, $y, $z], $orientation);
        $this->gamestate->nextState('');
    }

    function move(int $donkeyId, int $buildingId): void
    {
        self::checkAction('move');

        $playerId = self::getActivePlayerId();
        $count = self::getUniqueValueFromDb(
            "SELECT COUNT(*) FROM donkey WHERE player_id = $playerId");
        $movedDonkeys = self::getGameStateValue(Globals::MOVED_DONKEYS);
        for ($i = 0; $i < $count; ++$i) {
            $id = ($movedDonkeys >> DONKEY_BITS * $i & (0b1 << DONKEY_BITS) - 1);
            if ($id === 0) {
                break;
            } else if ($id === $donkeyId) {
                throw new BgaUserException("Donkey already moved");
            }
        }

        (int)self::dbQuery(<<<EOF
            UPDATE donkey INNER JOIN adjacency 
                ON (building1_id = building_id AND building2_id = $buildingId 
                    OR building2_id = building_id AND building1_id = $buildingId)
            SET building_id = $buildingId
            WHERE donkey_id = $donkeyId AND road > 0
            EOF);

        if (self::DbAffectedRow() === 0) {
            throw new BgaUserException('Invalid move');
        }

        self::notifyAllPlayers('move', clienttranslate('${player_name} moves ${donkeyIcon} to ${buildingIcon}'), [
            'player_name' => self::getActivePlayerId(),
            'donkeyId' => $donkeyId,
            'buildingId' => $buildingId,
            'donkeyIcon' => $donkeyId,
            'buildingIcon' => $buildingId,
            'preserve' => ['donkeyIcon', 'buildingIcon']
        ]);

        $movedDonkeys |= $donkeyId << DONKEY_BITS * $i;
        self::setGameStateValue(Globals::MOVED_DONKEYS, $movedDonkeys);

        $this->gamestate->nextState($i < $count - 1 ? 'move' : 'end');
    }

    function skip(): void
    {
        self::checkAction('skip');
        $this->gamestate->nextState('end');
    }

    function argMoveDonkey(): array
    {
        $movedDonkeys = self::getGameStateValue(Globals::MOVED_DONKEYS);
        return [
            'movedDonkeys' => $movedDonkeys
        ];
    }

    function argPlaceBuilding(): array
    {
        $buildingId = (int)self::getGameStateValue(Globals::CURRENT_BUILDING);
        return [
            'buildingId' => $buildingId,
            'spaces' => self::getBuildingData($buildingId)
        ];
    }

    function stNextTurn(): void
    {
        $no = self::getPlayerNoById(self::getActivePlayerId());
        self::setGameStateValue(Globals::MOVED_DONKEYS, 0);

        self::activeNextPlayer();
        $this->gamestate->nextState(
            $no >= self::getPlayersNumber() ? 'end' : 'next');
    }

    function stNextRound(): void
    {
        self::incGameStateValue(Globals::ROUND_NUMBER, 1);
        self::notifyAllPlayers('message', clienttranslate('New round begins'), []);
        $this->gamestate->nextState('next');
    }

    function zombieTurn($state, $active_player)
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === 'activeplayer') {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( 'zombiePass' );
                	break;
            }
        } else {
            throw new feException("Zombie mode not supported at this game state: $statename");
        }
    }

    function upgradeTableDb($from_version)
    {

    }

    function dbgTurn()
    {
        $this->gamestate->jumpToState(State::NEXT_TURN);
    }
}