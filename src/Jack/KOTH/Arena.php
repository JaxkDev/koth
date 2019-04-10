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
*   Copyright (C) 2019 Jackthehack21 (Jack Honour/Jackthehaxk21/JaxkDev)
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
*   Discord :: Jackthehaxk21#8860
*   Email   :: gangnam253@gmail.com
*/

declare(strict_types=1);
namespace Jack\KOTH;

use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

use Jack\KOTH\Main;
/*

NOTES:
- so if king dies out of box or goes out of box, its race to the box, 
  if killed in box from someone inside box that killer is king
  if someone outside the box kills him next to box is king
  if multple people in box when next king selection, person closest to middle is crowned.

- EventHandler handles all events from pmmp, then passed here.

*/

class Arena{
    private $plugin;
    public $spawns = []; //[[12,50,10],[],[],[]] list of spawn points.
    public $spawnCounter;
    public $hill = []; //[[20,50,20],[30,50,20]] two points corner to corner.
    public $players = []; //list of all players currently ingame. (lowercase names)
    public $limit;
    public $name;
    public $started;
    public $time;
    public $countDown;
    public $world;

    public $king;

    public function __construct(Main $plugin, string $name, int $min, int $limit, int $time, int $count, array $hill, array $spawns, string $world){
        $this->plugin = $plugin;
        $this->hill = $hill;
        $this->minPlayers = $min;
        $this->maxPlayers = $limit;
        $this->name = $name;
        $this->spawns = $spawns;
        $this->spawnCounter = 0;
        $this->started = false;
        $this->time = $time;
        $this->countDown = $count;
        $this->world = $world;
    }

    public function broadcastMessage(string $msg){
        foreach($this->players as $player){
            $this->plugin->getServer()->getPlayerExact($player)->sendMessage($msg);
        }
    }

    public function broadcastQuit(Player $player, string $reason){
        //get config.
        $this->broadcastMessage($this->plugin->prefix.$player->getName()." Has left the game, reason: ".$reason);
    }

    public function broadcastJoin(Player $player){
        //get config.
        $this->broadcastMessage($this->plugin->prefix.$player->getName()." Has joined the game !");
    }

    public function spawnPlayer(Player $player, bool $random = false){
        if(strtolower($player->getLevel()->getName()) !== strtolower($this->world)){
            if(!$this->plugin->getServer()->isLevelGenerated($this->world)) {
                //todo config msg.
                //world does not exist
                return;
            }
            if(!$this->getServer()->isLevelLoaded($this->world)) {
                $this->getServer()->loadLevel($this->world);
            }

        }
        if($random === true){
            $old = array_rand($this->spawns);
            $pos = new Position($old[0], $old[1], old[2], $this->getServer()->getLevelByName($this->world)); //x,y,z,level;
            $player->teleport($pos);
        } else {
            if($spawnCounter > count($this->spawns)){
                $spawnCounter = 0; //reset
            }
            $old = $this->spawns[$spawnCounter];
            $pos = new Position($old[0], $old[1], old[2], $this->getServer()->getLevelByName($this->world)); //x,y,z,level;
            $player->teleport($pos);
            $spawnCounter++;
        }
    }

    /**
     * @param Player $player
     * @param string $reason
     * 
     * @return void
     */
    public function removePlayer(Player $player, string $reason) : void{
        if($this->king === $player->getLowerCaseName()){
            //change king.
        }
        unset($this->players[array_search(strtolower($player->getName(), $this->players))]);
        $this->broadcastQuit($player, $reason);
    }

    /**
     * NOTE: Returns false if player cannot join.
     * 
     * @param Player $player
     * 
     * @return bool
     */
    public function addPlayer(Player $player) : bool{
        if(count($this->players) >= $this->limit){
            return false;
        }
        if($this->plugin->getArenaByPlayer(strtolower($player->getName())) !== null){
            return false;
        }
        $this->broadcastJoin($player);
        $this->players[] = strtolower($player->getName());
        $this->spawnPlayer($player);
    }
}