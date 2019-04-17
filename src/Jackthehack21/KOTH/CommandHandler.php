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

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat as C;

class CommandHandler{

    private $plugin;
    private $prefix;
    private $particle;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->prefix = $plugin->prefix;
        $this->particle = new FloatingText($this->plugin, $this->plugin->getServer()->getDefaultLevel(), $this->plugin->getServer()->getDefaultLevel()->getSpawnLocation()->asVector3(), "Test text\nNew Line\nNew Line", "Test Title");
    }

    public function handleCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        if($cmd->getName() == "koth"){
            if (!$sender->hasPermission("koth")) {
                $sender->sendMessage($this->prefix.C::RED ."You do not have permission to use this command!");
                return true;
            }
            if(!isset($args[0])){
                $sender->sendMessage($this->prefix.C::RED."Unknown Command, /koth help");
                return true;
            }
            if(!$sender instanceof Player){
                $sender->sendMessage($this->prefix.C::RED."Commands can only be run in-game");
                return true;
            }
            switch($args[0]){
                /*case 'test':
                    $this->plugin->getServer()->getDefaultLevel()->addParticle($this->particle);
                    return true;
                case 'test2':
                    $this->particle->setTitle("New TITLE");
                    return true;
                case 'test3':
                    $this->particle->setInvisible();
                    //$this->particle->setInvisible(false);
                    return true;
                case 'test4':
                    $this->particle->setInvisible(false);
                    return true;
                case 'update':
                    $this->plugin->getServer()->getDefaultLevel()->addParticle($this->particle);
                    return true;*/
                case 'help':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." HELP".C::YELLOW."]");
                    $sender->sendMessage(C::GOLD."/koth help ".C::RESET."- Sends help :)");
                    $sender->sendMessage(C::GOLD."/koth credits ".C::RESET."- Display the credits.");
                    if($sender->hasPermission("koth.list")) $sender->sendMessage(C::GOLD."/koth list ".C::RESET."- List all arena's setup and ready to play !");
                    if($sender->hasPermission("koth.join")) $sender->sendMessage(C::GOLD."/koth join (arena name)".C::RESET." - Join a game.");
                    if($sender->hasPermission("koth.new")) $sender->sendMessage(C::GOLD."/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)".C::RESET." - Start the setup process of making a new arena.");
                    if($sender->hasPermission("koth.rem")) $sender->sendMessage(C::GOLD."/koth rem (arena name)".C::RESET." - Remove a area that has been setup.");
                    return true;
                case 'credits':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." CREDITS".C::YELLOW."]");
                    $sender->sendMessage(C::AQUA."Developer: ".C::GOLD."Jackthehack21");
                    return true;
                case 'list':
                    if(!$sender->hasPermission("koth.list")){
                        $sender->sendMessage($this->prefix.C::RED."You do not have permission to use this command!");
                        return true;
                    }
                    //list all arena's
                    $this->listArenas($sender);
                    return true;
                case 'rem':
                case 'remove':
                case 'del':
                case 'delete':
                    if(!$sender->hasPermission("koth.rem")){
                        $sender->sendMessage($this->prefix.C::RED."You do not have permission to use this command!");
                        return true;
                    }
                    if(count($args) !== 2){
                        $sender->sendMessage($this->prefix.C::RED."Usage /koth rem (arena name)");
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->prefix.C::RED."A arena with that name does not exist.");
                        return true;
                    }
                    if($arena->getStatus() !== Arena::STATUS_READY and $arena->getStatus() !== Arena::STATUS_NOT_READY){
                        //in middle of game.
                        $sender->sendMessage($this->prefix.C::RED."That arena is currently running a game, when everyone has left the arena you can then remove it.");
                        return true;
                    }
                    $this->plugin->removeArena($arena);
                    $sender->sendMessage($this->prefix.C::GREEN."Arena removed.");
                    return true;
                case 'create':
                case 'make':
                case 'new':
                    if(!$sender->hasPermission("koth.new")){
                        $sender->sendMessage($this->prefix.C::RED ."You do not have permission to use this command!");
                        return true;
                    }
                    //create arena.
                    $this->createArena($sender, $args);
                    return true;

                case 'join':
                    if(!$sender->hasPermission("koth.join")){
                        $sender->sendMessage($this->prefix.C::RED ."You do not have permission to use this command!");
                        return true;
                    }
                    if(count($args) !== 2){
                        $sender->sendMessage($this->prefix.C::RED."No arena specified, /koth join (arena nzme)");
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->prefix.C::RED."A arena with that name does not exist.");
                        return true;
                    }
                    $arena->addPlayer($sender);
                    return true;

                //////-----Arena Setup------///////
                /** @noinspection SpellCheckingInspection */
                case 'setpos1':
                    //Set position one of the hill.
                    if(!$sender->hasPermission("koth.setpoints")){
                        $sender->sendMessage($this->prefix.C::RED ."You do not have permission to use this command!");
                        return true;
                    }
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z]; //get first to avoid split second movement *shrug*
                    if(count($args) !== 2){
                        $sender->sendMessage($this->prefix.C::RED."No arena specified, /koth setpos1 (arenaName)");
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->prefix.C::RED."A arena with that name does not exist.");
                        return true;
                    }
                    if($arena->status !== Arena::STATUS_NOT_READY or count($arena->hill) !== 0){
                        $sender->sendMessage($this->prefix.C::RED."That arena has already been setup (coming soon over-ride stored values)."); //TODO overwrite data.
                        return true;
                    }
                    $arena->hill[] = $point;
                    $arena->world = $sender->getLevel()->getName();
                    $sender->sendMessage($this->prefix.C::GREEN."Position 1 set, be sure to do /koth setpos2 ".$arena->getName());
                    return true;

                /** @noinspection SpellCheckingInspection */
                case 'setpos2':
                    //Set position two of the hill.
                    if(!$sender->hasPermission("koth.setpoints")){
                        $sender->sendMessage($this->prefix.C::RED ."You do not have permission to use this command!");
                        return true;
                    }
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z]; //get first to avoid split second movement *shrug*
                    if(count($args) !== 2){
                        $sender->sendMessage($this->prefix.C::RED."No arena specified, /koth setpos2 (arenaName)");
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->prefix.C::RED."A arena with that name does not exist.");
                        return true;
                    }
                    if($arena->status !== Arena::STATUS_NOT_READY ){
                        $sender->sendMessage($this->prefix.C::RED."That arena has already been setup (coming soon over-ride stored values)."); //TODO overwrite data.
                        return true;
                    }
                    if(count($arena->hill) === 0){
                        $sender->sendMessage($this->prefix.C::RED."That arena has no position1 set, please use /koth setpos1 ".$arena->getName()." First.");
                        return true;
                    }
                    $arena->hill[] = $point;
                    $arena->checkStatus();
                    $sender->sendMessage($this->prefix.C::GREEN."Position 2 set, be sure to setup some spawn point '/koth setspawn ".$arena->getName());
                    return true;

                /** @noinspection SpellCheckingInspection */
                case 'setspawn':
                    //Set a spawn position
                    if(!$sender->hasPermission("koth.setspawns")){
                        $sender->sendMessage($this->prefix.C::RED ."You do not have permission to use this command!");
                        return true;
                    }
                    $pos = $sender->getPosition();
                    $point = [$pos->x, $pos->y, $pos->z]; //get first to avoid split second movement *shrug*
                    if(count($args) !== 2){
                        $sender->sendMessage($this->prefix.C::RED."No arena specified, /koth setspawn (arenaName)");
                        return true;
                    }
                    $arena = $this->plugin->getArenaByName($args[1]);
                    if($arena === null){
                        $sender->sendMessage($this->prefix.C::RED."A arena with that name does not exist.");
                        return true;
                    }
                    $arena->spawns[] = $point;
                    $arena->checkStatus();
                    $sender->sendMessage($this->prefix.C::GREEN."spawn position added.");
                    return true;

                //////----------------------///////

                default:
                    $sender->sendMessage($this->prefix.C::RED."Unknown Command, /koth help");
                    return true;
            }
        }
        return false;
    }

    //todo API class for all the functions below.
    private function listArenas(CommandSender $sender) : void{
        $list = $this->plugin->getAllArenas();
        if(count($list) === 0){
            $sender->sendMessage($this->prefix.C::RED."No arenas are currently setup.");
            return;
        }
        $sender->sendMessage($this->prefix.C::RED.count($list).C::GOLD." Arena(s) - ".C::RED."Arena Name | Arena Status");
        foreach($list as $arena){
            $sender->sendMessage(C::GREEN.$arena->getName().C::RED." | ".C::AQUA.$arena->getFriendlyStatus());
        }
    }

    private function createArena(CommandSender $sender, array $args) : void{
        //assume has perms as it got here.

        $usage = $this->prefix.C::RED."/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)";
        //rest will be in config, or default for now (rem after coming out of beta)

        if(count($args) !== 5){
            $sender->sendMessage($usage);
            return;
        }
        $minGametime = 60; //1min, todo config.
        $maxGametime = 300; //5min, todo config.
        $forceMax = 20; //todo config.

        $name = $args[1];
        $min = $args[2];
        $max = $args[3];
        $gameTime = $args[4];

        //verify data:
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
        if(intval($max) > $forceMax){
            $sender->sendMessage($this->prefix.C::RED."The maximum number of players cannot be above ".$forceMax);
            return;
        }

        if(!is_numeric($gameTime)){
            $sender->sendMessage($this->prefix.C::RED."Game time has to be numbers :/");
            return;
        }
        if(intval($gameTime) < $minGametime or intval($gameTime) > $maxGametime){
            $sender->sendMessage($this->prefix.C::RED."Game time has to be between ".$minGametime." and ".$maxGametime);
            return;
        }

        //create arena
        $arena = new Arena($this->plugin, $name, intval($min), intval($max), intval($gameTime), [], [], "null");
        $result = $this->plugin->newArena($arena);
        if($result === false){
            $sender->sendMessage($this->prefix.C::RED."Failed to create arena, sorry.");
            return;
        }
        $sender->sendMessage($this->prefix.C::GREEN."Nice one, ".$name." arena is almost fully setup, to complete the arena setup be sure to do '/koth setpos1 (arena name)' when standing on pos 1, and '/koth setpos2 (arena name)' when standing in the opposite corner.");
        $sender->sendMessage(C::GREEN."You then setup spawn points, any amount of spawn points, set one by using the command '/koth setspawn (arena name)' when standing on the spawn point.");
        return;
    }
}