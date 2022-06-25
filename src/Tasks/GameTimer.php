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

namespace JaxkDev\KOTH\Tasks;

use pocketmine\scheduler\Task;
use JaxkDev\KOTH\Arena;

class GameTimer extends Task{
    private Arena $arena;
    public float $secondsLeft;

    public function __construct(Arena $arena){
        $this->arena = $arena;
        $this->secondsLeft = $arena->getTime();
    }

    public function onRun(): void{
        $this->secondsLeft -= 0.5;
        $inBox = $this->arena->getPlayersInBox();
        if($this->arena->getKing() === null){
            $this->arena->checkNewKing();
        }else{
            if(!in_array($this->arena->getKing(), $inBox)){
                $this->arena->removeKing();
            }
        }

        if($this->secondsLeft <= 0){
            $this->arena->endGame();
        }
    }
}