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
namespace Jackthehack21\KOTH\Providers;

use Jackthehack21\KOTH\{Main,Arena};
use pocketmine\utils\Config;

class YamlProvider implements BaseProvider{

    /** @var Main $plugin */
    private $plugin;

    /** @var Config $dataConfig */
    public $dataConfig;

    /** @var array $data */
    public $data;

    private $version = 0;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getName() : string{
        return "Yaml";
    }

    public function open() : void
    {
        $this->dataConfig = new Config($this->plugin->getDataFolder() . "arena.yml", Config::YAML, ["version" => $this->version, "arena_list" => []]);
        $this->data = $this->dataConfig->getAll();
        $this->plugin->debug("Arena data file opened/loaded.");
    }

    public function close() : void
    {
        unset($this->data);
        unset($this->dataConfig);
        $this->plugin->debug("Arena data file closed/unloaded.");
    }

    public function save(): void
    {
        $this->dataConfig->setAll($this->data);
        $this->dataConfig->save();
    }

    public function createArena(Arena $arena) : void{
        $this->data[] = [
            "name" => strtolower($arena->getName()),
            "min_players" => $arena->minPlayers,
            "max_players" => $arena->maxPlayers,
            "play_time" => $arena->time,
            "hill" => $arena->hill,
            "spawns" => $arena->spawns,
            "rewards" => $arena->rewards,
            "world" => $arena->world
        ];
        $this->save();
    }

    public function updateArena(Arena $arena) : void{
        $key = 0;
        while(count(array_keys($this->data)) !== $key){
            if($this->data[$key]["name"] == strtolower($arena->getName())){
                $this->data[$key] = [
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
        }
        $this->save();
    }

    public function deleteArena(string $arena) : void{
        $key = 0;
        while(count(array_keys($this->data)) !== $key){
            if($this->data[$key]["name"] == strtolower($arena)){
                unset($this->data[$key]);
            }
        }
        $this->save();
    }

    public function getDataVersion(): int
    {
        return $this->data["version"];
    }

    public function getAllData(): array
    {
        return $this->data["arena_list"]; //so no collisions between both providers.
    }

    public function setAllData(array $data): void
    {
        $this->data = $data;
        $this->save();
    }
}