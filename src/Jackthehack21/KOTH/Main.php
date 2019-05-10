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

use Jackthehack21\KOTH\Extensions\ExtensionManager;
use Jackthehack21\KOTH\Providers\BaseProvider;
use Jackthehack21\KOTH\Providers\SqliteProvider;
use Jackthehack21\KOTH\Providers\YamlProvider;
use Jackthehack21\KOTH\Tasks\ExtensionStartTask;
use Jackthehack21\KOTH\Utils as PluginUtils;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

/*
 * [-] Priority: High, Add all messages into messages.yml (different file as there are many customisable messages.)
 * [X] Priority: Medium, Add base events and begin using them.
 * [ ] Priority: Medium, Move most functions to separate file (eg ArenaManager.php) less mess in here to tidy...
 * [ ] Priority: Medium, Move around functions, into more sub files (eg ^) and add all PHPDoc for functions and variables to stop these useless warnings *frown*
 * [ ] Priority: Medium, Add a custom Update class/task.
 * [ ] Priority: Low, Add the rest of the modern languages to help files. (update existing ones/commit the ones locally)
 */

class Main extends PluginBase implements Listener{

    private static $instance;

    public const API = "1.0.0";
    //Note to self, X.Y.Z Bumping X will break all extensions, Y adds things but keep old things (deprecated), Z simply patches things.
    public const ARENA_VER = 2;
    public const CONFIG_VER = 1;

    private $arenas;
    private $CommandHandler;
    private $EventHandler;
    private $configC;
    private $messagesC;
    /** @var BaseProvider */
    private $db;

    public $config;
    public $messages;
    public $prefix = C::YELLOW."[".C::AQUA."KOTH".C::YELLOW."] ".C::RESET;

    /** @var Utils */
    public $utils;

    /**
     * @var ExtensionManager
     */
    public $ExtensionManager;

    private function init() : void{
        $this->ExtensionManager = new ExtensionManager($this);
        $this->CommandHandler = new CommandHandler($this);
        $this->EventHandler = new EventHandler($this);
        $this->utils = new PluginUtils($this);

        $this->arenas = [];
        $this->loadArenas();

        $this->getScheduler()->scheduleTask(new ExtensionStartTask($this));
        $this->getServer()->getPluginManager()->registerEvents($this->EventHandler, $this);
    }

    private function initResources() : void{
        $this->saveResource("config.yml");
        $this->configC = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->config = $this->configC->getAll();

        $this->saveResource("messages.yml");
        $this->messagesC = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->messages = $this->messagesC->getAll();

	    //todo check arena versions.
	    if($this->config["version"] !== $this::CONFIG_VER){
	        if(!isset($this->config["provider"])) $this->config["provider"] = "sqlite3";
            if(!isset($this->config["block_commands"])) $this->config["block_commands"] = true;
            if(!isset($this->config["prevent_place"])) $this->config["prevent_place"] = true;
            if(!isset($this->config["prevent_break"])) $this->config["prevent_break"] = true;
            if(!isset($this->config["prevent_gamemode_change"])) $this->config["prevent_gamemode_change"] = true;
            if(!isset($this->config["keep_inventory"])) $this->config["keep_inventory"] = true;
            if(!isset($this->config["allow_unknown_extensions"])) $this->config["allow_unknown_extensions"] = false;
            $this->config["version"] = $this::CONFIG_VER;
            $this->saveConfig();
        }

        $languages = array("eng"); //todo get files in dir and parse to get available langs.
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
            $this->debug($this->utils->colourise($this->messages["plugin_disabled"]));
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }

        //Start async task to check for latest release info.

        return true;
    }

    private function loadArenas() : void{
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
        $this->debug(str_replace("{NAME}",$this->db->getName(),$this->utils->colourise($this->messages["provider"])));
        $this->db->open();
        $data = $this->db->getAllData();

        foreach($data as $arenaC){
            $arena = new Arena($this, $arenaC["name"], $arenaC["min_players"], $arenaC["max_players"], $arenaC["play_time"], $arenaC["hill"], $arenaC["spawns"], $arenaC["rewards"], $arenaC["world"]);
            $this->arenas[] = $arena;
        }

        $this->debug(str_replace("{AMOUNT}",count($this->arenas),$this->utils->colourise($this->messages["arenas_loaded"])));
    }

    public function onDisable()
    {
        $this->ExtensionManager->disableExtensions();
        $this->updateAllArenas();
        $this->saveConfig();
        $this->db->close();
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
     * @return bool
     */
    public function debug(string $msg) : bool{
        if($this->config["debug"] === true){
            $this->getServer()->getLogger()->info(str_replace("{MSG}",$msg,$this->utils->colourise($this->messages["debug_format"])));
            return true;
        }
        return false;
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
     * @return bool
     */
    public function newArena(Arena $arena) : bool{
        $this->arenas[] = $arena;
        $this->db->createArena($arena);
        return true;
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
	 * @param string $name
	 *
	 * @return Arena|null
	 */
    public function getArenaByPlayer(string $name){
        foreach($this->arenas as $arena){
            if(in_array(strtolower($name), $arena->players)){
                return $arena;
            }
        }
        return null;
    }

    /**
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