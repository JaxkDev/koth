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

use Jackthehack21\KOTH\Providers\SqliteProvider;
use Jackthehack21\KOTH\Providers\YamlProvider;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

use Jackthehack21\KOTH\Utils as PluginUtils;

/*
 * [ ] Priority: High, Add all messages into messages.yml (different file as there are many customisable messages.)
 * [ ] Priority: Medium, Move most functions to separate file (eg ArenaManager.php) less mess in here to tidy...
 * [ ] Priority: Medium, Move around functions, into more sub files (eg ^) and add all PHPDoc for functions and variables to stop these useless warnings *frown*
 * [ ] Priority: Medium, Add a custom(or JackMD's) Update virion. (most likely create my own to support BetaX and download links etc.)
 * [ ] Priority: Low, Look into different methods of having addons.
 * [ ] Priority: Low, Add the rest of the modern languages to help files. (update existing ones/commit the ones locally)
 */

class Main extends PluginBase implements Listener{

    private static $instance;

    public const ARENA_VER = 2;
    public const CONFIG_VER = 1;

    private $arenas;
    private $CommandHandler;
    private $EventHandler;
    private $configC;
    private $db;

    public $config;
    public $prefix = C::YELLOW."[".C::AQUA."KOTH".C::YELLOW."] ".C::RESET;

    /** @var Utils */
    public $utils;

    private function init() : void{
        $this->CommandHandler = new CommandHandler($this);
        $this->EventHandler = new EventHandler($this);
        $this->utils = new PluginUtils($this);
        //todo addon manager.

        $this->arenas = [];
        $this->loadArenas();

        $this->getServer()->getPluginManager()->registerEvents($this->EventHandler, $this);
    }

    private function initResources() : void{
        $this->saveResource("config.yml");
        $this->configC = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->config = $this->configC->getAll();
        //todo message config.

	    //todo check config+arena versions.
	    if($this->config["version"] !== $this::CONFIG_VER){
	        if(!isset($this->config["provider"])) $this->config["provider"] = "sqlite3";
            if(!isset($this->config["block_commands"])) $this->config["block_commands"] = true;
            if(!isset($this->config["prevent_place"])) $this->config["prevent_place"] = true;
            if(!isset($this->config["prevent_break"])) $this->config["prevent_break"] = true;
            if(!isset($this->config["prevent_gamemode_change"])) $this->config["prevent_gamemode_change"] = true;
            $this->config["version"] = $this::CONFIG_VER;
            $this->saveConfig();
        }

        $languages = array("eng"); //list of all help file languages currently available.
        $language = "eng";
        if (in_array($this->config["language"], $languages) !== false) {
            $language = $this->config["language"];
        }
        $this->saveResource("help_".$language.".txt");
    }

    /**
     * @return bool
     */
    private function startChecks() : bool{
        if($this->config["plugin_enabled"] !== true){
            $this->debug("Plugin disabled, as stated in config.yml");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }

        //Start async task to check for latest release info.

        return true;
    }

    private function loadArenas() : void{
        //TODO Priority: High, change DB to sqlite3 (YAML will be optional in future, for now sqlite3 will be forced as default.) (when multiple providers are implemented make base class so both have same functions to get and save data.)
        switch(strtolower($this->config["provider"])){
            case 'sqlite':
            case 'sql':
            case 'sqlite3':
                $this->db = new SqliteProvider($this);
                break;
            case 'yaml':
                $this->db = new YamlProvider($this);
                break;
            default:
                $this->db = new SqliteProvider($this);
                $this->config["provider"] = "sqlite3";
                $this->saveConfig();
        }
        $this->debug("Provider was set to: ".$this->db->getName());
        $this->db->open();
        $data = $this->db->getAllData();

        if(count($data) === 0){
            $this->debug("0 Arena(s) loaded.");
            return;
        }

        foreach($data as $arenaC){
            $arena = new Arena($this, $arenaC["name"], $arenaC["min_players"], $arenaC["max_players"], $arenaC["play_time"], $arenaC["hill"], $arenaC["spawns"], $arenaC["rewards"], $arenaC["world"]);
            $this->arenas[] = $arena;
        }

        $this->debug(count($this->arenas)." Arena(s) loaded.");
    }

    public function onDisable()
    {
        $this->updateAllArenas();
        $this->saveConfig();
        //close DB
    }

    public function onEnable() : void{
        $this->initResources();
        if($this->startChecks() === false) return;
        $this->init();

    }

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        return $this->CommandHandler->handleCommand($sender, $cmd, $label, $args);
    }

    /**
     * @param Arena $arena
     */
    public function updateArena(Arena $arena) : void{
        $this->db->updateArena($arena);
    }

    /**
     * @param array|null $data
     */
    public function updateAllArenas(array $data = null) : void{
        if($data !== null){
            $this->db->setAllData($data);
            return;
        }
        $save = [];
        foreach($this->arenas as $arena) {
            $save[] = [
                "name" => strtolower($arena->name),
                "min_players" => $arena->minPlayers,
                "max_players" => $arena->maxPlayers,
                "play_time" => $arena->time,
                "hill" => $arena->hill,
                "spawns" => $arena->spawns,
                "rewards" => $arena->rewards,
                "world" => $arena->world
            ];
        }
        $this->db->setAllData($save);
    }

    /**
     * @param array|null $data
     */
    public function saveConfig(array $data = null) : void{
        if($data !== null){
            $this->configC->setAll($data);
            return;
        }
        $this->configC->setAll($this->config);
        $this->configC->save();
    }

    /**
     * @param string $msg
     */
    public function debug(string $msg) : void{
        if($this->config["debug"] === true){
            $this->getServer()->getLogger()->info(C::GRAY."[KOTH | DEBUG] : ".   $msg);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function inGame(string $name) : bool{
        return $this->getArenaByPlayer($name) !== null;
    }

    /**
     * @param Arena $arena
     */
    public function newArena(Arena $arena){
        $this->arenas[] = $arena;
        $this->db->createArena($arena);
    }

    /**
     * @param Arena $arena
     */
    public function removeArena(Arena $arena) : void{
        if (($key = array_search($arena, $this->arenas)) !== false) {
            unset($this->arenas[$key]);
            $this->db->deleteArena(strtolower($arena->getName()));
        }
    }

    /**
     * @param string $name
     */
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

    //TODO, Big.  Move most functions to separate file (eg ArenaManager.php) less mess in here to tidy...

    /**
     * @return Main
     */
    public static function getInstance() : self{
        return self::$instance;
    }

}