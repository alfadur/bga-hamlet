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
            Globals::MOVED_DONKEYS => Globals::MOVED_DONKEYS_ID
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

        self::setGameStateInitialValue(Globals::MOVED_DONKEYS, 0);

        self::createBuildings();

        $this->activeNextPlayer();
    }

    static function placeBuilding(int $buildingId, array $position, int $orientation, bool $positionCheck = true): array
    {
        self::DbQuery("INSERT INTO building(building_id) VALUES ($buildingId)");

        [$x, $y, $z] = $position;

        if ($x + $y + $z !== ($orientation & 0b1)) {
            throw new BgaUserException('Invalid orientation');
        }

        $sign = 1 - ($orientation & 0b1) * 2;
        $indices = [
            $orientation % 3,
            ($orientation + 1) % 3,
            ($orientation + 2) % 3
        ];

        $buildingData = BUILDING_CELLS[$buildingId];
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

        if ($positionCheck) {
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

        return $coords;
    }

    static function createBuildings(): void
    {
        self::placeBuilding(Building::CHURCH, [0, 0, 0], 0, false);
        self::placeBuilding(Building::TOWN_HALL, [-3, 3, 0], 0, true);
    }

    protected function getAllDatas()
    {
        $result = [];

        $query = 'SELECT player_id AS id, player_score AS score FROM player ';
        $result['players'] = self::getCollectionFromDb($query);
        $result['board'] = self::getObjectListFromDb('SELECT * FROM board');

        return $result;
    }

    function getGameProgression()
    {
        return 0;
    }

    function build(int $buildingId, int $x, int $y, int $z, int $orientation): void
    {
        self::placeBuilding($buildingId, [$x, $y, $z], $orientation);
    }

    function moveDonkey(int $id, int $tile): void
    {

    }

    function stNextTurn(): void
    {
        $this->gamestate->nextState('');
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
}
