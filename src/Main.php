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

use Exception;
use JaxkDev\KOTH\Providers\BaseProvider;
use JaxkDev\KOTH\Providers\SqliteProvider;
use JaxkDev\KOTH\Tasks\CheckUpdate;
use JaxkDev\KOTH\Utils as PluginUtils;
use Phar;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
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
    private BaseProvider $db;
    private Config $config;
    private Config $messages;

    public Utils $utils;

    public const PREFIX = C::YELLOW . "[" . C::AQUA . "KOTH" . C::YELLOW . "] " . C::RESET;

    private function loadArenas(): void{
        switch(strtolower($this->config->get("provider", "unknown"))){
            case 'sqlite':
            case 'sqlite3':
                $this->db = new SqliteProvider($this);
                break;
            default:
                $this->getLogger()->error("Invalid provider type: '" . $this->config->get("provider", "unknown") . "', Reverted to default provider 'sqlite3'");
                $this->db = new SqliteProvider($this);
                $this->config->set("provider", "sqlite3");
                $this->saveConfig();
        }
        $this->getLogger()->debug(str_replace("{NAME}", $this->db->getName(), $this->utils->colourise((string)$this->messages->getNested("general.provider", "Provider was set to: {NAME}"))));
        $this->db->open();
        $this->arenas = $this->db->loadAllArenas();
        $this->getLogger()->debug(str_replace("{AMOUNT}", (string)count($this->arenas), $this->utils->colourise((string)$this->messages->getNested("arenas.loaded", "{AMOUNT} Arena(s) loaded."))));
    }

    public function onDisable(): void{
        //$this->saveConfig();
        $this->db->close();
    }

    public function onLoad(): void{
        if(Phar::running() === ""){
            throw new Exception("Cannot be run from source.");
        }

        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $this->saveResource("messages.yml");
        $this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);

        if($this->messages->get("version", 1) !== $this::MESSAGE_VER){
            // TODO UPDATE
            $this->getLogger()->debug("Message file is outdated, attempting to update.");
        }
        if($this->config->get("version", 1) !== $this::CONFIG_VER){
            if($this->config->exists("start_countdown")){
                $this->config->set("countdown", $this->config->get("start_countdown"));
                $this->config->remove("start_countdown");
            }else{
                if(!$this->config->exists("countdown")){
                    $this->config->set("countdown", 30);
                }
            }
            if(!$this->config->exists("countdown_bcast")) $this->config->set("countdown_bcast", true);
            if(!$this->config->exists("countdown_bcast_interval")) $this->config->set("countdown_bcast_interval", 5);
            if(!$this->config->exists("countdown_bcast_serverwide")) $this->config->set("countdown_bcast_serverwide", false);
            if(!$this->config->exists("start_bcast_serverwide")) $this->config->set("start_bcast_serverwide", false);
            if(!$this->config->exists("end_bcast_serverwide")) $this->config->set("end_bcast_serverwide", false);
            if(!$this->config->exists("provider")) $this->config->set("provider", "sqlite3");
            if(!$this->config->exists("block_messages")) $this->config->set("block_messages", true);
            if(!$this->config->exists("block_commands")) $this->config->set("block_commands", true);
            if(!$this->config->exists("prevent_place")) $this->config->set("prevent_place", true);
            if(!$this->config->exists("prevent_break")) $this->config->set("prevent_break", true);
            if(!$this->config->exists("prevent_gamemode_change")) $this->config->set("prevent_gamemode_change", true);
            if(!$this->config->exists("keep_inventory")) $this->config->set("keep_inventory", true);
            if(!$this->config->exists("check_updates")) $this->config->set("check_updates", true);
            $this->config->remove("show_updates");
            $this->config->remove("language");
            $this->config->remove("download_updates");
            $this->config->set("version", self::CONFIG_VER);
            $this->saveConfig();
        }

        foreach(["eng","spa","fra"] as $language){
            @unlink($this->getDataFolder()."help_".$language.".txt");
            $this->saveResource("help_" . $language . ".txt");
        }
    }

    public function onEnable(): void{
        $this->commandHandler = new CommandHandler($this);
        $this->utils = new PluginUtils($this);
        $this->arenas = [];
        $this->loadArenas();
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);

        if($this->config->get("check_updates", true) === true){
            $this->getLogger()->debug("Starting update check task...");
            $this->getServer()->getAsyncPool()->submitTask(new CheckUpdate($this->getDescription()->getVersion()));
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if($sender instanceof ConsoleCommandSender or $sender instanceof Player){
            $this->commandHandler->handleCommand($sender, $args);
        }else{
            //Lovely messages for people like RCON etc.
            $sender->sendMessage("Who are you... You're not a player or a console user!");
        }
        return true;
    }

    public function updateArena(Arena $arena): void{
        $this->db->updateArena($arena);
    }

    public function createArena(Arena $arena): void{
        $this->arenas[] = $arena;
        $this->db->createArena($arena);
    }

    public function deleteArena(Arena $arena): void{
        if(($key = array_search($arena, $this->arenas)) !== false){
            unset($this->arenas[$key]);
            $this->db->deleteArena(strtolower($arena->getName()));
        }
    }

    public function deleteArenaByName(string $name): void{
        $arena = $this->getArenaByName($name);
        if($arena !== null){
            $this->deleteArena($arena);
        }
    }

    /**
     * @return Arena[]
     */
    public function getAllArenas(): array{
        return $this->arenas;
    }

    public function getArenaByPlayer(string $name): ?Arena{
        foreach($this->arenas as $arena){
            if(in_array(strtolower($name), $arena->getPlayers())){
                return $arena;
            }
        }
        return null;
    }

    public function getArenaByName(string $name): ?Arena{
        foreach($this->arenas as $arena){
            if(strtolower($arena->getName()) === strtolower($name)){
                return $arena;
            }
        }
        return null;
    }

    public function getMessages(): Config{
        return $this->messages;
    }
}