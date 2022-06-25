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

namespace JaxkDev\KOTH\Providers;

use JaxkDev\KOTH\{Main,Arena};
use SQLite3;

class SqliteProvider implements BaseProvider{

    /** @var Main $plugin */
    private $plugin;

    /** @var SQLite3 $db */
    public $db;

    private $version = Main::ARENA_VER;

    private $createTableCode, $deleteTableCode, $deleteArenaCode, $createArenaCode, $updateArenaCode, $getAllDataCode, $setAllDataCode;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getName() : string{
        return "Sqlite3";
    }

	/** @noinspection SqlNoDataSourceInspection
	 * @noinspection SqlResolve
	 */
	public function prepareCode() : void{
        $this->deleteTableCode = "DROP TABLE arena";
        $this->createTableCode = "CREATE TABLE IF NOT EXISTS arena (name TEXT PRIMARY KEY, min_players INTEGER, max_players INTEGER, play_time INTEGER, hill TEXT, spawns TEXT, rewards TEXT, world TEXT, version INTEGER);";

        $this->createArenaCode = "INSERT INTO arena (name,min_players,max_players,play_time,hill,spawns,rewards,world,version) VALUES (:name, :min_players, :max_players, :play_time, :hill, :spawns, :rewards, :world, $this->version );";
        $this->deleteArenaCode = "DELETE from arena where name = :name;";
        $this->updateArenaCode = "UPDATE arena SET min_players = :min_players, max_players = :max_players, play_time = :play_time, hill = :hill, spawns = :spawns, rewards = :rewards, world = :world, version = $this->version WHERE name = :name";

        $this->getAllDataCode = "SELECT * FROM arena";
        $this->setAllDataCode = "INSERT OR REPLACE INTO arena (name,min_players,max_players,play_time,hill,spawns,rewards,world,version) VALUES (:name, :min_players, :max_players, :play_time, :hill, :spawns, :rewards, :world, $this->version );";
        $this->plugin->debug("Prepared code execution.");
    }

    public function open() : void
    {
        $this->db = new SQLite3($this->plugin->getDataFolder() . "arena.db");
        $this->prepareCode();

        $this->db->exec($this->createTableCode);
        $this->plugin->debug("Arena DB opened/created.");
    }

    public function close() : void
    {
        $this->db->close();
        $this->plugin->debug("Arena DB closed.");
    }

    public function save(): void{}

    public function createArena(Arena $arena) : void{
        $code = $this->db->prepare($this->createArenaCode);
        $code->bindValue(":name", strtolower($arena->getName()));
        $code->bindValue(":min_players", $arena->minPlayers);
        $code->bindValue(":max_players", $arena->maxPlayers);
        $code->bindValue(":play_time", $arena->time);
        $code->bindValue(":hill", json_encode($arena->hill));
        $code->bindValue(":spawns", json_encode($arena->spawns));
        $code->bindValue(":rewards", json_encode($arena->rewards));
        $code->bindValue(":world", $arena->world);
        $code->execute();
    }

    public function updateArena(Arena $arena) : void{
        $code = $this->db->prepare($this->updateArenaCode);
        $code->bindValue(":min_players", $arena->minPlayers);
        $code->bindValue(":max_players", $arena->maxPlayers);
        $code->bindValue(":play_time", $arena->time);
        $code->bindValue(":hill", json_encode($arena->hill));
        $code->bindValue(":spawns", json_encode($arena->spawns));
        $code->bindValue(":rewards", json_encode($arena->rewards));
        $code->bindValue(":world", $arena->world);
        $code->execute();
    }

    public function deleteArena(string $arena) : void{
        $code = $this->db->prepare($this->deleteArenaCode);
        $code->bindValue(":name", strtolower($arena));
        $code->execute();
    }

    public function getDataVersion(): int
    {
        //returns version or -1 if not found.
        $data = $this->getAllData();
        if(count($data) === 0) return -1;
        return $data[0]["version"];
    }

    public function getAllData(): array
    {
        $result = $this->db->query($this->getAllDataCode);
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

    public function setAllData(array $data): void
    {
        foreach($data as $arena){
            $code = $this->db->prepare($this->setAllDataCode);
            $code->bindValue(":name", strtolower($arena["name"]));
            $code->bindValue(":min_players", $arena["min_players"]);
            $code->bindValue(":max_players", $arena["max_players"]);
            $code->bindValue(":play_time", $arena["play_time"]);
            $code->bindValue(":hill", json_encode($arena["hill"]));
            $code->bindValue(":spawns", json_encode($arena["spawns"]));
            $code->bindValue(":rewards", json_encode($arena["rewards"]));
            $code->bindValue(":world", $arena["world"]);
            $code->execute();
        }
    }

    public function remAllData(): void
    {
        $this->db->exec($this->deleteTableCode);
    }
}