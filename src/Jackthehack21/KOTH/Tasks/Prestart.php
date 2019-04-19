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
namespace Jackthehack21\KOTH\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as C;

use Jackthehack21\KOTH\Main;
use Jackthehack21\KOTH\Arena;

class Prestart extends Task{

    private $plugin;
    private $arena;
    private $countDown;

    /**
     * Prestart constructor.
     * @param Main $plugin
     * @param Arena $arena
     * @param int $count
     */
    public function __construct(Main $plugin, Arena $arena, int $count){
        $this->plugin = $plugin;
        $this->arena = $arena;
        $this->countDown = $count;
    }

    /**
     * @param int $tick
     */
    public function onRun(int $tick){
        if($this->countDown === 0){
            $this->arena->startGame();
            return;
        }
        if($this->countDown <= 5){
            $this->arena->broadcastMessage($this->plugin->prefix.C::RED."[COUNTDOWN] : ".C::GREEN.$this->countDown); //todo config.
        } else {
            if($this->countDown%5 === 0){
                $this->arena->broadcastMessage($this->plugin->prefix.C::RED."[COUNTDOWN] : ".C::GREEN.$this->countDown);
            }
        }
        $this->countDown--;
    }
}