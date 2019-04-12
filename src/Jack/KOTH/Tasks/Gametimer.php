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
namespace Jack\KOTH\Tasks;

use pocketmine\scheduler\Task;

use Jack\KOTH\Main;
use Jack\KOTH\Arena;

class Gametimer extends Task{

    private $plugin;
    private $arena;

    public $secondsPlayed = 0;

    public function __construct(Main $plugin, Arena $arena){
        $this->plugin = $plugin;
        $this->arena = $arena;
    }

    public function onRun(int $tick){
        $this->secondsPlayed = floor($tick/20);
        $inBox = $this->arena->playersInBox();
        if($this->arena->king === null){
            $this->arena->checkNewKing();
            return;
        }
        if(in_array($this->arena->king, $inBox) === false){
            $this->arena->removeKing();
            return;
        }

        if($this->secondsPlayed >= $this->arena->time){
            $this->arena->endGame();
            //task then cancelled.
        }
    }
}