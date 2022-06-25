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
*   Discord :: JaxkDev#2698
*   Email   :: JaxkDev@gmail.com
*/

namespace JaxkDev\KOTH;

use JaxkDev\KOTH\Events\{ArenaCreateEvent, ArenaDeleteEvent};;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class CommandHandler{

    private $plugin;
    private $prefix;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->prefix = $plugin->prefix;
    }

    public function handleCommand(CommandSender $sender, Command $cmd, /** @noinspection PhpUnusedParameterInspection */ string $label, array $args): void{
        /** @var Player|ConsoleCommandSender $player */
    	$player = $this->plugin->getServer()->getPlayerExact($sender->getName()) ?? $sender;

    	if($cmd->getName() == "koth"){ //Is this really done server side ?? (if i only register /koth ?) - YES
            if(!isset($args[0])){
                $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["unknown"]));
                return;
            }
            switch($args[0]){
                case 'help':
                    $player->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN."CONSOLE HELP".C::YELLOW."]");
                    $player->sendMessage(C::GOLD."/koth help ".C::RESET."- Sends help :)");
                    $player->sendMessage(C::GOLD."/koth credits ".C::RESET."- Display the credits.");
                    if($player instanceof ConsoleCommandSender) {
						$player->sendMessage(C::GOLD . "/koth list " . C::RESET . "- List all arena's setup and ready to play !");
						$player->sendMessage(C::GOLD . "/koth info (arena name) " . C::RESET . "- Get more info on one arena.");
						$player->sendMessage(C::GOLD . "/koth start (arena name) " . C::RESET . "- Starts a arena if game requirements are met.");
						$player->sendMessage(C::GOLD . "/koth forcestart (arena name) " . C::RESET . "- Forces a arena/game to start the countdown to begin.");
						$player->sendMessage(C::GOLD . "/koth enable (arena name)" . C::RESET . " - Enables a disabled arena, when enabled players can join etc.");
						$player->sendMessage(C::GOLD . "/koth disable (arena name" . C::RESET . " - Disable a arena, when disabled no one can use it at all.");
					} else {
						if ($player->hasPermission("koth.list")) $player->sendMessage(C::GOLD . "/koth list " . C::RESET . "- List all arena's setup and ready to play !");
						if ($player->hasPermission("koth.info")) $player->sendMessage(C::GOLD . "/koth info (arena name) " . C::RESET . "- Get more info on one arena.");
						if ($player->hasPermission("koth.join")) $player->sendMessage(C::GOLD . "/koth join (arena name)" . C::RESET . " - Join a game.");
						if ($player->hasPermission("koth.leave")) $player->sendMessage(C::GOLD . "/koth leave " . C::RESET . "- Leave a game you'r currently in.");
						if ($player->hasPermission("koth.start")) $player->sendMessage(C::GOLD . "/koth start (arena name - optional) " . C::RESET . "- Starts a arena if game requirements are met.");
						if ($player->hasPermission("koth.forcestart")) $player->sendMessage(C::GOLD . "/koth forcestart (arena name - optional) " . C::RESET . "- Forces a arena/game to start the countdown to begin.");
						if ($player->hasPermission("koth.new")) $player->sendMessage(C::GOLD . "/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)" . C::RESET . " - Start the setup process of making a new arena.");
						if ($player->hasPermission("koth.rem")) $player->sendMessage(C::GOLD . "/koth rem (arena name)" . C::RESET . " - Remove a area that has been setup.");
						if ($player->hasPermission("koth.enable")) $player->sendMessage(C::GOLD . "/koth enable (arena name)" . C::RESET . " - Enables a disabled arena, when enabled players can join etc.");
						if ($player->hasPermission("koth.disable")) $player->sendMessage(C::GOLD . "/koth disable (arena name" . C::RESET . " - Disable a arena, when disabled no one can use it at all.");
						if ($player->hasPermission("koth.setspawns")) $player->sendMessage(C::GOLD . "/koth setspawn (arena name) " . C::RESET . "- Set a spawn point for a arena.");
						if ($player->hasPermission("koth.setpos")) $player->sendMessage(C::GOLD . "/koth setpos1 (arena name) or /koth setpos2 (arena name> " . C::RESET . "- Set king area corner to corner.");
						if ($player->hasPermission("koth.addrewards")) $player->sendMessage(C::GOLD . "/koth addreward (arena name) (command eg. /give {PLAYER} 20 1)" . C::RESET . " - Add a command to execute when winner is announced");
					}
                    return;
                case 'credits':
                    $player->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." CREDITS".C::YELLOW."]");
                    $player->sendMessage(C::AQUA."Developer: ".C::GOLD."JaxkDev (AKA Jackthehack21)");
                    $player->sendMessage(C::AQUA."Icon creator: ".C::GOLD."WowAssasin#6608");
                    return;
                case 'list':
                    if(!$player->hasPermission("koth.list")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $this->listArenas($player);
                    return;
                case 'rem':
                case 'remove':
                case 'del':
                case 'delete':
                	if($player instanceof ConsoleCommandSender) {
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
						return;
					}
                    if(!$player->hasPermission("koth.rem")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $this->deleteArena($player, $args);
                    return;
                case 'create':
                case 'make':
                case 'new':
					if($player instanceof ConsoleCommandSender) {
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
						return;
					}
                    if(!$player->hasPermission("koth.new")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $this->createArena($player, $args);
                    return;

                case 'quit':
                case 'exit':
                case 'leave':
					if($player instanceof ConsoleCommandSender) {
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
						return;
					}
                    if(!$player->hasPermission("koth.leave")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $arena = $this->plugin->getArenaByPlayer(strtolower($player->getName()));
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_in_game_leave"]));
                        return;
                    }
                    $arena->removePlayer($player, $this->plugin->utils->colourise($this->plugin->messages["arenas"]["leave_message"]));
                    return;

                case 'join':
                	if($player instanceof ConsoleCommandSender) {
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
						return;
					}
                    if(!$player->hasPermission("koth.join")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if($this->plugin->getArenaByPlayer(strtolower($player->getName())) !== null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game_join"]));
                        return;
                    }
                    if(count($args) !== 2){
                        $player->sendMessage(str_replace("{USAGE}", "/koth join (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    $arena->addPlayer($player);
                    return;

                case 'details':
                case 'info':
                    if(!$player->hasPermission("koth.info")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) !== 2){
                        $player->sendMessage(str_replace("{USAGE}", "/koth info (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }

                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    $name = $arena->getName();
                    $status = $arena->getFriendlyStatus();
                    $players = count($arena->getPlayers());
                    $spawns = count($arena->spawns);
                    $rewards = $arena->rewards;
                    $gameTime = $arena->time;

                    $player->sendMessage($this->prefix.C::AQUA.$name." Info:");
                    $player->sendMessage(C::GREEN."Status  : ".C::BLUE.$status);
                    $player->sendMessage(C::GREEN."Gametime: ".C::BLUE.$gameTime." Seconds.");
                    $player->sendMessage(C::GREEN."Players : ".C::BLUE.$players);
                    $player->sendMessage(C::GREEN."Spawns  : ".C::BLUE.$spawns);
                    $player->sendMessage(C::GREEN."Rewards :");
                    foreach($rewards as $reward){
                        $player->sendMessage("- ".C::AQUA.$reward);
                    }
                    return;

                case 'start':
                    if(!$player->hasPermission("koth.start")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) < 2 and $this->plugin->getArenaByPlayer(strtolower($player->getName())) === null){
                        $player->sendMessage(str_replace("{USAGE}", "/koth start (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByPlayer(strtolower($player->getName()));
                    if($arena === null){
                        $arena = $this->plugin->getArenaByName($args[1]);
                    }
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if($arena->timerTask !== null){
                        $player->sendMessage($this->prefix.C::RED."Arena already started.");
                        return;
                    }
                    if($arena->getStatus() !== $arena::STATUS_READY){
                        $player->sendMessage($this->prefix.C::RED."Arena is not 'ready' and so cannot be started.");
                        return;
                    }
                    $result = $arena->startTimer();
                    if($result !== null){
                        $player->sendMessage($this->prefix.C::RED."Arena not started because: ".C::RESET.$result);
                        return;
                    }
                    $player->sendMessage($this->prefix.C::GREEN."Arena starting now...");
                    return;

                case 'forcestart':
                    if(!$player->hasPermission("koth.forcestart")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) < 2){
                        $player->sendMessage(str_replace("{USAGE}", "/koth forcestart (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }

                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if($arena->timerTask !== null){
                        $player->sendMessage($this->prefix.C::RED."Arena already started.");
                        return;
                    }
                    if($arena->status === $arena::STATUS_DISABLED or $arena->status === $arena::STATUS_INVALID) {
						$player->sendMessage($this->prefix . C::RED . "Cannot force a disabled/invalid arena.");
						return;
					}
                    $result = $arena->startTimer();
                    if($result !== null){
                        $player->sendMessage($this->prefix.C::RED."Arena not started because: ".C::RESET.$result);
                        return;
                    }
                    $player->sendMessage($this->prefix.C::GREEN."Arena starting now...");
                    return;

				case 'enable':
					if(!$player->hasPermission("koth.enable")){
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
						return;
					}
					if(count($args) < 2){
						$player->sendMessage(str_replace("{USAGE}", "/koth enable (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
						return;
					}

					$arena = $this->plugin->getArenaByName($args[1]);
					if($arena === null){
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
						return;
					}
					if($arena->status !== $arena::STATUS_DISABLED){
						$player->sendMessage($this->prefix.C::RED."Arena is not disabled so how can you enable it...");
						return;
					}
					if($arena->enable()) $player->sendMessage($this->prefix.C::GREEN."Arena has been enabled.");
					else $player->sendMessage($this->prefix.C::RED."Arena could not be enabled.");
					return;

				case 'disable':
					if(!$player->hasPermission("koth.disable")){
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
						return;
					}
					if(count($args) < 2){
						$player->sendMessage(str_replace("{USAGE}", "/koth disable (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
						return;
					}

					$arena = $this->plugin->getArenaByName($args[1]);
					if($arena === null){
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
						return;
					}
					if($arena->status === $arena::STATUS_DISABLED){
						$player->sendMessage($this->prefix.C::RED."Arena is not enabled so how can you enable it...");
						return;
					}
					if($arena->disable()) $player->sendMessage($this->prefix.C::GREEN."Arena has been disabled.");
					else $player->sendMessage($this->prefix.C::RED."Arena could not be disabled, make sure its empty and its not running before disabling.");
					//TODO force enable/disable.
					return;

                //////-----Arena Setup------///////
                case 'setpos1':
					if($player instanceof ConsoleCommandSender) {
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
						return;
					}
                    //Set position one of the hill.
                    if(!$player->hasPermission("koth.setpoints")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $pos = $player->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $player->sendMessage(str_replace("{USAGE}", "/koth setpos1 (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    /** @var Arena|null $arena */
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if(isset($arena->hill[0])){
                        $arena->hill[0] = $point;
                        $arena->world = $player->getLevel()->getName();
                        $player->sendMessage($this->prefix.C::GREEN."Position 1 Re-set");
                        return;
                    }
                    $arena->hill[0] = $point;
                    $arena->world = $player->getLevel()->getName();
                    $player->sendMessage($this->prefix.C::GREEN."Position 1 set, be sure to do /koth setpos2 ".$arena->getName());
                    return;

                case 'setpos2':
					if($player instanceof ConsoleCommandSender) {
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
						return;
					}
                    if(!$player->hasPermission("koth.setpoints")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $pos = $player->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $player->sendMessage(str_replace("{USAGE}", "/koth setpos2 (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    if(count($arena->hill) === 2){
                        $arena->hill[1] = $point;
                        $player->sendMessage($this->prefix.C::GREEN."Position 2 re-set");
                        return;
                    }
                    if(count($arena->hill) === 0){
                        $arena->hill[1] = $point;
                        $player->sendMessage($this->prefix.C::RED."Position 2 set, please use /koth setpos1 ".$arena->getName()." as well !");
                        return;
                    }
                    $arena->hill[1] = $point;
                    $arena->checkStatus();
                    $player->sendMessage($this->prefix.C::GREEN."Position 2 set, be sure to setup some spawn point '/koth setspawn ".$arena->getName());
                    return;

                case 'setspawn':
                    //Set a spawn position
					if($player instanceof ConsoleCommandSender) {
						$player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
						return;
					}
                    if(!$player->hasPermission("koth.setspawns")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    $pos = $player->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $player->sendMessage(str_replace("{USAGE}", "/koth setspawn (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return;
                    }
                    $arena->spawns[] = $point;
                    $arena->checkStatus();
                    $player->sendMessage($this->prefix.C::GREEN."Spawn position added.");
                    return;

                case 'addreward':
                    if(!$player->hasPermission("koth.addreward")){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return;
                    }
                    if(count($args) <= 2){
                        $player->sendMessage(str_replace("{USAGE}", " /koth addreward (arena name) (command eg. give {PLAYER} 20 1)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
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
                    $player->sendMessage($this->prefix.C::GREEN."Reward added to the ".$arena->getName()." Arena.");
                    return;

                //////----------------------///////

                default:
                    $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["unknown"]));
                    return;
            }
        }
    }

	/**
	 * @param Player|ConsoleCommandSender $player
	 */
	private function listArenas($player) : void{
        $list = $this->plugin->getAllArenas();
        if(count($list) === 0){
            $player->sendMessage($this->prefix.C::RED."There are no arena's");
            return;
        }
        $player->sendMessage($this->prefix.C::RED.count($list).C::GOLD." Arena(s) - ".C::RED."Arena Name | Arena Status");
        foreach($list as $arena){
            $player->sendMessage(C::GREEN.$arena->getName().C::RED." | ".C::AQUA.$arena->getFriendlyStatus());
        }
    }


	/**
	 * @param Player|ConsoleCommandSender $player
	 * @param array $args
	 */
	private function deleteArena($player, array $args) : void{
        if(count($args) !== 2){
            $player->sendMessage(str_replace("{USAGE}", "/koth rem (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
            return;
        }
        $arena = $this->plugin->getArenaByName($args[1]);
        if($arena === null){
            $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
            return;
        }
        if($arena->started === true){
            $player->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_while_running"]));
            return;
        }

        $event = new ArenaDeleteEvent($this->plugin, $player, $arena);
        $event->call();

        if($event->isCancelled()){
            $player->sendMessage($this->prefix.C::RED."Arena not removed, reason: ".$event->getReason());
            return;
        }

        $this->plugin->removeArena($arena);
        $player->sendMessage($this->prefix.C::GREEN."Arena Removed.");
        return;
    }

	/**
	 * @param Player|ConsoleCommandSender $player
	 * @param array $args
	 */
	private function createArena($player, array $args) : void{
        //assuming player has sufficient perms.

        $usage = "/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)";

        if(count($args) !== 5){
            $player->sendMessage(str_replace("{USAGE}", $usage, $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
            return;
        }

        $name = $args[1];
        $min = $args[2];
        $max = $args[3];
        $gameTime = $args[4];

        if($this->plugin->getArenaByName($name) !== null){
            $player->sendMessage($this->prefix.C::RED."A arena with that name already exists.");
            return;
        }
        if(!is_numeric($min)){
            $player->sendMessage($this->prefix.C::RED."Min value must be a number.");
            return;
        }
        if(intval($min) < 2){
            $player->sendMessage($this->prefix.C::RED."minimum value must be above 2.");
            return;
        }
        if(!is_numeric($max)){
            $player->sendMessage($this->prefix.C::RED."Max value must be a number.");
            return;
        }
        if(intval($max) <= intval($min)){
            $player->sendMessage($this->prefix.C::RED."Cant play with 1 player, make sure max value is bigger then min.");
            return;
        }

        if(!is_numeric($gameTime)){
            $player->sendMessage($this->prefix.C::RED."Game time must be a number.");
            return;
        }
        if(intval($gameTime) < 5){
            $player->sendMessage($this->prefix.C::RED."Game time has to be above 5 seconds.");
            return;
        }

        $event = new ArenaCreateEvent($this->plugin, $player, $name, intval($min), intval($max), intval($gameTime));
        $event->call();

        if($event->isCancelled()){
            $player->sendMessage($this->prefix.C::RED."Arena not created, reason: ".$event->getReason());
            return;
        }

        $arena = new Arena($this->plugin, $event->getName(), $event->getMinPlayers(), $event->getMaxPlayers(), $event->getGameTime(), $event->getHillPositions(), $event->getSpawnPositions(), $event->getRewards(), $event->getWorld());
        $this->plugin->newArena($arena);

        $player->sendMessage($this->prefix.C::GREEN."Nice one, ".$name." arena is almost fully setup, to complete the arena setup be sure to do '/koth setpos1 (arena name)' when standing on pos 1, and '/koth setpos2 (arena name)' when standing in the opposite corner.");
        $player->sendMessage(C::GREEN."You then setup spawn points, any amount of spawn points, set one by using the command '/koth setspawn (arena name)' when standing on the spawn point.");
        return;
    }
}