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
namespace Jack\KOTH;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat as C;

class CommandHandler{

    private $plugin;
    private $prefix;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->prefix = $plugin->prefix;
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
                case 'help':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." HELP".C::YELLOW."]");
                    $sender->sendMessage(C::GOLD."/koth help ".C::RESET."- Sends help :)");
                    $sender->sendMessage(C::GOLD."/koth credits ".C::RESET."- Display the credits.");
                    $sender->sendMessage(C::GOLD."/koth list ".C::RESET."- List all arena's setup and ready to play !");
                    if($sender->hasPermission("koth.join")) $sender->sendMessage(C::GOLD."/koth join <arena name>".C::RESET." - Join a game.");
                    if($sender->hasPermission("koth.new")) $sender->sendMessage(C::GOLD."/koth new <arena name>".C::RESET." - Start the setup process of making a new arena.");
                    if($sender->hasPermission("koth.rem")) $sender->sendMessage(C::GOLD."/koth rem <arena name>".C::RESET." - Remove a area that has been setup.");
                    return true;
                case 'credits':
                    $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." CREDITS".C::YELLOW."]");
                    $sender->sendMessage(C::AQUA."Developer: ".C::GOLD."Jackthehack21");
                    return true;
                case 'make':
                case 'new':
                    if(!$sender->hasPermission("koth.new")){
                        $sender->sendMessage($this->prefix.C::RED ."You do not have permission to use this command!");
                        return true;
                    }
                    //create arena.
                    $this->createArena($sender, $args);
                    return true;
                default:
                    $sender->sendMessage($this->prefix.C::RED."Unknown Command, /koth help");
                    return true;
            }
        }
        return false;
    }


    //todo API class that holds all methods that SHOULD be used via other plugins :)
    private function createArena(CommandSender $sender, array $args) : void{
        //assume has perms as it got here.

        $usage = "/koth new (arena name - no spaces) (min players) (max players) (gametime in seconds)";
        //rest will be in config, or default for now (rem after coming out of beta)

        if(count($args) !== 5){
            $sender->sendMessage($usage);
            return;
        }
        $minGametime = 60; //1min, todo config.
        $maxGametime = 120; //2min, todo config.
        $forceMax = 20; //todo config.

        $name = $args[1];
        $min = $args[2];
        $max = $args[3];
        $gameTime = $args[4];

        //verify data:
        if($this->plugin->getArenaByName($name) !== null){
            $sender->sendMessage(C::RED."A arena with that name already exists.");
            return;
        }
        if(!is_numeric($min)){
            $sender->sendMessage(C::RED."Min value must be a number.");
            return;
        }
        if(intval($min) < 2){
            $sender->sendMessage(C::RED."minimum value must be above 2.");
            return;
        }
        if(!is_numeric($max)){
            $sender->sendMessage(C::RED."Max value must be a number.");
            return;
        }
        if(intval($max) >= intval($min)){
            $sender->sendMessage(C::RED."Cant play with 1 player, make sure max value is bigger then min.");
            return;
        }
        if(intval($max) > $forceMax){
            $sender->sendMessage(C::RED."The maximum number of players cannot be above ".$forceMax);
            return;
        }

        if(!is_numeric($gameTime)){
            $sender->sendMessage(C::RED."Game time has to be numbers :/");
            return;
        }
        if(intval($gameTime) < $minGametime or intval($gameTime) > $maxGametime){
            $sender->sendMessage(C::RED."Game time has to be between ".$minGametime." and ".$maxGametime);
            return;
        }

        //create arena
        $arena = new Arena($this->plugin, $name, $min, $max, $gameTime, 10 /*todo default config.*/, [[0,0] ,[0,0]], [], "null");
        $this->plugin->newArena($arena);
    }
}