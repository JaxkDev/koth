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
namespace Jackthehack21\KOTH\Events;

use Jackthehack21\KOTH\Arena;
use Jackthehack21\KOTH\Main;

/*
 * Note: The event is only used when the command /koth remove/delete is used,
 * NOT when the plugins removeArena is called (so it will not work if plugins call the function)
 *
 * You have been warned.
 */

class ArenaEndEvent extends KothEvent{

    /** @var Arena */
    private $arena;

    /** @var int */
    private $secondsLeft;

    public function __construct(Main $plugin, Arena $arena){
        $this->arena = $arena;
        $this->secondsLeft = $arena->time;
        parent::__construct($plugin);
    }

    /**
     * @return Arena
     */
    public function getArena(): Arena{
        return $this->arena;
    }

    /**
     * @return int
     */
    public function getSecondsLeft(): int
    {
        return $this->secondsLeft;
    }

    /**
     * @param int $seconds
     * Notice: Change this when cancelling otherwise it will continue to
     *         send out this event in a loop until not cancelled.
     */
    public function setSecondsLeft(int $seconds): void{
        $this->secondsLeft = $seconds;
    }
}