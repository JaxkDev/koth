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
*   Copyright (C) 2019 JaxkDev
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
*   Email   :: JaxkDev@gmail.com
*/


declare(strict_types=1);
namespace Jackthehack21\KOTH;

use Jackthehack21\KOTH\Events\{ArenaCreateEvent, ArenaDeleteEvent};;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use ReflectionException;

class CommandHandler{

    private $plugin;
    private $prefix;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->prefix = $plugin->prefix;
    }

    public function handleCommand(CommandSender $sender, Command $cmd, /** @noinspection PhpUnusedParameterInspection */ string $label, array $args): void{
        if($cmd->getName() == "koth"){ //Is this really done server side ?? (if i only register /koth ?)
            if(!$sender instanceof Player and $this->plugin->getServer()->getMotd() !== "Jacks-Test-Server"){  //debug
                $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
                return;
            }
            if(!isset($args[0])){
                $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["unknown"]));
                return;
            }
            switch($args[0]){
                case 'help':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN."CONSOLE HELP".C::YELLOW."]");
                    $sender->sendMessage(C::GOLD."/koth help ".C::RESET."- Sends help :)");
                    $sender->sendMessage(C::GOLD."/koth credits ".C::RESET."- Display the credits.");
                    if($sender instanceof ConsoleCommandSender) {
						$sender->sendMessage(C::GOLD . "/koth list " . C::RESET . "- List all arena's setup and ready to play !");
						$sender->sendMessage(C::GOLD . "/koth info (arena name) " . C::RESET . "- Get more info on one arena.");
						$sender->sendMessage(C::GOLD . "/koth start (arena name) " . C::RESET . "- Starts a arena if game requirements are met.");
						$sender->sendMessage(C::GOLD . "/koth forcestart (arena name) " . C::RESET . "- Forces a arena/game to start the countdown to begin.");
						$sender->sendMessage(C::GOLD . "/koth enable (arena name)" . C::RESET . " - Enables a disabled arena, when enabled players can join etc.");
						$sender->sendMessage(C::GOLD . "/koth disable (arena name" . C::RESET . " - Disable a arena, when disabled no one can use it at all.");
					} else {
						if ($sender->hasPermission("koth.list")) $sender->sendMessage(C::GOLD . "/koth list " . C::RESET . "- List all arena's setup and ready to play !");
						if ($sender->hasPermission("koth.info")) $sender->sendMessage(C::GOLD . "/koth info (arena name) " . C::RESET . "- Get more info on one arena.");
						if ($sender->hasPermission("koth.join")) $sender->sendMessage(C::GOLD . "/koth join (arena name)" . C::RESET . " - Join a game.");
						if ($sender->hasPermission("koth.leave")) $sender->sendMessage(C::GOLD . "/koth leave " . C::RESET . "- Leave a game you'r currently in.");
						if ($sender->hasPermission("koth.start")) $sender->sendMessage(C::GOLD . "/koth start (arena name - optional) " . C::RESET . "- Starts a arena if game requirements are met.");
						if ($sender->hasPermission("koth.forcestart")) $sender->sendMessage(C::GOLD . "/koth forcestart (arena name - optional) " . C::RESET . "- Forces a arena/game to start the countdown to begin.");
						if ($sender->hasPermission("koth.new")) $sender->sendMessage(C::GOLD . "/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)" . C::RESET . " - Start the setup process of making a new arena.");
						if ($sender->hasPermission("koth.rem")) $sender->sendMessage(C::GOLD . "/koth rem (arena name)" . C::RESET . " - Remove a area that has been setup.");
						if ($sender->hasPermission("koth.enable")) $sender->sendMessage(C::GOLD . "/koth enable (arena name)" . C::RESET . " - Enables a disabled arena, when enabled players can join etc.");
						if ($sender->hasPermission("koth.disable")) $sender->sendMessage(C::GOLD . "/koth disable (arena name" . C::RESET . " - Disable a arena, when disabled no one can use it at all.");
						if ($sender->hasPermission("koth.setspawns")) $sender->sendMessage(C::GOLD . "/koth setspawn (arena name) " . C::RESET . "- Set a spawn point for a arena.");
						if ($sender->hasPermission("koth.setpos")) $sender->sendMessage(C::GOLD . "/koth setpos1 (arena name) or /koth setpos2 (arena name> " . C::RESET . "- Set king area corner to corner.");
						if ($sender->hasPermission("koth.addrewards")) $sender->sendMessage(C::GOLD . "/koth addreward (arena name) (command eg. /give {PLAYER} 20 1)" . C::RESET . " - Add a command to execute when winner is announced");
					}
                    return;
                case 'credits':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." CREDITS".C::YELLOW."]");
                    $sender->sendMessage(C::AQUA."Developer: ".C::GOLD."JaxkDev (AKA Jackthehack21)");
                    $sender->sendMessage(C::AQUA."Icon creator: ".C::GOLD."WowAssasin#6608");
                    return;
                case 'list':
                    if(!$sender->hasPermission("koth.list")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $this->listArenas($sender);
                    return;
                case 'rem':
                case 'remove':
                case 'del':
                case 'delete':
                    if(!$sender->hasPermission("koth.rem")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $this->deleteArena($sender, $args);
                    return;
                case 'create':
                case 'make':
                case 'new':
                    if(!$sender->hasPermission("koth.new")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $this->createArena($sender, $args);
                    return;

                case 'quit':
                case 'exit':
                case 'leave':
                    if(!$sender->hasPermission("koth.leave")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $arena = $this->plugin->getArenaByPlayer(strtolower($sender->getName()));
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_in_game_leave"]));
                        return;
                    }
                    $arena->removePlayer($sender, $this->plugin->utils->colourise($this->plugin->messages["arenas"]["leave_message"]));
                    return;

                case 'join':
                    if(!$sender->hasPermission("koth.join")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if($this->plugin->getArenaByPlayer(strtolower($sender->getName())) !== null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game_join"]));
                        return;
                    }
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth join (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    $arena->addPlayer($sender);
                    return;

                case 'details':
                case 'info':
                    if(!$sender->hasPermission("koth.info")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth info (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }

                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    $name = $arena->getName();
                    $status = $arena->getFriendlyStatus();
                    $players = count($arena->getPlayers());
                    $spawns = count($arena->spawns);
                    $rewards = $arena->rewards;
                    $gameTime = $arena->time;

                    $sender->sendMessage($this->prefix.C::AQUA.$name." Info:");
                    $sender->sendMessage(C::GREEN."Status  : ".C::BLUE.$status);
                    $sender->sendMessage(C::GREEN."Gametime: ".C::BLUE.$gameTime." Seconds.");
                    $sender->sendMessage(C::GREEN."Players : ".C::BLUE.$players);
                    $sender->sendMessage(C::GREEN."Spawns  : ".C::BLUE.$spawns);
                    $sender->sendMessage(C::GREEN."Rewards :");
                    foreach($rewards as $reward){
                        $sender->sendMessage("- ".C::AQUA.$reward);
                    }
                    return;

                case 'start':
                    if(!$sender->hasPermission("koth.start")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) < 2 and $this->plugin->getArenaByPlayer(strtolower($sender->getName())) === null){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth start (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByPlayer(strtolower($sender->getName()));
                    if($arena === null){
                        $arena = $this->plugin->getArenaByName($args[1]);
                    }
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if($arena->timerTask !== null){
                        $sender->sendMessage($this->prefix.C::RED."Arena already started.");
                        return;
                    }
                    if($arena->getStatus() !== $arena::STATUS_READY){
                        $sender->sendMessage($this->prefix.C::RED."Arena is not 'ready' and so cannot be started.");
                        return;
                    }
                    $result = $arena->startTimer();
                    if($result !== null){
                        $sender->sendMessage($this->prefix.C::RED."Arena not started because: ".C::RESET.$result);
                        return;
                    }
                    $sender->sendMessage($this->prefix.C::GREEN."Arena starting now...");
                    return;

                case 'forcestart':
                    if(!$sender->hasPermission("koth.forcestart")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) < 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth forcestart (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }

                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if($arena->timerTask !== null){
                        $sender->sendMessage($this->prefix.C::RED."Arena already started.");
                        return;
                    }
                    if($arena->status === $arena::STATUS_DISABLED or $arena->status === $arena::STATUS_INVALID) {
						$sender->sendMessage($this->prefix . C::RED . "Cannot force a disabled/invalid arena.");
						return;
					}
                    $result = $arena->startTimer();
                    if($result !== null){
                        $sender->sendMessage($this->prefix.C::RED."Arena not started because: ".C::RESET.$result);
                        return;
                    }
                    $sender->sendMessage($this->prefix.C::GREEN."Arena starting now...");
                    return;

				case 'enable':
					if(!$sender->hasPermission("koth.enable")){
						$sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
						return;
					}
					if(count($args) < 2){
						$sender->sendMessage(str_replace("{USAGE}", "/koth enable (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
						return;
					}

					$arena = $this->plugin->getArenaByName($args[1]);
					if($arena === null){
						$sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
						return;
					}
					if($arena->status !== $arena::STATUS_DISABLED){
						$sender->sendMessage($this->prefix.C::RED."Arena is not disabled so how can you enable it...");
						return;
					}
					if($arena->enable()) $sender->sendMessage($this->prefix.C::GREEN."Arena has been enabled.");
					else $sender->sendMessage($this->prefix.C::RED."Arena could not be enabled.");
					return;

				case 'disable':
					if(!$sender->hasPermission("koth.disable")){
						$sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
						return;
					}
					if(count($args) < 2){
						$sender->sendMessage(str_replace("{USAGE}", "/koth disable (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
						return;
					}

					$arena = $this->plugin->getArenaByName($args[1]);
					if($arena === null){
						$sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
						return;
					}
					if($arena->status === $arena::STATUS_DISABLED){
						$sender->sendMessage($this->prefix.C::RED."Arena is not enabled so how can you enable it...");
						return;
					}
					if($arena->disable()) $sender->sendMessage($this->prefix.C::GREEN."Arena has been disabled.");
					else $sender->sendMessage($this->prefix.C::RED."Arena could not be disabled, make sure its empty and its not running before disabling.");
					//TODO force enable/disable.
					return;

                //////-----Arena Setup------///////
                case 'setpos1':
                    //Set position one of the hill.
                    if(!$sender->hasPermission("koth.setpoints")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth setpos1 (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if(isset($arena->hill[0])){
                        $arena->hill[0] = $point;
                        $arena->world = $sender->getLevel()->getName();
                        $sender->sendMessage($this->prefix.C::GREEN."Position 1 Re-set");
                        return;
                    }
                    $arena->hill[0] = $point;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $arena->world = $sender->getLevel()->getName();
                    $sender->sendMessage($this->prefix.C::GREEN."Position 1 set, be sure to do /koth setpos2 ".$arena->getName());
                    return;

                case 'setpos2':
                    if(!$sender->hasPermission("koth.setpoints")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth setpos2 (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if(count($arena->hill) === 2){
                        $arena->hill[1] = $point;
                        $sender->sendMessage($this->prefix.C::GREEN."Position 2 re-set");
                        return;
                    }
                    if(count($arena->hill) === 0){
                        $arena->hill[1] = $point;
                        $sender->sendMessage($this->prefix.C::RED."Position 2 set, please use /koth setpos1 ".$arena->getName()." as well !");
                        return;
                    }
                    $arena->hill[1] = $point;
                    $arena->checkStatus();
                    $sender->sendMessage($this->prefix.C::GREEN."Position 2 set, be sure to setup some spawn point '/koth setspawn ".$arena->getName());
                    return;

                case 'setspawn':
                    //Set a spawn position
                    if(!$sender->hasPermission("koth.setspawns")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth setspawn (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    $arena->spawns[] = $point;
                    $arena->checkStatus();
                    $sender->sendMessage($this->prefix.C::GREEN."Spawn position added.");
                    return;

                case 'addreward':
                    if(!$sender->hasPermission("koth.addreward")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) <= 2){
                        $sender->sendMessage(str_replace("{USAGE}", " /koth addreward (arena name) (command eg. give {PLAYER} 20 1)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if($args[2][0] === "/"){
                        $args[2] = substr($args[2], 1);
                    }
                    unset($args[0]);
                    unset($args[1]);
                    $cmd = array_values($args);
                    $arena->rewards[] = implode(" ",$cmd);
                    $this->plugin->updateArena($arena);
                    $sender->sendMessage($this->prefix.C::GREEN."Reward added to the ".$arena->getName()." Arena.");
                    return;

                //////----------------------///////

                default:
                    $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["unknown"]));
                    return;
            }
        }
    }

    private function listArenas(CommandSender $sender) : void{
        $list = $this->plugin->getAllArenas();
        if(count($list) === 0){
            $sender->sendMessage($this->prefix.C::RED."There are no arena's");
            return;
        }
        $sender->sendMessage($this->prefix.C::RED.count($list).C::GOLD." Arena(s) - ".C::RED."Arena Name | Arena Status");
        foreach($list as $arena){
            $sender->sendMessage(C::GREEN.$arena->getName().C::RED." | ".C::AQUA.$arena->getFriendlyStatus());
        }
    }

    private function deleteArena(CommandSender $sender, array $args) : void{
        if(count($args) !== 2){
            $sender->sendMessage(str_replace("{USAGE}", "/koth rem (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
            return;
        }
        $arena = $this->plugin->getArenaByName($args[1]);
        if($arena === null){
            $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
            return;
        }
        if($arena->started === true){
            $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_while_running"]));
            return;
        }

        $event = new ArenaDeleteEvent($this->plugin, $sender, $arena);
        try {
            $event->call();
        } catch (ReflectionException $e) {
            $sender->sendMessage($this->prefix.C::RED."Event failed, Arena not removed.");
            return;
        }

        if($event->isCancelled()){
            $sender->sendMessage($this->prefix.C::RED."Arena not removed, reason: ".$event->getReason());
            return;
        }

        $this->plugin->removeArena($arena);
        $sender->sendMessage($this->prefix.C::GREEN."Arena Removed.");
        return;
    }

    private function createArena(CommandSender $sender, array $args) : void{
        //assuming sender has sufficient perms.

        $usage = "/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)";

        if(count($args) !== 5){
            $sender->sendMessage(str_replace("{USAGE}", $usage, $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
            return;
        }

        $name = $args[1];
        $min = $args[2];
        $max = $args[3];
        $gameTime = $args[4];

        if($this->plugin->getArenaByName($name) !== null){
            $sender->sendMessage($this->prefix.C::RED."A arena with that name already exists.");
            return;
        }
        if(!is_numeric($min)){
            $sender->sendMessage($this->prefix.C::RED."Min value must be a number.");
            return;
        }
        if(intval($min) < 2){
            $sender->sendMessage($this->prefix.C::RED."minimum value must be above 2.");
            return;
        }
        if(!is_numeric($max)){
            $sender->sendMessage($this->prefix.C::RED."Max value must be a number.");
            return;
        }
        if(intval($max) <= intval($min)){
            $sender->sendMessage($this->prefix.C::RED."Cant play with 1 player, make sure max value is bigger then min.");
            return;
        }

        if(!is_numeric($gameTime)){
            $sender->sendMessage($this->prefix.C::RED."Game time must be a number.");
            return;
        }
        if(intval($gameTime) < 5){
            $sender->sendMessage($this->prefix.C::RED."Game time has to be above 5 seconds.");
            return;
        }

        $event = new ArenaCreateEvent($this->plugin, $sender, $name, intval($min), intval($max), intval($gameTime));
        try {
            $event->call();
        } catch (ReflectionException $e) {
            $sender->sendMessage($this->prefix.C::RED."Event failed, Arena not created.");
            return;
        }

        if($event->isCancelled()){
            $sender->sendMessage($this->prefix.C::RED."Arena not created, reason: ".$event->getReason());
            return;
        }

        $arena = new Arena($this->plugin, $event->getName(), $event->getMinPlayers(), $event->getMaxPlayers(), $event->getGameTime(), $event->getHillPositions(), $event->getSpawnPositions(), $event->getRewards(), $event->getWorld());
        $this->plugin->newArena($arena);

        $sender->sendMessage($this->prefix.C::GREEN."Nice one, ".$name." arena is almost fully setup, to complete the arena setup be sure to do '/koth setpos1 (arena name)' when standing on pos 1, and '/koth setpos2 (arena name)' when standing in the opposite corner.");
        $sender->sendMessage(C::GREEN."You then setup spawn points, any amount of spawn points, set one by using the command '/koth setspawn (arena name)' when standing on the spawn point.");
        return;
    }
}