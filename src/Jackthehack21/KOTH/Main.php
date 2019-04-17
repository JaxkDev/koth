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

/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);
namespace Jackthehack21\KOTH;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

#use JackMD\UpdateNotifier\UpdateNotifier;

class Main extends PluginBase implements Listener{

    private static $instance;

    private $arenas;
    private $CommandHandler;
    private $EventHandler;
    private $configC;
    private $arenaC;
    private $arenaSaves;

    public $config;
    public $prefix = C::YELLOW."[".C::AQUA."KOTH".C::YELLOW."] ".C::RESET;

    private function init() : void{
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
	    $this->arenaSaves = $this->arenaC->getAll();

	    //todo check config+arena versions.

        $languages = array("eng"); //list of all help file languages currently available.
        $language = "eng";
        if (in_array($this->config["language"], $languages) !== false) {
            $language = $this->config["language"];
        }
        $this->saveResource("help_".$language.".txt");
    }

    private function startChecks() : bool{
        if($this->config["plugin_enabled"] !== true){
            $this->debug("Plugin disabled, as stated in config.yml");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }

        /*if($this->config["check_updates"] === true) {
            Todo enable UpdateNotifier virion by JackMD. (when out of beta)
            UpdateNotifier::checkUpdate($this, $this->getDescription()->getName(), $this->getDescription()->getVersion());
        }*/
        return true;
    }

    private function loadArenas() : void{
        if(count($this->arenaSaves["arena_list"]) === 0){
            $this->debug("0 Arena(s) loaded.");
            return;
        }
        foreach($this->arenaSaves["arena_list"] as $arenaC){
            $arena = new Arena($this, $arenaC["name"], $arenaC["min_players"], $arenaC["max_players"], $arenaC["play_time"], $arenaC["hill"], $arenaC["spawns"], $arenaC["world"]);
            $this->arenas[] = $arena;
        }
        $this->debug(count($this->arenas)." Arena(s) loaded.");
    }

    public function onDisable()
    {
        $this->saveArena();
        $this->saveConfig();
    }

    public function onEnable() : void{
        $this->initResources();
        if($this->startChecks() === false) return;
        $this->init();

    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->CommandHandler->handleCommand($sender, $cmd, $label, $args);
    }

    public function saveArena(array $data = null) : void{
        if($data !== null){
            $this->arenaC->set("arena_list",$data);
            return;
        }
        $save = [];
        foreach($this->arenas as $arena) {
            $save[] = [
                "name" => $arena->name,
                "min_players" => $arena->minPlayers,
                "max_players" => $arena->maxPlayers,
                "play_time" => $arena->time,
                "hill" => $arena->hill,
                "spawns" => $arena->spawns,
                "world" => $arena->world
            ];
        }
        //$this->getLogger()->debug("Saving Arena data.");
        $this->arenaC->set("arena_list", $save);
        $this->arenaC->save();  //<-- took a hour to figure out why it wasn't saving :/
    }

    public function saveConfig(array $data = null) : void{
        if($data !== null){
            $this->configC->setAll($data);
            return;
        }
        $this->configC->setAll($this->config);
        $this->configC->save(); //<-- took a hour to figure out why it wasn't saving :/
    }

    /**
     * @param string $msg
     */
    public function debug(string $msg) : void{
        if($this->config["debug"] === true){
            $this->getLogger()->info(C::GRAY."[DEBUG] : ".   $msg);
        }
    }

    public function inGame(string $name) : bool{
        return $this->getArenaByPlayer($name) !== null;
    }

    public function newArena(Arena $arena){
        $this->arenas[] = $arena;
        $this->saveArena();
    }

    public function removeArena(Arena $arena) : void{
        if (($key = array_search($arena, $this->arenas)) !== false) {
            unset($this->arenas[$key]);
            $this->saveArena();
        }
    }

    public function removeArenaByName(string $name) : void{
        $this->removeArena($this->getArenaByName($name));
    }

    /**
     * @return Arena[]
     */
    public function getAllArenas() : array{
        return $this->arenas;
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

    /**
     * NOTE: This only matches by their lowercase name.
     *
     * @param string $name
     *
     * @return Arena|null
     */
    public function getArenaByName(string $name){
        foreach($this->arenas as $arena){
            if(strtolower($arena->getName()) == strtolower($name)){
                return $arena;
            }
        }
        return null;
    }

    /**
     * @return Main
     */
    public static function getInstance() : self{
        return self::$instance;
    }

}