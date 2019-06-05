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
namespace Jackthehack21\KOTH;

use Jackthehack21\KOTH\Particles\FloatingText;
use Jackthehack21\KOTH\Tasks\Prestart;
use Jackthehack21\KOTH\Tasks\Gametimer;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\level\Position;

use pocketmine\utils\TextFormat as C;


/*

NOTES:
- so if king dies out of box or goes out of box, its race to the box, 
  if killed in box from someone inside box that killer is king
  if someone outside the box kills him next to box is king
  if multiple people in box when next king selection, person closest to middle is crowned.

- EventHandler handles all events from pmmp, then passed here, or handled by values obtained from here.

*/

class Arena{

    public const STATUS_NOT_READY = 0;
    public const STATUS_READY = 1;
    public const STATUS_STARTED = 2;
    public const STATUS_FULL = 3;
    public const STATUS_UNKNOWN = 9;

    public $statusList = [
        self::STATUS_NOT_READY => "Not Ready/Setup",
        self::STATUS_READY => "Ready",
        self::STATUS_STARTED => "Started",
        self::STATUS_FULL => "Full",
        self::STATUS_UNKNOWN => "Unknown"
    ];

    private $plugin;
    public $spawns = [];
    public $spawnCounter;
    public $hill = [];
    public $players = [];
    public $playerOldPositions = [];
    public $playerOldNameTags = [];
    public $minPlayers;
    public $maxPlayers;
    public $name;
    public $started;
    public $time;
    public $countDown;
    public $world;
    public $rewards;

    public $oldKing;
    public $king;
    public $playersInBox;

    public $timerTask;
    public $status;

    public $currentKingParticle = null;

    /**
     * Arena constructor.
     * @param Main $plugin
     * @param string $name
     * @param int $min
     * @param int $max
     * @param int $time
     * @param array $hill
     * @param array $spawns
     * @param array $rewards
     * @param string $world
     */
    public function __construct(Main $plugin, string $name, int $min, int $max, int $time, array $hill, array $spawns, array $rewards, string $world){
        $this->plugin = $plugin;
        $this->hill = $hill;
        $this->minPlayers = $min;
        $this->maxPlayers = $max;
        $this->name = $name;
        $this->spawns = $spawns;
        $this->spawnCounter = 0;
        $this->started = false;
        $this->time = $time;
        $this->countDown = $plugin->config["start_countdown"];
        $this->world = $world;
        $this->rewards = $rewards;

        $this->king = null;
        $this->playersInBox = [];
        $this->timerTask = null;

        $this->currentKingParticle = null;

        $this->checkStatus();
        $this->createKingTextParticle();
    }

    /**
     * @return string
     */
    public function getFriendlyStatus() : string{
        return $this->statusList[$this->status];
    }

