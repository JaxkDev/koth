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

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        //possibly prepare all executions ? (todo decide)
    }

    public function open() : void
    {
        $this->db = new SQLite3($this->plugin->getDataFolder() . "arena.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS arena
			(name TEXT PRIMARY KEY, min_players INTEGER, max_players INTEGER, 
			play_time INTEGER, hill TEXT, spawns TEXT, rewards TEXT, world TEXT);");
        $this->plugin->debug("Arena DB opened/loaded.");
    }

    public function close() : void
    {
        $this->db->close();
        $this->plugin->debug("Arena DB closed/unloaded.");
    }

    public function save(): void
    {
        //todo save all arena's
    }

    public function createArena(Arena $arena) : void{
        //todo save Arena using arenaToObject util method.
    }

    public function updateArena(Arena $arena) : void{
        //todo same as above, replacing values.
    }

    public function deleteArena(Arena $arena) : void{
        //todo remove arena.
    }

    public function getAllData(): array
    {
        //todo return all the data in Array.
    }

    public function setAllData(array $data): void
    {
        //todo set all data then save()
    }
}