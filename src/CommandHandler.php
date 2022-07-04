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

use JaxkDev\KOTH\Events\ArenaCreateEvent;
use JaxkDev\KOTH\Events\ArenaDeleteEvent;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class CommandHandler{
    private Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param Player|ConsoleCommandSender $player
     * @param string[] $args
     * @return void
     */
    public function handleCommand(Player|ConsoleCommandSender $player, array $args): void{
        switch($args[0]??"help"){
            case 'help':
                $player->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN."CONSOLE HELP".C::YELLOW."]");
                $player->sendMessage(C::GOLD."/koth help ".C::RESET."- Sends help :)");
                $player->sendMessage(C::GOLD."/koth credits ".C::RESET."- Display the credits.");
                if(!$player instanceof Player){
                    $player->sendMessage(C::GOLD."/koth list ".C::RESET."- List all arena's setup and ready to play !");
                    $player->sendMessage(C::GOLD."/koth info (arena name) ".C::RESET."- Get more info on one arena.");
                    $player->sendMessage(C::GOLD."/koth start (arena name) ".C::RESET."- Starts a arena if game requirements are met.");
                    $player->sendMessage(C::GOLD."/koth forcestart (arena name) ".C::RESET."- Forces a arena/game to start the countdown to begin.");
                    $player->sendMessage(C::GOLD."/koth enable (arena name)".C::RESET." - Enables a disabled arena, when enabled players can join etc.");
                    $player->sendMessage(C::GOLD."/koth disable (arena name".C::RESET." - Disable a arena, when disabled no one can use it at all.");
                }else{
                    if($player->hasPermission("koth.command.list")) $player->sendMessage(C::GOLD."/koth list ".C::RESET."- List all arena's setup and ready to play !");
                    if($player->hasPermission("koth.command.info")) $player->sendMessage(C::GOLD."/koth info (arena name) ".C::RESET."- Get more info on one arena.");
                    if($player->hasPermission("koth.command.join")) $player->sendMessage(C::GOLD."/koth join (arena name)".C::RESET." - Join a game.");
                    if($player->hasPermission("koth.command.leave")) $player->sendMessage(C::GOLD."/koth leave ".C::RESET."- Leave a game you'r currently in.");
                    if($player->hasPermission("koth.command.start")) $player->sendMessage(C::GOLD."/koth start (arena name - optional) ".C::RESET."- Starts a arena if game requirements are met.");
                    if($player->hasPermission("koth.command.forcestart")) $player->sendMessage(C::GOLD."/koth forcestart (arena name - optional) ".C::RESET."- Forces a arena/game to start the countdown to begin.");
                    if($player->hasPermission("koth.command.create")){
                        $player->sendMessage(C::GOLD."/koth create (arena name - no spaces) (min players) (max players) (gametime in seconds)".C::RESET." - Start the setup process of making a new arena.");
                        $player->sendMessage(C::GOLD."/koth setspawn (arena name) ".C::RESET."- Set a spawn point for a arena.");
                        $player->sendMessage(C::GOLD."/koth setpos1 (arena name) or /koth setpos2 (arena name> ".C::RESET."- Set king area corner to corner.");
                    }
                    if($player->hasPermission("koth.command.delete")) $player->sendMessage(C::GOLD."/koth delete (arena name)".C::RESET." - Delete a area that has been setup.");
                    if($player->hasPermission("koth.command.enable")) $player->sendMessage(C::GOLD."/koth enable (arena name)".C::RESET." - Enables a disabled arena, when enabled players can join etc.");
                    if($player->hasPermission("koth.command.disable")) $player->sendMessage(C::GOLD."/koth disable (arena name".C::RESET." - Disable a arena, when disabled no one can use it at all.");
                    if($player->hasPermission("koth.command.rewards")) $player->sendMessage(C::GOLD."/koth addreward (arena name) (command eg. /give {PLAYER} 20 1)".C::RESET." - Add a command to execute when winner is announced");
                }
                return;
            case 'credits':
                $player->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." CREDITS".C::YELLOW."]");
                $player->sendMessage(C::AQUA."Developer: ".C::GOLD."JaxkDev");
                $player->sendMessage(C::AQUA."Icon creator: ".C::GOLD."WowAssasin#6608");
                return;
            case 'list':
                if(!$player->hasPermission("koth.command.list")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                $this->listArenas($player);
                return;
            case 'remove':
            case 'delete':
                if($player instanceof ConsoleCommandSender){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game", "{PREFIX}{RED}Commands can only be run in-game.")));
                    return;
                }
                if(!$player->hasPermission("koth.command.delete")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                $this->deleteArena($player, $args);
                return;
            case 'create':
            case 'new':
                if($player instanceof ConsoleCommandSender){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game", "{PREFIX}{RED}Commands can only be run in-game.")));
                    return;
                }
                if(!$player->hasPermission("koth.command.create")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                $this->createArena($player, $args);
                return;

            case 'quit':
            case 'exit':
            case 'leave':
                if($player instanceof ConsoleCommandSender){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game", "{PREFIX}{RED}Commands can only be run in-game.")));
                    return;
                }
                if(!$player->hasPermission("koth.command.leave")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                $arena = $this->plugin->getArenaByPlayer(strtolower($player->getName()));
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_in_game_leave", "{PREFIX}{RED}Your not in a game, so how can you leave a game...")));
                    return;
                }
                $arena->removePlayer($player, $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("arenas.leave_message", "Chickened Out.")));
                return;

            case 'join':
                if($player instanceof ConsoleCommandSender){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game", "{PREFIX}{RED}Commands can only be run in-game.")));
                    return;
                }
                if(!$player->hasPermission("koth.command.join")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                if($this->plugin->getArenaByPlayer(strtolower($player->getName())) !== null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game_join", "{PREFIX}{RED}Your currently in a game, please leave the game using /koth leave to join another one.")));
                    return;
                }
                if(count($args) !== 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth join (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }
                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                $arena->addPlayer($player);
                return;

            case 'details':
            case 'info':
                if(!$player->hasPermission("koth.command.info")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                if(count($args) !== 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth info (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }

                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                $name = $arena->getName();
                $status = $arena->getFriendlyStatus();
                $players = count($arena->getPlayers());
                $spawns = count($arena->getSpawns());
                $rewards = $arena->getRewards();
                $gameTime = $arena->getTime();

                $player->sendMessage(Main::PREFIX.C::AQUA.$name." Info:");
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
                if(!$player->hasPermission("koth.command.start")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                if(count($args) < 2 and $this->plugin->getArenaByPlayer(strtolower($player->getName())) === null){
                    $player->sendMessage(str_replace("{USAGE}", "/koth start (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }
                $arena = $this->plugin->getArenaByPlayer(strtolower($player->getName()));
                if($arena === null){
                    $arena = $this->plugin->getArenaByName($args[1]);
                }
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                if($arena->getStatus() === Arena::STATUS_STARTED){
                    $player->sendMessage(Main::PREFIX.C::RED."Arena already started.");
                    return;
                }
                if($arena->getStatus() !== $arena::STATUS_READY){
                    $player->sendMessage(Main::PREFIX.C::RED."Arena is not 'ready' and so cannot be started.");
                    return;
                }
                $result = $arena->startTimer();
                if($result !== null){
                    $player->sendMessage(Main::PREFIX.C::RED."Arena not started because: ".C::RESET.$result);
                    return;
                }
                $player->sendMessage(Main::PREFIX.C::GREEN."Arena starting now...");
                return;

            case 'forcestart':
                if(!$player->hasPermission("koth.command.forcestart")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                if(count($args) < 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth forcestart (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }

                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                if($arena->getStatus() === Arena::STATUS_STARTED){
                    $player->sendMessage(Main::PREFIX.C::RED."Arena already started.");
                    return;
                }
                if($arena->getStatus() === $arena::STATUS_DISABLED or $arena->getStatus() === $arena::STATUS_INVALID){
                    $player->sendMessage(Main::PREFIX.C::RED."Cannot force a disabled/invalid arena.");
                    return;
                }
                $result = $arena->startTimer(true);
                if($result !== null){
                    $player->sendMessage(Main::PREFIX.C::RED."Arena not started because: ".C::RESET.$result);
                    return;
                }
                $player->sendMessage(Main::PREFIX.C::GREEN."Arena starting now...");
                return;

            case 'enable':
                if(!$player->hasPermission("koth.command.enable")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                if(count($args) < 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth enable (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }

                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                if($arena->getStatus() !== $arena::STATUS_DISABLED){
                    $player->sendMessage(Main::PREFIX.C::RED."Arena is not disabled so how can you enable it...");
                    return;
                }
                if($arena->enable()) $player->sendMessage(Main::PREFIX.C::GREEN."Arena has been enabled.");
                else $player->sendMessage(Main::PREFIX.C::RED."Arena could not be enabled.");
                return;

            case 'disable':
                if(!$player->hasPermission("koth.command.disable")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                if(count($args) < 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth disable (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }

                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                if($arena->getStatus() === $arena::STATUS_DISABLED){
                    $player->sendMessage(Main::PREFIX.C::RED."Arena is not enabled so how can you enable it...");
                    return;
                }
                if($arena->disable()) $player->sendMessage(Main::PREFIX.C::GREEN."Arena has been disabled.");
                else $player->sendMessage(Main::PREFIX.C::RED."Arena could not be disabled, make sure its empty and its not running before disabling.");
                //TODO force enable/disable.
                return;

            //////-----Arena Setup------///////
            case 'setpos1':
                if($player instanceof ConsoleCommandSender){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game", "{PREFIX}{RED}Commands can only be run in-game.")));
                    return;
                }
                //Set position one of the hill.
                if(!$player->hasPermission("koth.command.create")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                $pos = $player->getPosition();
                $point = [$pos->x, $pos->y, $pos->z];
                if(count($args) !== 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth setpos1 (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }
                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                $hill = $arena->getHill();
                $hill[0] = $point;
                if(isset($arena->getHill()[0])){
                    $arena->setHill($hill);
                    $arena->setWorld($player->getWorld()->getDisplayName());
                    $player->sendMessage(Main::PREFIX.C::GREEN."Position 1 Re-set");
                    return;
                }
                $arena->setHill($hill);
                $arena->setWorld($player->getWorld()->getDisplayName());
                $player->sendMessage(Main::PREFIX.C::GREEN."Position 1 set, be sure to do /koth setpos2 ".$arena->getName());
                return;

            case 'setpos2':
                if($player instanceof ConsoleCommandSender){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game", "{PREFIX}{RED}Commands can only be run in-game.")));
                    return;
                }
                if(!$player->hasPermission("koth.command.create")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                $pos = $player->getPosition();
                $point = [$pos->x, $pos->y, $pos->z];
                if(count($args) !== 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth setpos2 (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }
                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                $hill = $arena->getHill();
                $hill[1] = $point;
                if(count($arena->getHill()) === 2){
                    $arena->setHill($hill);
                    $player->sendMessage(Main::PREFIX.C::GREEN."Position 2 re-set");
                    return;
                }
                if(count($arena->getHill()) === 0){
                    $arena->setHill($hill);
                    $player->sendMessage(Main::PREFIX.C::RED."Position 2 set, please use /koth setpos1 ".$arena->getName()." as well !");
                    return;
                }
                $arena->setHill($hill);
                $arena->checkStatus();
                $player->sendMessage(Main::PREFIX.C::GREEN."Position 2 set, be sure to setup some spawn point '/koth setspawn ".$arena->getName());
                return;

            case 'setspawn':
                //Set a spawn position
                if($player instanceof ConsoleCommandSender){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.in_game", "{PREFIX}{RED}Commands can only be run in-game.")));
                    return;
                }
                if(!$player->hasPermission("koth.command.create")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                $pos = $player->getPosition();
                $point = [$pos->x, $pos->y, $pos->z];
                if(count($args) !== 2){
                    $player->sendMessage(str_replace("{USAGE}", "/koth setspawn (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }
                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                $spawns = $arena->getSpawns();
                $spawns[] = $point;
                $arena->setSpawns($spawns);
                $arena->checkStatus();
                $player->sendMessage(Main::PREFIX.C::GREEN."Spawn position added.");
                return;

            case 'addreward':
                if(!$player->hasPermission("koth.command.rewards")){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.no_perms", "{PREFIX}{RED}You do not have permission to use this command!")));
                    return;
                }
                if(count($args) <= 2){
                    $player->sendMessage(str_replace("{USAGE}", " /koth addreward (arena name) (command eg. give {PLAYER} 20 1)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
                    return;
                }
                $arena = $this->plugin->getArenaByName($args[1]);
                if($arena === null){
                    $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
                    return;
                }
                if($args[2][0] === "/"){
                    $args[2] = substr($args[2], 1);
                }
                unset($args[0]);
                unset($args[1]);
                $cmd = array_values($args);
                $rewards = $arena->getRewards();
                $rewards[] = implode(" ", $cmd);
                $arena->setRewards($rewards);
                $this->plugin->updateArena($arena);
                $player->sendMessage(Main::PREFIX.C::GREEN."Reward added to the ".$arena->getName()." Arena.");
                return;
        }
        $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.unknown", "{PREFIX}{RED}Unknown command, try /koth help")));
    }

	private function listArenas(Player|ConsoleCommandSender $player): void{
        $list = $this->plugin->getAllArenas();
        if(count($list) === 0){
            $player->sendMessage(Main::PREFIX.C::RED."There are no arena's");
            return;
        }
        $player->sendMessage(Main::PREFIX.C::RED.count($list).C::GOLD." Arena(s) - ".C::RED."Arena Name | Arena Status");
        foreach($list as $arena){
            $player->sendMessage(C::GREEN.$arena->getName().C::RED." | ".C::AQUA.$arena->getFriendlyStatus());
        }
    }


    /**
     * @param Player|ConsoleCommandSender $player
     * @param string[] $args
     * @return void
     */
	protected function deleteArena(Player|ConsoleCommandSender $player, array $args): void{
        if(count($args) !== 2){
            $player->sendMessage(str_replace("{USAGE}", "/koth delete (arena name)", $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
            return;
        }
        $arena = $this->plugin->getArenaByName($args[1]);
        if($arena === null){
            $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_exist", "{PREFIX}{RED}The specified arena does not exist.")));
            return;
        }
        if($arena->getStatus() === Arena::STATUS_STARTED){
            $player->sendMessage($this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.not_while_running", "{PREFIX}{RED}That arena is currently running, please stop it first.")));
            return;
        }

        $event = new ArenaDeleteEvent($this->plugin, $player, $arena);
        $event->call();

        if($event->isCancelled()){
            $player->sendMessage(Main::PREFIX.C::RED."Arena not removed, reason: ".$event->getReason());
            return;
        }

        $this->plugin->deleteArena($arena);
        $player->sendMessage(Main::PREFIX.C::GREEN."Arena Removed.");
    }

    /**
     * @param Player|ConsoleCommandSender $player
     * @param string[] $args
     * @return void
     */
	protected function createArena(Player|ConsoleCommandSender $player, array $args): void{
        $usage = "/koth create (arena name - no spaces) (min players) (max players) (gametime in seconds)";

        if(count($args) !== 5){
            $player->sendMessage(str_replace("{USAGE}", $usage, $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("commands.usage", "{PREFIX}{RED}Usage Incorrect, {USAGE}"))));
            return;
        }

        $name = $args[1];
        $min = $args[2];
        $max = $args[3];
        $gameTime = $args[4];

        if($this->plugin->getArenaByName($name) !== null){
            $player->sendMessage(Main::PREFIX.C::RED."A arena with that name already exists.");
            return;
        }
        if(!is_numeric($min)){
            $player->sendMessage(Main::PREFIX.C::RED."Min value must be a number.");
            return;
        }
        if(intval($min) < 2){
            $player->sendMessage(Main::PREFIX.C::RED."minimum value must be above 2.");
            return;
        }
        if(!is_numeric($max)){
            $player->sendMessage(Main::PREFIX.C::RED."Max value must be a number.");
            return;
        }
        if(intval($max) <= intval($min)){
            $player->sendMessage(Main::PREFIX.C::RED."Cant play with 1 player, make sure max value is bigger then min.");
            return;
        }

        if(!is_numeric($gameTime)){
            $player->sendMessage(Main::PREFIX.C::RED."Game time must be a number.");
            return;
        }
        if(intval($gameTime) < 5){
            $player->sendMessage(Main::PREFIX.C::RED."Game time has to be above 5 seconds.");
            return;
        }

        $event = new ArenaCreateEvent($this->plugin, $player, $name, intval($min), intval($max), intval($gameTime));
        $event->call();

        if($event->isCancelled()){
            $player->sendMessage(Main::PREFIX.C::RED."Arena not created, reason: ".$event->getReason());
            return;
        }

        $arena = new Arena($this->plugin, $event->getName(), $event->getMinPlayers(), $event->getMaxPlayers(), $event->getGameTime(), $event->getHillPositions(), $event->getSpawnPositions(), $event->getRewards(), $event->getWorld());
        $this->plugin->createArena($arena);

        $player->sendMessage(Main::PREFIX.C::GREEN."Nice one, ".$name." arena is almost fully setup, to complete the arena setup be sure to do '/koth setpos1 (arena name)' when standing on pos 1, and '/koth setpos2 (arena name)' when standing in the opposite corner.");
        $player->sendMessage(C::GREEN."You then setup spawn points, any amount of spawn points, set one by using the command '/koth setspawn (arena name)' when standing on the spawn point.");
    }
}