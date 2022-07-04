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

use JaxkDev\KOTH\Arena;
use JaxkDev\KOTH\Main;

class ArenaEndEvent extends KothEvent{
    private Arena $arena;
    private int $secondsLeft;

    public function __construct(Main $plugin, Arena $arena){
        $this->arena = $arena;
        $this->secondsLeft = $arena->getTime();
        parent::__construct($plugin);
    }

    public function getArena(): Arena{
        return $this->arena;
    }

    public function getSecondsLeft(): int{
        return $this->secondsLeft;
    }

    /**
     * @param int $seconds
     * Notice: Cancels the event if seconds left is changed to a positive int (basically extra time.)
     */
    public function setSecondsLeft(int $seconds): void{
        $this->secondsLeft = $seconds;
        if($seconds > 0){
            $this->cancel(); //Just in-case developers don't...
        }
    }
}