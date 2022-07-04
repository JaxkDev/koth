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

use JaxkDev\KOTH\Main;
use JaxkDev\KOTH\Arena;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;

class SqlProvider implements BaseProvider{

    private Main $plugin;
    private DataConnector $db;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function getName(): string{
        return "SQL";
    }

    /**
     * @throws SqlError
     */
    public function open(callable $callable): void{
        $this->db = libasynql::create($this->plugin, $this->plugin->getConfig()->get("database"), [
            "sqlite" => "provider/sqlite.sql",
            //"mysql" => "provider/mysql.sql"
        ]);

        $this->db->executeGeneric("arena.init", [], function() use($callable): void{
            $this->plugin->getLogger()->debug("[DB] Tables checked/created.");
            $callable();
        }, function(SqlError $error): void{
            $this->plugin->getLogger()->error("[DB] Failed to create/check tables - '".$error->getErrorMessage()."'.");
            throw $error;
        });
    }

    public function close(): void{
        if(isset($this->db)){
            $this->db->close();
        }
        $this->plugin->getLogger()->debug("[DB] Connector closed.");
    }

    /**
     * @throws SqlError
     */
    public function createArena(Arena $arena): void{
        $this->db->executeInsert("arena.create", [
            "name" => $arena->getName(),
            "min" => $arena->getMinPlayers(),
            "max" => $arena->getMaxPlayers(),
            "hill" => json_encode($arena->getHill()),
            "spawns" => json_encode($arena->getSpawns()),
            "rewards" => json_encode($arena->getRewards()),
            "world" => $arena->getWorld()
        ], function(int $insertId, int $affectedRows) use($arena): void{
            if($affectedRows === 1){
                $this->plugin->getLogger()->debug("[DB] Arena '".$arena->getName()."' created.");
            }else{
                $this->plugin->getLogger()->error("[DB] Failed to create arena '".$arena->getName()."' - no affected rows.");
            }
        }, function(SqlError $error) use($arena): void{
            $this->plugin->getLogger()->error("[DB] Arena '".$arena->getName()."' failed to update - '".$error->getErrorMessage()."'.");
            throw $error;
        });
    }

    /**
     * @throws SqlError
     */
    public function updateArena(Arena $arena): void{
        $this->db->executeChange("arena.update", [
            "name" => $arena->getName(),
            "min" => $arena->getMinPlayers(),
            "max" => $arena->getMaxPlayers(),
            "hill" => json_encode($arena->getHill()),
            "spawns" => json_encode($arena->getSpawns()),
            "rewards" => json_encode($arena->getRewards()),
            "world" => $arena->getWorld()
        ], function(int $affectedRows) use($arena): void{
            if($affectedRows === 1){
                $this->plugin->getLogger()->debug("[DB] Arena '".$arena->getName()."' updated.");
            }else{
                $this->plugin->getLogger()->warning("[DB] Arena '".$arena->getName()."' failed to update - no affected rows.");
            }
        }, function(SqlError $error) use($arena): void{
            $this->plugin->getLogger()->error("[DB] Arena '".$arena->getName()."' failed to update - '".$error->getErrorMessage()."'.");
            throw $error;
        });
    }

    /**
     * @throws SqlError
     */
    public function deleteArena(string $arena): void{
        $this->db->executeChange("arena.delete", [
            "name" => $arena
        ], function(int $affectedRows) use($arena): void{
            if($affectedRows === 1){
                $this->plugin->getLogger()->debug("[DB] Arena '".$arena."' deleted.");
            }else{
                $this->plugin->getLogger()->warning("[DB] Arena '".$arena."' failed to delete - no affected rows.");
            }
        }, function(SqlError $error) use($arena): void{
            $this->plugin->getLogger()->error("[DB] Arena '".$arena."' failed to delete - '".$error->getErrorMessage()."'.");
            throw $error;
        });
    }

    /**
     * @throws SqlError
     */
    public function loadAllArenas(callable $callback): void{
        $this->db->executeSelect("arena.all", [], function(array $rows) use($callback): void{
            $arenas = [];
            foreach($rows as $row){
                $arenas[] = new Arena($this->plugin, $row["name"], $row["min_players"], $row["max_players"], $row["time"], json_decode($row["hill"], true), json_decode($row["spawns"], true), json_decode($row["rewards"], true), $row["world"]);
            }
            $callback($arenas);
        }, function(SqlError $error): void{
            $this->plugin->getLogger()->error("[DB] Failed to load all arenas - '".$error->getErrorMessage()."'.");
            throw $error;
        });
    }
}