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

/** @noinspection PhpMissingBreakStatementInspection */
/** @noinspection PhpUnusedParameterInspection */
//PhpStorm useless warnings.

declare(strict_types=1);
namespace Jackthehack21\KOTH;

use Jackthehack21\KOTH\Events\{ArenaCreateEvent, ArenaDeleteEvent};;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

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

    public function handleCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        if($cmd->getName() == "koth"){ //Is this really done server side ?? (if i only register /koth ?)
            if(!$sender instanceof Player and $this->plugin->getServer()->getMotd() !== "Jacks-Test-Server"){  //To help me debug faster.
                $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game"]));
                return true;
            }
            if(!isset($args[0])){
                $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["unknown"]));
                return true;
            }
            switch($args[0]){
                case 'help':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." HELP".C::YELLOW."]");
                    $sender->sendMessage(C::GOLD."/koth help ".C::RESET."- Sends help :)");
                    $sender->sendMessage(C::GOLD."/koth credits ".C::RESET."- Display the credits.");
                    if($sender->hasPermission("koth.list")) $sender->sendMessage(C::GOLD."/koth list ".C::RESET."- List all arena's setup and ready to play !");
                    if($sender->hasPermission("koth.info")) $sender->sendMessage(C::GOLD."/koth info (arena name) ".C::RESET."- Get more info on one arena.");
                    if($sender->hasPermission("koth.join")) $sender->sendMessage(C::GOLD."/koth join (arena name)".C::RESET." - Join a game.");
                    if($sender->hasPermission("koth.leave")) $sender->sendMessage(C::GOLD."/koth leave ".C::RESET."- Leave a game you'r currently in.");
                    if($sender->hasPermission("koth.new")) $sender->sendMessage(C::GOLD."/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)".C::RESET." - Start the setup process of making a new arena.");
                    if($sender->hasPermission("koth.rem")) $sender->sendMessage(C::GOLD."/koth rem (arena name)".C::RESET." - Remove a area that has been setup.");
                    if($sender->hasPermission("koth.setspawns")) $sender->sendMessage(C::GOLD."/koth setspawn (arena name) ".C::RESET."- Set a spawn point for a arena.");
                    if($sender->hasPermission("koth.setpos")) $sender->sendMessage(C::GOLD."/koth setpos1 (arena name) or /koth setpos2 (arena name> ".C::RESET."- Set king area corner to corner.");
                    if($sender->hasPermission("koth.addrewards")) $sender->sendMessage(C::GOLD."/koth addreward (arena name) (command eg. /give {PLAYER} 20 1)");
                    return true;
                case 'credits':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." CREDITS".C::YELLOW."]");
                    $sender->sendMessage(C::AQUA."Developer: ".C::GOLD."Jackthehack21");
                    $sender->sendMessage(C::AQUA."Icon creator: ".C::GOLD."WowAssasin#6608");
                    return true;
                case 'list':
                    if(!$sender->hasPermission("koth.list")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    $this->listArenas($sender);
                    return true;
                case 'rem':
                case 'remove':
                case 'del':
                case 'delete':
                    if(!$sender->hasPermission("koth.rem")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    $this->deleteArena($sender, $args);
                    return true;
                case 'create':
                case 'make':
                case 'new':
                    if(!$sender->hasPermission("koth.new")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    $this->createArena($sender, $args);
                    return true;

                case 'quit':
                case 'exit':
                case 'leave':
                    if(!$sender->hasPermission("koth.leave")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    $arena = $this->plugin->getArenaByPlayer(strtolower($sender->getName()));
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_in_game_leave"]));
                        return true;
                    }
                    $arena->removePlayer($sender, $this->plugin->utils->colourise($this->plugin->messages["arenas"]["leave_message"]));
                    return true;

                case 'join':
                    if(!$sender->hasPermission("koth.join")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    if($this->plugin->getArenaByPlayer(strtolower($sender->getName())) !== null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["in_game_join"]));
                        return true;
                    }
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth join (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return true;
                    }
                    $arena->addPlayer($sender);
                    return true;

                case 'details':
                case 'info':
                    if(!$sender->hasPermission("koth.info")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth info (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return true;
                    }

                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return true;
                    }
                    $name = $arena->getName();
                    $status = $arena->getFriendlyStatus();
                    $players = count($arena->players);
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
                    return true;

                case 'start': //todo
                case 'forcestart':
                    if(!$sender->hasPermission("koth.forcestart")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth forcestart (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return true;
                    }

                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return true;
                    }
                    if($arena->timerTask !== null){
                        $sender->sendMessage($this->prefix.C::RED."Arena already started.");
                        return true;
                    }

                    $result = $arena->startTimer();
                    if($result !== null){
                        $sender->sendMessage($this->prefix.C::RED."Arena not started because: ".C::RESET.$result);
                        return true;
                    }
                    $sender->sendMessage($this->prefix.C::GREEN."Arena starting now...");
                    return true;

                //////-----Arena Setup------///////
                case 'setpos1':
                    //Set position one of the hill.
                    if(!$sender->hasPermission("koth.setpoints")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth setpos1 (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return true;
                    }
                    if(isset($arena->hill[0])){
                        $arena->hill[0] = $point;
                        $arena->world = $sender->getLevel()->getName();
                        $sender->sendMessage($this->prefix.C::GREEN."Position 1 Re-set");
                        return true;
                    }
                    $arena->hill[0] = $point;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $arena->world = $sender->getLevel()->getName();
                    $sender->sendMessage($this->prefix.C::GREEN."Position 1 set, be sure to do /koth setpos2 ".$arena->getName());
                    return true;

                case 'setpos2':
                    if(!$sender->hasPermission("koth.setpoints")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth setpos2 (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return true;
                    }
                    if(count($arena->hill) === 2){
                        $arena->hill[1] = $point;
                        $sender->sendMessage($this->prefix.C::GREEN."Position 2 re-set");
                        return true;
                    }
                    if(count($arena->hill) === 0){
                        $arena->hill[1] = $point;
                        $sender->sendMessage($this->prefix.C::RED."Position 2 set, please use /koth setpos1 ".$arena->getName()." as well !");
                        return true;
                    }
                    $arena->hill[1] = $point;
                    $arena->checkStatus();
                    $sender->sendMessage($this->prefix.C::GREEN."Position 2 set, be sure to setup some spawn point '/koth setspawn ".$arena->getName());
                    return true;

                case 'setspawn':
                    //Set a spawn position
                    if(!$sender->hasPermission("koth.setspawns")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z];
                    if(count($args) !== 2){
                        $sender->sendMessage(str_replace("{USAGE}", "/koth setspawn (arena name)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return true;
                    }
                    $arena->spawns[] = $point;
                    $arena->checkStatus();
                    $sender->sendMessage($this->prefix.C::GREEN."Spawn position added.");
                    return true;

                case 'addreward':
                    if(!$sender->hasPermission("koth.addreward")){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["no_perms"]));
                        return true;
                    }
                    if(count($args) <= 2){
                        $sender->sendMessage(str_replace("{USAGE}", " /koth addreward (arena name) (command eg. give {PLAYER} 20 1)", $this->plugin->utils->colourise($this->plugin->messages["commands"]["usage"])));
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["not_exist"]));
                        return true;
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
                    return true;

                //////----------------------///////

                default:
                    $sender->sendMessage($this->plugin->utils->colourise($this->plugin->messages["commands"]["unknowns"]));
                    return true;
            }
        }
        return false;
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

        if($this->plugin->getArenaByName($name) !== null){
            $sender->sendMessage($this->prefix.C::RED."A arena under that name already exists.");
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