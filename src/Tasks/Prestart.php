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

class Prestart extends Task{

    /** @var Main */
    private $plugin;

    /** @var Arena */
    private $arena;

    /** @var int */
    private $countDown;
    private $serverBcast;

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
        $this->serverBcast = $plugin->config["countdown_bcast_serverwide"] === true;
    }

    /**
     * @param int $tick
     */
    public function onRun(int $tick){
        if($this->countDown === 0){
            $this->arena->startGame();
            return;
        }
        if($this->plugin->config["countdown_bcast"] === true) {
            $msg = str_replace(["{COUNT}","{ARENA}"],[$this->countDown, $this->arena->getName()], $this->plugin->utils->colourise($this->plugin->messages["broadcasts"]["countdown"]));
            if ($this->countDown <= 5) {
                if(!$this->serverBcast){
                    $this->arena->broadcastMessage($msg);
                } else{
                    $this->plugin->getServer()->broadcastMessage($msg);
                }
            } else {
                if (($this->countDown % $this->plugin->config["countdown_bcast_interval"]) === 0) {
                    if(!$this->serverBcast){
                        $this->arena->broadcastMessage($msg);
                    } else {
                        $this->plugin->getServer()->broadcastMessage($msg);
                    }
                }
            }
        }
        $this->countDown--;
    }
}