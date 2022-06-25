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
use JaxkDev\KOTH\Main;
use JaxkDev\KOTH\Arena;

class StartCountdown extends Task{

    private Main $plugin;
    private Arena $arena;

    private int $countDown;
    private bool $serverBcast;

    public function __construct(Main $plugin, Arena $arena, int $count){
        $this->plugin = $plugin;
        $this->arena = $arena;
        $this->countDown = $count;
        $this->serverBcast = ($plugin->getConfig()->get("countdown_bcast_serverwide", true) === true);
    }

    public function onRun(): void{
        if($this->countDown === 0){
            $this->arena->startGame();
            return;
        }
        if($this->plugin->getConfig()->get("countdown_bcast", true) === true){
            $msg = str_replace(["{COUNT}","{ARENA}"],[$this->countDown, $this->arena->getName()], $this->plugin->utils->colourise((string)$this->plugin->getMessages()->getNested("broadcasts.countdown", "{PREFIX}{GOLD}[{AQUA}{ARENA} | {GOLD}COUNTDOWN] {RED}: {GREEN}{COUNT}")));
            if($this->countDown <= 5){
                if(!$this->serverBcast){
                    $this->arena->broadcastMessage($msg);
                } else{
                    $this->plugin->getServer()->broadcastMessage($msg);
                }
            }else{
                if(($this->countDown % (int)$this->plugin->getConfig()->get("countdown_bcast_interval", 5)) === 0){
                    if(!$this->serverBcast){
                        $this->arena->broadcastMessage($msg);
                    }else{
                        $this->plugin->getServer()->broadcastMessage($msg);
                    }
                }
            }
        }
        $this->countDown--;
    }
}