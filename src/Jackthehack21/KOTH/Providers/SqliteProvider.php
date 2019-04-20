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
namespace Jackthehack21\KOTH\Providers;

use Jackthehack21\KOTH\{Main,Arena};
use SQLite3;

class SqliteProvider implements BaseProvider{

    /** @var Main $plugin */
    private $plugin;

    /** @var SQLite3 $db */
    public $db;

    private $version = 0;

    private $deleteArenaCode, $createArenaCode, $updateArenaCode, $getAllDataCode;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getName() : string{
        return "Sqlite3";
    }

    public function prepareCode() : void{
        $this->createArenaCode = "INSERT INTO arena (name,min_players,max_players,play_time,hill,spawns,rewards,world,version) VALUES (:name, :min_players, :max_players, :play_time, :hill, :spawns, :rewards, :world, $this->version );";
        $this->deleteArenaCode = "DELETE from arena where name = :name;";
        $this->updateArenaCode = "UPDATE arena SET min_players = :min_players, max_players = :max_players, play_time = :play_time, hill = :hill, spawns = :spawns, rewards = :rewards, world = :world, version = $this->version WHERE name = :name";
        $this->getAllDataCode = "SELECT * FROM arena";
    }

    public function open() : void
    {
        $this->db = new SQLite3($this->plugin->getDataFolder() . "arena.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS arena (name TEXT PRIMARY KEY, min_players INTEGER, max_players INTEGER, play_time INTEGER, hill TEXT, spawns TEXT, rewards TEXT, world TEXT, version INTEGER);");
        $this->plugin->debug("Arena DB opened/created/loaded.");
        $this->prepareCode();
        $this->plugin->debug("Prepared code execution.");
    }

    public function close() : void
    {
        $this->db->close();
        $this->plugin->debug("Arena DB closed/unloaded.");
    }

    public function save(): void{} //not needed, saved on execute.

    public function createArena(Arena $arena) : void{
        $code = $this->db->prepare($this->createArenaCode);
        $code->bindValue(":name", strtolower($arena->getName()));
        $code->bindValue(":min_players", $arena->minPlayers);
        $code->bindValue(":max_players", $arena->maxPlayers);
        $code->bindValue(":play_time", $arena->time);
        $code->bindValue(":hill", "[".implode(",",$arena->hill)."]");
        $code->bindValue(":spawns", "[".implode(",",$arena->spawns)."]");
        $code->bindValue(":rewards", "[".implode(",", $arena->rewards)."]");
        $code->bindValue(":world", $arena->world);
        $code->execute();
    }

    public function updateArena(Arena $arena) : void{
        $code = $this->db->prepare($this->updateArenaCode);
        $code->bindValue(":min_players", $arena->minPlayers);
        $code->bindValue(":max_players", $arena->maxPlayers);
        $code->bindValue(":play_time", $arena->time);
        $code->bindValue(":hill", "[".implode(",",$arena->hill)."]");
        $code->bindValue(":spawns", "[".implode(",",$arena->spawns)."]");
        $code->bindValue(":rewards", "[".implode(",", $arena->rewards)."]");
        $code->bindValue(":world", $arena->world);
        $code->execute();
        //almost the exact same as create...
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
        $data = [];
        $tmp = $result->fetchArray(1);
        while($tmp !== false){
            $data[] = $tmp;
            $tmp = $result->fetchArray(1);
        }
        //todo change string arrays back into arrays here.
        return $data;
    }

    public function setAllData(array $data): void
    {
        $this->plugin->getLogger()->warning("Un finished code.");
        //todo set all data, think about this :/
    }
}