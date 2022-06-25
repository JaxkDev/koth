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

namespace JaxkDev\KOTH\Events;

use JaxkDev\KOTH\Main;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;

class ArenaCreateEvent extends KothEvent{

    private Player|ConsoleCommandSender|null $creator;
    private string $name;
    private string $world;
    private int $minPlayers;
    private int $maxPlayers;
    private int $gameTime;

    private array $hill;
    private array $spawns;
    /** @var string[] */
    private array $rewards;

    public function __construct(Main $plugin, $creator, string $name, int $min_players, int $max_players, int $gameTime, array $hill = [], array $spawns = [], array $rewards = [], string $world = "null"){
        $this->creator = $creator;
        $this->name = $name;
        $this->minPlayers = $min_players;
        $this->maxPlayers = $max_players;
        $this->gameTime = $gameTime;
        $this->hill = $hill;
        $this->spawns = $spawns;
        $this->rewards = $rewards;
        $this->world = $world;
        parent::__construct($plugin);
    }

    public function getCreator(): Player|ConsoleCommandSender|null{
        return $this->creator;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getMinPlayers(): int{
        return $this->minPlayers;
    }

    public function setMinPlayers(int $amount): void{
        if($amount < 2){
            throw new \InvalidArgumentException("Min players must be above 2");
        }
        if($amount >= $this->maxPlayers){
            throw new \InvalidArgumentException("Min players must be below max players");
        }
        $this->minPlayers = $amount;
    }

    public function getMaxPlayers(): int{
        return $this->maxPlayers;
    }

    public function setMaxPlayers(int $amount): void{
        if($amount <= $this->minPlayers){
            throw new \InvalidArgumentException("Max players cannot be less than or equal to min players");
        }
        $this->maxPlayers = $amount;
    }

    public function getGameTime(): int{
        return $this->gameTime;
    }

    public function setGameTime(int $amount): void{
        $this->gameTime = $amount;
    }

    public function getHillPositions(): array{
        return $this->hill;
    }

    public function setHillPositions(array $hill): void{
        $this->hill = $hill;
    }

    public function getSpawnPositions(): array{
        return $this->spawns;
    }

    public function setSpawnPositions(array $spawns): void{
        $this->spawns = $spawns;
    }

    public function getRewards(): array{
        return $this->rewards;
    }

    public function setRewards(array $rewards): void{
        $this->rewards = $rewards;
    }

    public function getWorld(): string{
        return $this->world;
    }

    public function setWorld(string $worldName): void{
        $this->world = $worldName;
    }
}