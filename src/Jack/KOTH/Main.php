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
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender,ConsoleCommandSender};;

use pocketmine\utils\TextFormat as C;

use Jack\KOTH\{CommandHandler, EventHandler};;

class Main extends PluginBase implements Listener{

    private function init() : void{
        $this->CommandHandler = new CommandHandler($this);
        $this->EventHandler = new EventHandler($this);
        //TODO Arena's
        $this->getServer()->getPluginManager()->registerEvents($this->EventHandler, $this);
    }

    private function initResources() : void{
        $this->saveResource("config.yml");
        $this->configC = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->config = $this->configC->getAll();
        $this->arenaC = new Config($this->getDataFolder() . "arena.yml", Config::YAML, ["version" => 1, "arena_list" => []]);
		$this->arena = $this->arenaC->getAll();
    }

    public function onEnable() : void{
        $this->init();
        $this->initResources();
        $this->getLogger()->info(C::GREEN."Plugin Enabled.");
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        return $this->CommandHandler->handleCommand($sender, $cmd, $label, $args);
    }

    public function saveArena(array $data = null) : void{
        if($data === null){
            $this->arenaC->setAll($data);
            return;
        }
        $this->arenaC->setAll($this->arena);
    }
    
    public function saveConfig(array $data = null) : void{
        if($data === null){
            $this->configC->setAll($data);
            return;
        }
        $this->configC->setAll($this->config);
    }
}