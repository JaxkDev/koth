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
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\utils\TextFormat as C;

use Jack\KOTH\Main;

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
                default:
                    $sender->sendMessage($this->prefix.C::RED."Unknown Command, /koth help");
                    return true;
            }
        }
        return false;
    }
}