    /**
     * @return int
     */
    public function getStatus() : int{
        return $this->status;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @param string $msg
     */
    public function broadcastMessage(string $msg) : void{
        foreach($this->players as $player){
            $this->plugin->getServer()->getPlayerExact($player)->sendMessage($msg);
        }
    }

    /**
     * @param string $player
     */
    public function broadcastWinner(string $player) : void{
        //todo get config.
        $this->broadcastMessage($this->plugin->prefix.$player." Has won the game !");
    }

    /**
     * @param Player $player
     * @param string $reason
     */
    public function broadcastQuit(Player $player, string $reason) : void{
        //todo get config.
        $this->broadcastMessage($this->plugin->prefix.$player->getName()." Has left the game, reason: ".$reason);
    }

    /**
     * @param Player $player
     */
    public function broadcastJoin(Player $player) : void{
        //todo get config.
        $this->broadcastMessage($this->plugin->prefix.$player->getName()." Has joined the game !");
    }

    /**
     * @param bool $save
     */
    public function checkStatus(bool $save = true) : void{
        if(count($this->hill) === 2 && count($this->spawns) >= 1 && $this->plugin->getServer()->getLevelByName($this->world) !== null){
            $this->status = self::STATUS_READY;
        } else {
            $this->status = self::STATUS_NOT_READY;
            if($save === true) $this->plugin->updateArena($this);
            return;
        }
        if($this->started === true){
            $this->status = self::STATUS_STARTED;
        }
        if(count($this->players) >= $this->maxPlayers){
            $this->status = self::STATUS_FULL;
            if($save === true) $this->plugin->updateArena($this);
            return;
        }
        if($save === true) $this->plugin->updateArena($this);
    }

    public function createKingTextParticle() : void{
        if($this->plugin->config["KingTextParticles"] === false) return;
        if($this->status !== $this::STATUS_NOT_READY and $this->currentKingParticle === null){
            //spawn king particle, as we have position of hill/throne and level.
            $pos = new Vector3(($this->hill[0][0]+$this->hill[1][0])/2,($this->hill[0][1]+$this->hill[1][1])/2,($this->hill[0][2]+$this->hill[1][2])/2);
            $this->currentKingParticle = new FloatingText($this->plugin, $this->plugin->getServer()->getLevelByName($this->world), $pos, C::RED."King: ".C::GOLD."-");
        }
    }

    public function updateKingTextParticle() : void{
        if($this->currentKingParticle !== null){
            /** @noinspection PhpUndefinedMethodInspection */
            $this->currentKingParticle->setInvisible(false); //fix restarting games.
            /** @noinspection PhpUndefinedMethodInspection */
            $this->currentKingParticle->setText(C::RED."King: ".C::GOLD.($this->king === null ? "-" : $this->king));
        }
        //set name tags, its own function so others can run it without updating Particles.
        $this->updateNameTags();
    }

    public function removeKingTextParticles() : void{
        if($this->currentKingParticle !== null){
            /** @noinspection PhpUndefinedMethodInspection */
            $this->currentKingParticle->setInvisible();
        }
        $this->updateNameTags(); //here to revert back to original.
    }

    public function updateNameTags() : void{
        //this makes plugins that modify your tag based on things like health,lvl etc not work while in game.
        if($this->plugin->config["nametag_enabled"] === true){
            /** @noinspection PhpUndefinedMethodInspection */
            $format = $this->plugin->utils->colourise($this->plugin->config["nametag_format"]);
            if($this->king !== null){
                /** @noinspection PhpStrictTypeCheckingInspection */
                $player = $this->plugin->getServer()->getPlayerExact($this->king);
                if(array_key_exists($this->king,$this->playerOldNameTags) !== true){
                    $this->playerOldNameTags[$this->king] = $player->getNameTag();
                }
                $old = $this->playerOldNameTags[$player->getLowerCaseName()];
                $player->setNameTag($format."\n".$old);
                if($this->oldKing !== null and $this->oldKing !== $this->king){
                    //remove nametag.
                    $old = $this->playerOldNameTags[$this->oldKing];
                    $p = $this->plugin->getServer()->getPlayerExact($this->oldKing);
                    if($p === null) return;
                    $p->setNameTag($old);
                }
            } else {
                if($this->oldKing !== null){
                    $player = $this->plugin->getServer()->getPlayerExact($this->oldKing);
                    if($player === null) return;
                    $player->setNameTag($this->playerOldNameTags[strtolower($player->getName())]);
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param bool $random
     */
    public function spawnPlayer(Player $player, $random = false) : void{
        if(strtolower($player->getLevel()->getName()) !== strtolower($this->world)){
            if(!$this->plugin->getServer()->isLevelGenerated($this->world)) {
                //todo config msg.
                $player->sendMessage($this->plugin->prefix.C::RED."This arena is corrupt.");
                return;
            }
            if(!$this->plugin->getServer()->isLevelLoaded($this->world)) {
                $this->plugin->getServer()->loadLevel($this->world);
            }

        }
        $player->teleport($this->getSpawn($random));
    }

    /**
     * @param bool $random
     * @return Position
     */
    public function getSpawn(bool $random = false) : Position{
        if($random === false){
            if($this->spawnCounter >= count($this->spawns)){
                $this->spawnCounter = 0;
            }
            $old = $this->spawns[$this->spawnCounter];
            $pos = new Position($old[0], $old[1], $old[2], $this->plugin->getServer()->getLevelByName($this->world));
            $this->spawnCounter++;
            return $pos;
        } else {
            $old = $this->spawns[array_rand($this->spawns)];
            $pos = new Position($old[0], $old[1], $old[2], $this->plugin->getServer()->getLevelByName($this->world));
            return $pos;
        }
    }

    /**
     * @param bool $freeze
     */
    public function freezeAll(bool $freeze) : void{
        foreach($this->players as $name){
            $this->plugin->getServer()->getPlayerExact($name)->setImmobile($freeze);
        }
    }

    public function startTimer() : void{
        $this->timerTask = $this->plugin->getScheduler()->scheduleRepeatingTask(new Prestart($this->plugin, $this, $this->countDown),20);
    }

    public function startGame() : void{
        /** @noinspection PhpUndefinedMethodInspection */
        //todo check config for broadcast on start.
        $this->timerTask->cancel();
        $this->started = true;
        $this->checkStatus();
        $this->broadcastMessage($this->plugin->prefix.C::GOLD."Game On !");
        $this->createKingTextParticle(); //in case it was never made on startup as it was first made.
        $this->updateKingTextParticle(); //spawn in.
        $this->timerTask = $this->plugin->getScheduler()->scheduleRepeatingTask(new Gametimer($this->plugin, $this),10);
    }

    public function reset() : void{
        $this->removeKingTextParticles();

        $this->started = false;
        $this->king = null;
        $this->oldKing = null;
        $this->timerTask = null;

        foreach($this->players as $name){
            $player = $this->plugin->getServer()->getPlayerExact($name);
            $this->removePlayer($player, "Game over", true);
        }

        $this->players = [];
        $this->playerOldPositions = [];
        $this->playerOldNameTags = [];
        $this->checkStatus();
    }

    public function endGame() : void{
        //todo check config for broadcast on end.
        $this->freezeAll(true);
        $old = $this->oldKing; //in case of no king.
        $king = $this->king; //in case of a change in this tiny blind spot.
        /** @noinspection PhpUndefinedMethodInspection */
        $this->timerTask->cancel();
        //todo events.
        if($king !== null){
            /** @noinspection PhpStrictTypeCheckingInspection */
            $this->setWinner($king);
        } else {
            if($old === null){
                $this->setWinner("Null");
            } else {
                /** @noinspection PhpStrictTypeCheckingInspection */
                $this->setWinner($old);
            }
        }
        $this->reset();
        $this->checkStatus();
    }

    /**
     * @param string $king
     */
    public function setWinner(string $king) : void{
        if($king === "Null"){
            $this->broadcastMessage($this->plugin->prefix.C::RED."GAME OVER, No one managed to claim the hill and win the game. Better luck next time.");
            $this->freezeAll(false);
            return;
        }
        $this->broadcastWinner($king);
        $console = new ConsoleCommandSender();
        foreach($this->rewards as $reward){
            $reward = str_replace("{PLAYER}", $king, $reward);
            if($this->plugin->getServer()->getCommandMap()->dispatch($console, $reward) === false){
                $this->plugin->getLogger()->warning("Reward/command (".$reward.") failed to execute.");
            };

        }
        //todo particles fireworks and more for king, and X second delay before un freezing.
        $this->freezeAll(false);
    }

    /**
     * @return array
     */
    public function getPlayers() : array{
        return $this->players;
    }

    /**
     * @return array
     */
    public function playersInBox() : array{
        $pos1 = [];
        $pos1["x"] = $this->hill[0][0];
        $pos1["y"] = $this->hill[0][1];
        $pos1["z"] = $this->hill[0][2];
        $pos2 = [];
        $pos2["x"] = $this->hill[1][0];
        $pos2["y"] = $this->hill[1][1];
        $pos2["z"] = $this->hill[1][2];
        $minX = min($pos2["x"],$pos1["x"]);
        $maxX = max($pos2["x"],$pos1["x"]);
        $minY = min($pos2["y"],$pos1["y"]);
        $maxY = max($pos2["y"],$pos1["y"]);
        $minZ = min($pos2["z"],$pos1["z"]);
        $maxZ = max($pos2["z"],$pos1["z"]);
        $list = [];

        if($minY == $maxY){
            $maxY += 1.51;
        } //To allow jumping, shouldn't effect what so ever.

        foreach($this->players as $playerName){
            $player = $this->plugin->getServer()->getPlayer($playerName);
            if(($minX <= $player->getX() && $player->getX() <= $maxX && $minY <= $player->getY() && $player->getY() <= $maxY && $minZ <= $player->getZ() && $player->getZ() <= $maxZ)){
                $list[] = $playerName;
            }
        }
        return $list;
    }

    public function removeKing() : void{
        if($this->king === null) return;
        $this->broadcastMessage($this->plugin->prefix.C::RED."The king has fallen.");
        //todo config.
        $this->changeking();
    }

    public function changeKing() : void{
        if($this->king !== null){
            $this->oldKing = $this->king;
            $this->king = null;
        }
        $this->updateKingTextParticle();
    }

    /**
     * @return bool
     */
    public function checkNewKing() : bool{
        if(count($this->playersInBox()) === 0){
            return false;
        } else {
            $player = $this->playersInBox()[array_rand($this->playersInBox())]; //todo closest to middle.
            $this->broadcastMessage($this->plugin->prefix.C::GOLD.$player.C::GREEN." Has claimed the throne, how long will it last...");
            $this->king = $player;
            $this->updateKingTextParticle();
            return true;
        }
    }


    /**
     * @param Player|CommandSender $player
     * @param string $reason
     * @param bool   $silent
     * 
     * @return void
     */
    public function removePlayer(Player $player, string $reason, bool $silent = false) : void{
        unset($this->players[array_search(strtolower($player->getName()), $this->players)]);
        if($this->king === $player->getLowerCaseName()){
            $this->removeKing();
        }
        if($silent === false) $this->broadcastQuit($player, $reason);
        $this->checkStatus();
        if($player->loggedIn !== false and $player->spawned !== false){ //check to avoid tp if player left server.
            $pos = new Position($this->playerOldPositions[strtolower($player->getName())][1],$this->playerOldPositions[strtolower($player->getName())][2],$this->playerOldPositions[strtolower($player->getName())][3],$this->plugin->getServer()->getLevelByName($this->playerOldPositions[strtolower($player->getName())][0]));
            $player->teleport($pos);
            unset($this->playerOldPositions[strtolower($player->getName())]);
        }
    }

    /**
     * Returns false if player cannot join.
     * 
     * @param Player|CommandSender $player
     * 
     * @return bool
     */
    public function addPlayer(Player $player) : bool{
        if(count($this->players) >= $this->maxPlayers){
            return false;
        }
        if($this->plugin->getArenaByPlayer(strtolower($player->getName())) !== null){
            return false;
        }
        switch($this->status){
            case self::STATUS_NOT_READY:
                $player->sendMessage($this->plugin->prefix.C::RED."This arena has not been setup.");
                return false;
            case self::STATUS_FULL:
                $player->sendMessage($this->plugin->prefix.C::RED."This arena is full.");
                return false;
        }
        $player->setGamemode(0);
        $this->players[] = strtolower($player->getName());
        $this->playerOldPositions[strtolower($player->getName())] = [$player->getLevel()->getName(),$player->getX(), $player->getY(), $player->getZ()];
        $this->broadcastJoin($player);
        $this->spawnPlayer($player);
        if(count($this->players) >= $this->minPlayers && $this->timerTask === null && $this->plugin->config["auto-start"] === true){
            $this->startTimer();
        }
        $this->checkStatus();
        return true;
    }
}