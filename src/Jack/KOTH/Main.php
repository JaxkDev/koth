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

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat as C;

use Jack\KOTH\{CommandHandler, EventHandler, Arena};;

class Main extends PluginBase implements Listener{

    private $arenas;
    private $CommandHandler;
    private $EventHandler;
    private $configC;
    private $arenaC;
    private $arena;

    public $config;
    public $prefix;

    private function init() : void{
        $this->prefix = C::YELLOW."[".C::AQUA."KOTH".C::YELLOW."] ".C::RESET;
        $this->CommandHandler = new CommandHandler($this);
        $this->EventHandler = new EventHandler($this);
        // --- //
        $this->arenas = [];
        $this->loadArenas();
        // --- //
        $this->getServer()->getPluginManager()->registerEvents($this->EventHandler, $this);
    }

    private function initResources() : void{
        $this->saveResource("config.yml");
        $this->configC = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->config = $this->configC->getAll();
        $this->arenaC = new Config($this->getDataFolder() . "arena.yml", Config::YAML, ["version" => 1, "arena_list" => []]);
		$this->arena = $this->arenaC->getAll();
    }

    private function loadArenas() : void{
        if(count($this->arenas) === 0) return;
        foreach($this->arenas as $arenaC){
            var_dump($arenaC);
            $arena = new Arena($this, $arenaC["name"], $arenaC["min_players"], $arenaC["max_players"], $arenaC["play_time"], $arenaC["start_countdown"], [$arenaC["hill_1"],$arenaC["hill_2"]], $arenaC["spawns"], $arenaC["world"]);
            $this->arenas[] = $arena;
        }
    }

    public function onEnable() : void{
        $this->initResources(); //first to enable Debug.
        $this->init();
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

    /**
	 * NOTE: This only matches by their lowercase name.
	 *
	 * @param string $name
	 *
	 * @return Arena|null
	 */
    public function getArenaByPlayer(string $name){
        foreach($this->arenas as $arena){
            if(in_array($name, $arena->players)){
                return $arena;
            }
        }
        return null;
    }


}