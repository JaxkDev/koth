<?php
/*
 *   KOTH, A pocketmine-MP Mini-game
 *
 *   Copyright (C) 2019-present JaxkDev
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
 *   Discord :: JaxkDev#2698
 *   Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\KOTH;

use JaxkDev\KOTH\Events\ArenaAddPlayerEvent;
use JaxkDev\KOTH\Events\ArenaEndEvent;
use JaxkDev\KOTH\Events\ArenaPreStartEvent;
use JaxkDev\KOTH\Events\ArenaRemovePlayerEvent;
use JaxkDev\KOTH\Events\ArenaStartEvent;
use JaxkDev\KOTH\Particles\FloatingText;
use JaxkDev\KOTH\Tasks\StartCountdown;
use JaxkDev\KOTH\Tasks\GameTimer;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat as C;

use TypeError;


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
    public const STATUS_INVALID = 4;
    public const STATUS_DISABLED = 7;
    public const STATUS_UNKNOWN = 9;

    public array $statusList = [
        self::STATUS_NOT_READY => "Not Ready/Setup",
        self::STATUS_READY => "Ready",
        self::STATUS_STARTED => "Started",
        self::STATUS_FULL => "Full",
        self::STATUS_INVALID => "Invalid Setup", #Used when arena was setup correctly but external causes means its no longer compatible.
		self::STATUS_DISABLED => "Disabled",
        self::STATUS_UNKNOWN => "Unknown"
    ];

    private Main $plugin;
    public $spawns = [];
    public $spawnCounter;
    public $hill = [];
    private $players = [];
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

    public ?Player $oldKing = null;
    public ?Player $king = null;
    /** @var Player[] */
    public array $playersInBox = [];

    /** @var null|TaskHandler */
    public $timerTask;

    public int $status = 9;

    public ?FloatingText $currentKingParticle = null;

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
        $this->countDown = $plugin->config["countdown"];
        $this->world = $world;
        $this->rewards = $rewards;

        $this->timerTask = null;


        $this->checkStatus();
        $this->createKingTextParticle();

        $this->plugin->getLogger()->debug($this->getName()." - Arena Constructed, status: ".$this->getFriendlyStatus());
    }

    public function getFriendlyStatus(): string{
        return $this->statusList[$this->status]??$this->statusList[$this::STATUS_UNKNOWN];
    }

    public function getStatus(): int{
        return $this->status;
    }

    public function getName(): string{
        return $this->name;
    }

    public function enable(): bool{
    	if($this->status !== $this::STATUS_DISABLED){
    		$this->plugin->getLogger()->debug("Failed to enable arena '".$this->getName()."', already enabled.");
    		return false;
		}
    	$this->status = $this::STATUS_UNKNOWN;
    	$this->checkStatus();
    	if($this->status !== $this::STATUS_DISABLED) $this->plugin->getLogger()->debug("Enabled arena '".$this->getName()."'");
    	else $this->plugin->getLogger()->debug("Failed to enable arena '".$this->getName()."'");
    	return true;
	}

	public function disable(): bool{
		if($this->status === $this::STATUS_DISABLED){
			$this->plugin->getLogger()->debug("Failed to disable arena '".$this->getName()."', already disabled.");
			return false;
		}
		if($this->status === $this::STATUS_STARTED or count($this->players) > 0){
			$this->plugin->getLogger()->debug("Failed to disable arena '".$this->getName()."', players are currently in the arena.");
			return false;
		}
    	$this->status = $this::STATUS_DISABLED;
		if($this->timerTask !== null) $this->timerTask->cancel();
		$this->reset();
		$this->plugin->getLogger()->debug("Disabled arena '".$this->getName()."'");
    	return true;
	}

    /**
     * @param string $msg
     */
    public function broadcastMessage(string $msg): void{
    	$this->plugin->getLogger()->debug("Broadcasting message '".$msg."' to '".count($this->players)."' Players in arena '".$this->getName()."'");
        foreach($this->players as $player){
            $this->plugin->getServer()->getPlayerExact($player)->sendMessage($msg);
        }
    }

    public function broadcastWinner(string $player): void{
        $this->broadcastMessage(str_replace(["{ARENA}", "{PLAYER}"], [$this->name, $player], $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["winner"])));
    }

    public function broadcastQuit(Player $player, string $reason): void{
        $this->broadcastMessage(str_replace(["{REASON}", "{PLAYER}"], [$reason, $player->getLowerCaseName()], $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["player_quit"])));
    }

    public function broadcastJoin(Player $player): void{
        $this->broadcastMessage(str_replace("{PLAYER}", $player->getLowerCaseName(), $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["player_join"])));
    }

    public function checkStatus(bool $save = true): void{
    	$this->plugin->getLogger()->debug("Checking status in arena '".$this->getName()."', save: ".($save ? "yes" : "no"));
    	$world = $this->plugin->utils->getLevelByName($this->world);
    	if($this->status === $this::STATUS_DISABLED) return;
    	if(count($this->hill) === 2 and count($this->spawns) >= 1 and $world !== null){
    		$this->status = self::STATUS_READY;
    	} else {
    		$this->status = self::STATUS_NOT_READY;
    		if($world === null){
    			$this->plugin->getLogger()->warning(C::RED."World '".$this->world."' couldn't be loaded/found, Arena '".$this->getName()."' Will not be playable.");
    			$this->status = self::STATUS_INVALID;
    			$this->currentKingParticle = null;
    		}
    		if($world !== null) $this->world = $world->getFolderName();
    		if($save === true) $this->plugin->updateArena($this);
			$this->plugin->getLogger()->debug("Setting status to '".$this->getFriendlyStatus()."'. (Arena: '".$this->getName()."')");
    		return;
    	}
    	if($this->started === true){
    		$this->status = self::STATUS_STARTED;
    	}
    	if(count($this->players) >= $this->maxPlayers){
    		$this->status = self::STATUS_FULL;
    		if($save === true) $this->plugin->updateArena($this);
			$this->plugin->getLogger()->debug("Setting status to '".$this->getFriendlyStatus()."'. (Arena: '".$this->getName()."')");
    		return;
    	}
    	if($save === true) $this->plugin->updateArena($this);
		$this->plugin->getLogger()->debug("Setting status to '".$this->getFriendlyStatus()."'. (Arena: '".$this->getName()."')");
    }

    public function createKingTextParticle(): void{
    	$this->plugin->getLogger()->debug("Creating KT particle for arena '".$this->getName()."'");
        if($this->plugin->config["KingTextParticles"] === false) return;
        $this->checkStatus(); //Double check its ready, also used to get exact world name.
        if(($this->status !== $this::STATUS_NOT_READY and $this->status !== $this::STATUS_INVALID) and $this->currentKingParticle === null){
        	$this->plugin->getLogger()->debug("KT Particle being created... ('".$this->getName()."')");
            $pos = new Vector3(($this->hill[0][0]+$this->hill[1][0])/2,($this->hill[0][1]+$this->hill[1][1])/2,($this->hill[0][2]+$this->hill[1][2])/2);
            $this->currentKingParticle = new FloatingText($this->plugin, $this->plugin->getServer()->getLevelByName($this->world), $pos, C::RED."King: ".C::GOLD."-");
        }
        else $this->plugin->getLogger()->debug("Arena '".$this->getName()."' doesnt satisfy the requirements needed to create the KT particle.");
    }

    public function updateKingTextParticle(): void{
    	$this->plugin->getLogger()->debug("Updating KT particle in arena '".$this->getName()."'");
        if($this->currentKingParticle !== null){
            $this->currentKingParticle->setInvisible(false); //fix restarting games.
            $this->currentKingParticle->setText(C::RED."King: ".C::GOLD.($this->king === null ? "-" : $this->king));
        } else {
            $this->createKingTextParticle(); //keep trying to create it in case the scenario changes and its now able.
        }
        //set name tags, its own function so others can run it without updating Particles.
        $this->updateNameTags();
    }

    public function removeKingTextParticles(): void{
        if($this->currentKingParticle !== null){
            $this->currentKingParticle->setInvisible();
        }
        $this->updateNameTags(); //here to revert back to original.
    }

    public function updateNameTags(): void{
        //this makes plugins that modify your tag based on things like health,lvl etc not work while in game.
        if($this->plugin->config["nametag_enabled"] === true){
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
     * @return bool
     */
    private function spawnPlayer(Player $player, $random = false): bool{
    	$world = $this->plugin->utils->getLevelByName($this->world);
    	if($world === null){
    		$player->sendMessage($this->plugin->prefix.C::RED."World set for '".$this->name."' does not exist");
    		$this->plugin->getLogger()->debug("World not found, '".$this->world."' for arena '".$this->getName()."'");
    		return false;
		}
        if($player->getLevel()->getId() !== $world->getId()){
            if(!$this->plugin->getServer()->isLevelLoaded($world->getFolderName())) {
                $this->plugin->getServer()->loadLevel($world->getFolderName());
                $this->plugin->getLogger()->debug("Loaded world '".$world->getFolderName()."' So '".$player->getName()."' can join.");
            }

        }
        $spawn = $this->getSpawn($random);
        $this->plugin->getLogger()->debug("Teleporting '".$player->getName()."' to '".$spawn."' in level '".$world->getFolderName()."'");
        $player->teleport($spawn);
        return true;
    }

    /**
     * @param bool $random
     * @return Position|null
     */
    public function getSpawn(bool $random = false): ?Position{
		$world = $this->plugin->utils->getLevelByName($this->world);
		if($world === null){
			$this->plugin->getLogger()->debug("World '".$this->world."' Not found !!!, Failed to get random position in arena '".$this->getName()."'");
			throw new TypeError("World '".$this->world."' Not found, Failed to get random position in arena '".$this->getName()."'");
		}
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
    public function freezeAll(bool $freeze): void{
        $this->plugin->getLogger()->debug("Setting players in arena '".$this->name."' ".($freeze ? "immobile" : "mobile"));
        foreach($this->players as $name){
            $this->plugin->getServer()->getPlayerExact($name)->setImmobile($freeze);
        }
    }

    /** @return null|string */
    public function startTimer(){
        $event = new ArenaPreStartEvent($this->plugin, $this);
        $event->call();

        if($event->isCancelled()){
            return $event->getReason();
        }
        $this->timerTask = $this->plugin->getScheduler()->scheduleRepeatingTask(new StartCountdown($this->plugin, $this, $event->getCountDown()),20);
        $this->plugin->getLogger()->debug("Started Prestart task for arena '".$this->name."'.");
        return null;
    }

    public function startGame(): void{
        $event = new ArenaStartEvent($this->plugin, $this);
        $event->call();

        if($event->isCancelled()){
            $this->plugin->getLogger()->warning($this->plugin->prefix.C::RED."Cant start game in Arena '".$this->getName()."' because: ".$event->getReason());
            return;
        }
        $this->plugin->getLogger()->debug("Starting arena '".$this->name."'...");
        $this->timerTask->cancel();
        $this->started = true;
        $this->checkStatus();
        $msg = str_replace("{ARENA}", $this->name, $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["start"]));
        if($this->plugin->config["start_bcast_serverwide"] === true){
            $this->plugin->getServer()->broadcastMessage($msg);
        } else {
            $this->broadcastMessage($msg);
        }
        $this->createKingTextParticle(); //in case it was never made on startup as it was first made.
        $this->updateKingTextParticle(); //spawn in here.
        $this->timerTask = $this->plugin->getScheduler()->scheduleRepeatingTask(new GameTimer($this),10);
        $this->plugin->getLogger()->debug("Started arena '".$this->name."'.");
    }

    public function reset(): void{
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

    public function endGame(): void{
        $event = new ArenaEndEvent($this->plugin, $this);
        $event->call();

        if($event->isCancelled()){
        	/** @var GameTimer $tsk */
        	$tsk = $this->timerTask->getTask();
            $tsk->secondsLeft = $event->getSecondsLeft();
            $this->plugin->getLogger()->warning($this->plugin->prefix.C::RED."Arena '".$this->name."' not ended, reason: ".$event->getReason());
            return;
        }
        $msg = str_replace("{ARENA}", $this->name, $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["end"]));
        if($this->plugin->config["end_bcast_serverwide"] === true){
            $this->plugin->getServer()->broadcastMessage($msg);
        } else {
            $this->broadcastMessage($msg);
        }
        $this->plugin->getLogger()->debug("Arena '".$this->name."' ended.");
        $this->freezeAll(true);
        $king = "Null";
        $this->timerTask->cancel();
        if($this->king !== null){
            $king = $this->king;
        } else {
            if($this->oldKing !== null){
                $king = $this->oldKing;
            }
        }
        $this->setWinner($king);
        $this->reset();
        $this->checkStatus();
    }

    /**
     * @param string $king
     */
    public function setWinner(string $king): void{
        if($king === "Null"){
            $this->broadcastMessage($this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["no_winner"]));
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
        //todo particles fireworks and more for king, Beta4
        $this->freezeAll(false);
    }

    /**
     * @return array
     */
    public function getPlayers(): array{
        return $this->players;
    }

    /**
     * @return array
     */
    public function playersInBox(): array{
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

    public function removeKing(): void{
        if($this->king === null) return;
        $this->broadcastMessage(str_replace("{PLAYER}", $this->king, $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["fallen_king"])));
        $this->changeking();
    }

    public function changeKing(): void{
        if($this->king !== null){
            $this->oldKing = $this->king;
            $this->king = null;
        }
        $this->updateKingTextParticle();
    }

    /**
     * @return bool
     */
    public function checkNewKing(): bool{
        if(count($this->playersInBox()) === 0){
            return false;
        } else {
            $player = $this->playersInBox()[array_rand($this->playersInBox())]; //todo closest to middle, Beta4
            $this->broadcastMessage(str_replace("{PLAYER}", $player, $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["new_king"])));
            $this->king = $player;
            $this->updateKingTextParticle();
            return true;
        }
    }


    /**
     * @param Player $player
     * @param string $reason
     * @param bool   $silent
     * 
     * @return void
     */
    public function removePlayer(Player $player, string $reason, bool $silent = false): void{
        $event = new ArenaRemovePlayerEvent($this->plugin, $this, $player, $reason, $silent);
        $event->call();
        if($event->isCancelled()){
            if(!$player->isConnected()){
                //Player is leaving app.
                $this->plugin->getLogger()->warning($this->plugin->prefix . C::RED . "Event cancelled, but player is leaving app so will be removed anyway.");
            } else {
                $player->sendMessage($this->plugin->prefix.C::RED."Cannot leave the arena, reason: ".$event->getReason());
                return;
            }
        }
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
     * @param Player $player
     * 
     * @return bool
     */
    public function addPlayer(Player $player): bool{
        if($this->plugin->getArenaByPlayer(strtolower($player->getName())) !== null){
            $player->sendMessage($this->plugin->prefix.C::RED."You are in a arena, type /koth leave before joining another one.");
            return false;
        }
        switch($this->status){
            case self::STATUS_NOT_READY:
                $player->sendMessage($this->plugin->prefix.C::RED."This arena has not been setup.");
                return false;
            case self::STATUS_FULL:
                $player->sendMessage($this->plugin->prefix.C::RED."This arena is full.");
                return false;
            case self::STATUS_INVALID:
                $player->sendMessage($this->plugin->prefix.C::RED."This arena has been setup in a place that no longer exists.");
                return false;
            case self::STATUS_UNKNOWN:
                $player->sendMessage($this->plugin->prefix.C::RED."This arena has a unknown status.");
                return false;
        }
        $event = new ArenaAddPlayerEvent($this->plugin, $this, $player);
        $event->call();
        if($event->isCancelled()){
            $player->sendMessage($this->plugin->prefix.C::RED."Unable to join arena, reason: ".$event->getReason());
            return false;
        }

        $this->playerOldPositions[strtolower($player->getName())] = [$player->getLevel()->getName(),$player->getX(), $player->getY(), $player->getZ()];
        if(!$this->spawnPlayer($player)){
            unset($this->playerOldPositions[strtolower($player->getName())]);
            return false;
        }
        $player->setGamemode(0); //todo Beta4 configurable.
        $this->players[] = strtolower($player->getName());
        $this->broadcastJoin($player);
        if(count($this->players) >= $this->minPlayers && $this->timerTask === null && $this->plugin->config["auto_start"] === true){
            $this->startTimer();
        }
        $this->checkStatus();
        return true;
    }
}