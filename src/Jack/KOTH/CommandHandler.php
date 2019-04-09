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

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function handleCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        if($cmd->getName() == "koth"){
            if(!isset($args[0])){
                return false;
            }
            if(!$sender instanceof Player){
                $sender->sendMessage(C::RED."Commands can only be run in-game");
                return true;
            }
            switch($args[0]){
                case 'credits':
                    $sender->sendMessage(C::RED."Nobody has a bounty yet.");
                    return true;
                default:
                    $sender->sendMessage(C::RED."Unknown Command, /koth help");
                    return true;
            }
        }
    }
}