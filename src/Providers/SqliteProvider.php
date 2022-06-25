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
/**
 * @noinspection SqlDialectInspection
 * @noinspection SqlNoDataSourceInspection
 * @noinspection DuplicatedCode
 */

namespace JaxkDev\KOTH\Providers;

use Exception;
use JaxkDev\KOTH\{Main,Arena};
use SQLite3;

class SqliteProvider implements BaseProvider{

    private Main $plugin;
    private SQLite3 $db;
    private int $version = Main::ARENA_VER;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function getName(): string{
        return "Sqlite3";
    }

    /**
     * @throws Exception
     */
    public function open(): void{
        $this->db = new SQLite3($this->plugin->getDataFolder() . "arena.db");
        if($this->db->exec("CREATE TABLE IF NOT EXISTS arena (name TEXT PRIMARY KEY, min_players INTEGER, max_players INTEGER, play_time INTEGER, hill TEXT, spawns TEXT, rewards TEXT, world TEXT, version INTEGER);") === false){
            throw new Exception("Failed to create arena table");
        }
        $this->plugin->getLogger()->debug("Database opened.");
    }

    public function close(): void{
        $this->db->close();
        $this->plugin->getLogger()->debug("Arena DB closed.");
    }

    public function save(): void{}

    /**
     * @throws Exception
     */
    public function createArena(Arena $arena): void{
        $code = $this->db->prepare("INSERT INTO arena (name,min_players,max_players,play_time,hill,spawns,rewards,world,version) VALUES (:name, :min_players, :max_players, :play_time, :hill, :spawns, :rewards, :world, :version);");
        if($code === false){
            throw new Exception("Failed to prepare SQLite3 statement to create arena.");
        }
        $code->bindValue(":name", strtolower($arena->getName()));
        $code->bindValue(":min_players", $arena->minPlayers);
        $code->bindValue(":max_players", $arena->maxPlayers);
        $code->bindValue(":play_time", $arena->time);
        $code->bindValue(":hill", json_encode($arena->hill));
        $code->bindValue(":spawns", json_encode($arena->spawns));
        $code->bindValue(":rewards", json_encode($arena->rewards));
        $code->bindValue(":world", $arena->world);
        $code->bindValue(":version", $this->version);
        if($code->execute() === false){
            throw new Exception("Failed to execute SQLite3 statement to create arena.");
        }
    }

    /**
     * @throws Exception
     */
    public function updateArena(Arena $arena): void{
        $code = $this->db->prepare("UPDATE arena SET min_players = :min_players, max_players = :max_players, play_time = :play_time, hill = :hill, spawns = :spawns, rewards = :rewards, world = :world, version = :version WHERE name = :name");
        if($code === false){
            throw new Exception("Failed to prepare SQLite3 statement to update arena.");
        }
        $code->bindValue(":min_players", $arena->minPlayers);
        $code->bindValue(":max_players", $arena->maxPlayers);
        $code->bindValue(":play_time", $arena->time);
        $code->bindValue(":hill", json_encode($arena->hill));
        $code->bindValue(":spawns", json_encode($arena->spawns));
        $code->bindValue(":rewards", json_encode($arena->rewards));
        $code->bindValue(":world", $arena->world);
        $code->bindValue(":version", $this->version);
        if($code->execute() === false){
            throw new Exception("Failed to execute SQLite3 statement to update arena.");
        }
    }

    /**
     * @throws Exception
     */
    public function deleteArena(string $arena): void{
        $code = $this->db->prepare("DELETE from arena where name = :name;");
        if($code === false){
            throw new Exception("Failed to prepare SQLite3 statement to delete arena.");
        }
        $code->bindValue(":name", strtolower($arena));
        if($code->execute() === false){
            throw new Exception("Failed to execute SQLite3 statement to delete arena.");
        }
    }

    /**
     * @throws Exception
     */
    public function getDataVersion(): ?int{
        try{
            $data = $this->getAllData();
        }catch(Exception){
            return null;
        }
        if(count($data) === 0) return null;
        return $data[0]["version"];
    }

    /**
     * @throws Exception
     */
    public function getAllData(): array{
        $result = $this->db->query("SELECT * FROM arena");
        if($result === false){
            throw new Exception("Failed to execute SQLite3 statement to get all data.");
        }
        $tmpData = [];
        $countTmp = $result->fetchArray(1);
        while($countTmp !== false){
            $tmpData[] = $countTmp;
            /** @var false|array $countTmp */
            $countTmp = $result->fetchArray(1);
        }
        $data = [];
        foreach($tmpData as $tmp){
            $tmp["hill"] =  json_decode($tmp["hill"], true);
            $tmp["spawns"] =  json_decode($tmp["spawns"], true);
            $tmp["rewards"] =  json_decode($tmp["rewards"], true);
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    public function setAllData(array $data): void{
        foreach($data as $arena){
            $code = $this->db->prepare("INSERT OR REPLACE INTO arena (name,min_players,max_players,play_time,hill,spawns,rewards,world,version) VALUES (:name, :min_players, :max_players, :play_time, :hill, :spawns, :rewards, :world, :version);");
            if($code === false){
                throw new Exception("Failed to prepare SQLite3 statement to set all data.");
            }
            $code->bindValue(":name", strtolower($arena["name"]));
            $code->bindValue(":min_players", $arena["min_players"]);
            $code->bindValue(":max_players", $arena["max_players"]);
            $code->bindValue(":play_time", $arena["play_time"]);
            $code->bindValue(":hill", json_encode($arena["hill"]));
            $code->bindValue(":spawns", json_encode($arena["spawns"]));
            $code->bindValue(":rewards", json_encode($arena["rewards"]));
            $code->bindValue(":world", $arena["world"]);
            $code->bindValue(":version", $this->version);
            if($code->execute() === false){
                throw new Exception("Failed to execute SQLite3 statement to set all data.");
            }
        }
    }
}