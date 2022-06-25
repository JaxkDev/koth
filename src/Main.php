<?php
/*
 *   KOTH, A pocketmine-MP Mini-game
 *
 *   Copyright (C) 2019-present JaxkDev
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
 *   Discord :: JaxkDev#2698
 *   Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\KOTH;

use JaxkDev\KOTH\Providers\BaseProvider;
use JaxkDev\KOTH\Providers\SqliteProvider;
use JaxkDev\KOTH\Tasks\CheckUpdate;
use JaxkDev\KOTH\Utils as PluginUtils;
use Phar;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

class Main extends PluginBase implements Listener{
    public const ARENA_VER = 2;
    public const CONFIG_VER = 2;
    public const MESSAGE_VER = 0;

    /** @var Arena[] */
    private array $arenas = [];
    private CommandHandler $commandHandler;
    private EventHandler $eventHandler;
    private Config $configC;
    private Config $messagesC;
    private BaseProvider $db;
    public Utils $utils;

    public array $config;
    /** @var string[] */
    public array $messages;

    public const PREFIX = C::YELLOW . "[" . C::AQUA . "KOTH" . C::YELLOW . "] " . C::RESET;

    private function loadArenas(): void{
        switch (strtolower($this->config["provider"])){
            case 'sqlite':
            case 'sqlite3':
                $this->db = new SqliteProvider($this);
                break;
            default:
                $this->getLogger()->error("Invalid provider type: '" . $this->config["provider"] . "', Reverted to default provider 'sqlite3'");
                $this->db = new SqliteProvider($this);
                $this->config["provider"] = "sqlite3";
                $this->saveConfig();
        }
        $this->getLogger()->debug(str_replace("{NAME}", $this->db->getName(), $this->utils->colourise($this->messages["general"]["provider"])));
        $this->db->open();
        $data = $this->db->getAllData();

        foreach ($data as $arenaC) {
            $arena = new Arena($this, $arenaC["name"], $arenaC["min_players"], $arenaC["max_players"], $arenaC["play_time"], $arenaC["hill"], $arenaC["spawns"], $arenaC["rewards"], $arenaC["world"]);
            $this->arenas[] = $arena;
        }

        $this->getLogger()->debug(str_replace("{AMOUNT}", (string)count($this->arenas), $this->utils->colourise($this->messages["arenas"]["loaded"])));
    }

    public function onDisable(): void{
        $this->updateAllArenas();
        $this->saveConfig();
        $this->db->close();
    }

    public function onLoad(): void{
        if(Phar::running() === ""){
            throw new \Exception("Cannot be run from source.");
        }

        $this->saveResource("config.yml");
        $this->configC = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->config = $this->configC->getAll();

        $this->saveResource("messages.yml");
        $this->messagesC = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->messages = $this->messagesC->getAll();

        if ($this->messages["version"] !== $this::MESSAGE_VER) {
            // TODO UPDATE
            $this->getLogger()->debug("Message file is outdated, attempting to update.");
        }
        if ($this->config["version"] !== $this::CONFIG_VER) {
            if (isset($this->config["start_countdown"])){
                $this->config["countdown"] = $this->config["start_countdown"];
                unset($this->config["start_countdown"]);
            } else {
                if(!isset($this->config["countdown"])){
                    $this->config["countdown"] = 30;
                }
            }
            if (!isset($this->config["countdown_bcast"])) $this->config["countdown_bcast"] = true;
            if (!isset($this->config["countdown_bcast_interval"])) $this->config["countdown_bcast_interval"] = 5;
            if (!isset($this->config["countdown_bcast_serverwide"])) $this->config["countdown_bcast_serverwide"] = false;
            if (!isset($this->config["start_bcast_serverwide"])) $this->config["start_bcast_serverwide"] = false;
            if (!isset($this->config["end_bcast_serverwide"])) $this->config["end_bcast_serverwide"] = false;
            if (isset($this->config["language"])) unset($this->config["language"]);
            if (!isset($this->config["provider"])) $this->config["provider"] = "sqlite3";
            if (!isset($this->config["block_messages"])) $this->config["block_messages"] = true;
            if (!isset($this->config["block_commands"])) $this->config["block_commands"] = true;
            if (!isset($this->config["prevent_place"])) $this->config["prevent_place"] = true;
            if (!isset($this->config["prevent_break"])) $this->config["prevent_break"] = true;
            if (!isset($this->config["prevent_gamemode_change"])) $this->config["prevent_gamemode_change"] = true;
            if (!isset($this->config["keep_inventory"])) $this->config["keep_inventory"] = true;
            if (isset($this->config["show_updates"])) unset($this->config["show_updates"]);
            if (!isset($this->config["check_updates"])) $this->config["check_updates"] = true;
            if (isset($this->config["download_updates"])) unset($this->config["download_updates"]);
            $this->config["version"] = $this::CONFIG_VER;
            $this->saveConfig();
        }

        foreach(array("eng","spa","fra") as $language){
            @unlink($this->getDataFolder()."help_".$language.".txt");
            $this->saveResource("help_" . $language . ".txt");
        }
    }

    public function onEnable(): void{
        $this->CommandHandler = new CommandHandler($this);
        $this->EventHandler = new EventHandler($this);
        $this->utils = new PluginUtils($this);
        $this->arenas = [];
        $this->loadArenas();
        $this->getServer()->getPluginManager()->registerEvents($this->EventHandler, $this);

        if ($this->config["check_updates"]) {
            $this->debug("Starting update check task...");
            $this->getServer()->getAsyncPool()->submitTask(new CheckUpdate($this->getDescription()->getVersion()));
        }
    }

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        $this->CommandHandler->handleCommand($sender, $cmd, $label, $args);
        return true;
    }

    /**
     * @param Arena $arena
     */
    public function updateArena(Arena $arena): void
    {
        $this->db->updateArena($arena);
    }

    /**
     * @param array|null $data
     */
    public function updateAllArenas(array $data = null): void
    {
        if ($data !== null) {
            $this->db->setAllData($data);
            return;
        }
        $save = [];
        foreach ($this->arenas as $arena) {
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
    public function saveConfig(array $data = null): void
    {
        if ($data !== null) {
            $this->configC->setAll($data);
            return;
        }
        $this->configC->setAll($this->config);
        $this->configC->save();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function inGame(string $name): bool
    {
        return $this->getArenaByPlayer($name) !== null;
    }

    /**
     * @param Arena $arena
     */
    public function newArena(Arena $arena): void
    {
        $this->arenas[] = $arena;
        $this->db->createArena($arena);
    }

    /**
     * @param Arena $arena
     */
    public function removeArena(Arena $arena): void
    {
        if (($key = array_search($arena, $this->arenas)) !== false) {
            unset($this->arenas[$key]);
            $this->db->deleteArena(strtolower($arena->getName()));
        }
    }

    /**
     * @param string $name
     */
    public function removeArenaByName(string $name): void
    {
        $this->removeArena($this->getArenaByName($name));
    }

    /**
     * @return Arena[]
     */
    public function getAllArenas(): array
    {
        return $this->arenas;
    }

    /**
     * @param string $name
     *
     * @return Arena|null
     */
    public function getArenaByPlayer(string $name)
    {
        foreach ($this->arenas as $arena) {
            if (in_array(strtolower($name), $arena->getPlayers())) {
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
    public function getArenaByName(string $name)
    {
        foreach ($this->arenas as $arena) {
            if (strtolower($arena->getName()) == strtolower($name)) {
                return $arena;
            }
        }
        return null;
    }

    /**
     * @return Main
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }
}