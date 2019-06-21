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
use pocketmine\Player;

/*
 * NOTICE: This event may be cancellable however, if player is quiting MC (app)
 *         then the event will not matter unless messages like leaveReason and silent are changed.
 *         whether cancelled or not the player will be removed if leaving the app.
 */

class ArenaRemovePlayerEvent extends KothEvent{

    /** @var Arena */
    private $arena;

    /** @var Player */
    private $player;

    /** @var string */
    private $leaveReason;

    /** @var bool */
    private $silent;

    /**
     * ArenaRemovePlayerEvent constructor.
     * @param Main $plugin
     * @param Arena $arena
     * @param Player $player
     * @param string $leaveReason
     * @param bool $silent
     */
    public function __construct(Main $plugin, Arena $arena, Player $player, string $leaveReason, bool $silent){
        $this->arena = $arena;
        $this->player = $player;
        $this->silent = $silent;
        $this->leaveReason = $leaveReason;
        parent::__construct($plugin);
    }

    public function setSilent(bool $silent): void{
        $this->silent = $silent;
    }

    /**
     * @return bool
     */
    public function isSilent(): bool{
        return $this->silent;
    }

    /**
     * @return string
     */
    public function getLeaveReason(): string{
        return $this->leaveReason;
    }

    /**
     * @param string $leaveReason
     */
    public function setLeaveReason(string $leaveReason): void{
        $this->leaveReason = $leaveReason;
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