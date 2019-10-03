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
*   Copyright (C) 2019 JaxkDev
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
*   Email   :: JaxkDev@gmail.com
*/

declare(strict_types=1);
namespace Jackthehack21\KOTH\Events;

use Jackthehack21\KOTH\Arena;
use Jackthehack21\KOTH\Main;
use pocketmine\Player;

class ArenaAddPlayerEvent extends KothEvent{

    /** @var Arena */
    private $arena;

    /** @var Player */
    private $player;

    /**
     * ArenaAddPlayerEvent constructor.
     * @param Main $plugin
     * @param Arena $arena
     * @param Player $player
     */
    public function __construct(Main $plugin, Arena $arena, Player $player){
        $this->arena = $arena;
        $this->player = $player;
        parent::__construct($plugin);
    }

    /**
     * @return Arena
     */
    public function getArena(): Arena{
        return $this->arena;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     * Notice: Change this with caution, may result in unwanted behaviour.
     *         You have been warned.
     */
    public function setPlayer(Player $player): void{
        $this->player = $player;
    }
}