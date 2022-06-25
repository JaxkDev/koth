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
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

/*
 * Note: The event is only used when the command /koth remove/delete is used,
 * NOT when the plugins removeArena is called (so it will not work if plugins call the function)
 *
 * You have been warned.
 */

class ArenaDeleteEvent extends KothEvent{

    private $destroyer;

    /** @var Arena */
    private $arena;

    public function __construct(Main $plugin, $destroyer, Arena $arena){
        $this->destroyer = $destroyer;
        $this->arena = $arena;
        parent::__construct($plugin);
    }

    /** @return Player|ConsoleCommandSender|CommandSender|null */
    public function getDestroyer(){
        return $this->destroyer;
    }

    /**
     * @return Arena
     */
    public function getArena() : Arena{
        return $this->arena;
    }
}