<?php
/*
*    /$$   /$$  /$$$$$$  /$$$$$$$$ /$$   /$$
*   | $$  /$$/ /$$__  $$|__  $$__/| $$  | $$
*   | $$ /$$/ | $$  \ $$   | $$   | $$  | $$
*   | $$$$$/  | $$  | $$   | $$   | $$$$$$$$
*   | $$  $$  | $$  | $$   | $$   | $$__  $$
*   | $$\  $$ | $$  | $$   | $$   | $$  | $$
*   | $$ \  $$|  $$$$$$/   | $$   | $$  | $$
*   |__/  \__/ \______/    |__/   |__/  |__/
*
*   Copyright (C) 2019-2020 JaxkDev
*
*   This program is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   any later version.
*
*   This program is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <https://www.gnu.org/licenses/>.
*
*   Twitter :: @JaxkDev
*   Discord :: JaxkDev#8860
*   Email   :: JaxkDev@gmail.com
*/

declare(strict_types=1);
namespace Jackthehack21\KOTH\Events;

use Jackthehack21\KOTH\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\player\Player;

/*
 * Note: The event is only used when the command /koth remove/delete is used,
 * NOT when the plugins removeArena is called (so it will not work if plugins call the function)
 *
 * You have been warned.
 */

class ArenaCreateEvent extends KothEvent{

    private $creator;

    /** @var string */
    private $name;
    private $world;

    /** @var int */
    private $min_players;
    private $max_players;
    private $game_time;

    /** @var array */
    private $hill;
    private $spawns;
    private $rewards;

    public function __construct(Main $plugin, $creator, string $name, int $min_players, int $max_players, int $gameTime, array $hill = [], array $spawns = [], array $rewards = [], string $world = "null"){
        $this->creator = $creator;
        $this->name = $name;
        $this->min_players = $min_players;
        $this->max_players = $max_players;
        $this->game_time = $gameTime;
        $this->hill = $hill;
        $this->spawns = $spawns;
        $this->rewards = $rewards;
        $this->world = $world;
        parent::__construct($plugin);
    }

    /** @return Player|ConsoleCommandSender|CommandSender|null */
    public function getCreator(){
        return $this->creator;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) : void{
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getMinPlayers() : int{
        return $this->min_players;
    }

    /**
     * @param int $amount
     */
    public function setMinPlayers(int $amount) : void{
        $this->min_players = $amount;
    }

    /**
     * @return int
     */
    public function getMaxPlayers() : int{
        return $this->max_players;
    }

    /**
     * @param int $amount
     */
    public function setMaxPlayers(int $amount) : void{
        $this->max_players = $amount;
    }

    /**
     * @return int
     */
    public function getGameTime() : int{
        return $this->game_time;
    }

    /**
     * @param int $amount
     */
    public function setGameTime(int $amount) : void{
        $this->game_time = $amount;
    }

    /**
     * @return array
     */
    public function getHillPositions() : array{
        return $this->hill;
    }

    /**
     * @param array $hill
     */
    public function setHillPositions(array $hill) : void{
        $this->hill = $hill;
    }

    /**
     * @return array
     */
    public function getSpawnPositions() : array{
        return $this->spawns;
    }

    /**
     * @param array $spawns
     */
    public function setSpawnPositions(array $spawns) : void{
        $this->spawns = $spawns;
    }

    /**
     * @return array
     */
    public function getRewards() : array{
        return $this->rewards;
    }

    /**
     * @param array $rewards
     */
    public function setRewards(array $rewards) : void{
        $this->rewards = $rewards;
    }

    /**
     * @return string
     */
    public function getWorld() : string{
        return $this->world;
    }

    /**
     * @param string $worldName
     */
    public function setWorld(string $worldName) : void{
        $this->world = $worldName;
    }
}