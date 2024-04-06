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
            Globals::CURRENT_BUILDING => Globals::CURRENT_BUILDING_ID,
            Globals::BUILDINGS_DECK => Globals::BUILDINGS_DECK_ID,
            Globals::AVAILABLE_BUILDINGS => Globals::AVAILABLE_BUILDINGS_ID
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
            if (array_key_exists($buildingId, BUILDING_PRODUCTS)) {
                [, $type] = BUILDING_PRODUCTS[$buildingId];
                self::DbQuery("INSERT INTO product(building_id, product_type) VALUES ($buildingId, $type)");
            }
        }

        return $coords;
    }

    static function deckDraw(int &$deck): int {
        if ($deck <= 0) {
            return -1;
        }

        $count = ($deck >> 1 & 0x5555555555555555) + ($deck & 0x5555555555555555);
        $count = ($count >> 2 & 0x3333333333333333) + ($count & 0x3333333333333333);
        $count = ($count >> 4 & 0x0F0F0F0F0F0F0F0F) + ($count & 0x0F0F0F0F0F0F0F0F);
        $count = ($count >> 8 & 0x00FF00FF00FF00FF) + ($count & 0x00FF00FF00FF00FF);
        $count = ($count >> 16 & 0x0000FFFF0000FFFF) + ($count & 0x0000FFFF0000FFFF);
        $count = ($count >> 32) + ($count & 0x00000000FFFFFFFF);

        $index = bga_rand(0, $count - 1);
        $counter = $deck;

        while (--$index >= 0) {
            $counter -= $counter & -$counter;
        }

        $bit = $counter & -$counter;
        $position = DECK_REMAINDERS[$bit % count(DECK_REMAINDERS)];
        $deck -= $bit;
        return $position;
    }

    static function setupBuildings(): void
    {
        $church = Building::CHURCH;
        $connections = [];
        $products = [];

        foreach (Building::SETUP as [$building, $position, $orientation]) {
            self::placeBuilding($building, $position, $orientation, true);

            if ($building !== Building::CHURCH) {
                $connections[] = "($building,$church,2)";
            }

            if (array_key_exists($building, BUILDING_PRODUCTS)) {
                [, $type] = BUILDING_PRODUCTS[$building];
                $products[] = "($building,$type,2)";
            }
        }
        $args = implode(",", $connections);
        self::DbQuery("INSERT INTO adjacency(building1_id, building2_id, road) VALUES $args");

        $args = implode(",", $products);
        self::DbQuery("INSERT INTO product(building_id, product_type, count) VALUES $args");

        $deck = 0;
        foreach (Building::BASIC as $buildingId) {
            $deck |= 1 << $buildingId;
        }

        $display = 0;
        for ($i = 0; $i < 4; ++$i) {
            $display |= self::draw($deck) << $i * 5;
        }

        self::setGameStateValue(Globals::BUILDINGS_DECK, $deck);
        self::setGameStateValue(Globals::AVAILABLE_BUILDINGS, $display);
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
        $result['availableBuildings'] = self::getGameStateValue(Globals::AVAILABLE_BUILDINGS);
        $result['players'] = self::getCollectionFromDb(<<<'EOF'
            SELECT player_id AS id, player_score AS score, 
                player_color AS color, player_no AS no, coins 
            FROM player 
            EOF);
        $result['buildings'] = self::getObjectListFromDb(
            'SELECT building_id AS id, x, y, z, orientation FROM building');
        $result['products'] = self::getObjectListFromDb(
                'SELECT building_id AS buildingId, product_type AS type, count FROM product WHERE `count` > 0');
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
            'player_name' => self::getActivePlayerName(),
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

    function work(int $buildingId, int $count, ?array $ingredients = null): void
    {
        if ($count <= 0 || !array_key_exists($buildingId, BUILDING_PRODUCTS)) {
            throw new BgaUserException("Invalid building");
        }

        [$maxNumber, $type] = BUILDING_PRODUCTS[$buildingId];
        self::DbQuery(<<<EOF
            UPDATE product 
            SET `count` = $maxNumber
            WHERE building_id = $buildingId AND product_type = $type AND `count` + $count = $maxNumber
            EOF);

        if (self::DbAffectedRow() === 0) {
            throw new BgaUserException("Invalid work");
        }

        self::notifyAllPlayers('work', clienttranslate('${building}: ${player_name} earns ${coinIcons} for producing ${productIcons}'), [
            'building' => $buildingId,
            'player_name' => self::getActivePlayerName(),
            'coinIcons' => 2,
            'product' => [
                'type' => $type,
                'count' => $count
            ],
            'productIcons' => "$count,$type"
        ]);

        $this->gamestate->nextState('');
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