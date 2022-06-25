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
use pocketmine\player\Player;

/*
 * NOTICE: This event may be cancellable however, if player is leaving server (eg closed Minecraft)
 *         then the event will not matter unless messages like leaveReason and silent are changed.
 *         whether cancelled or not the player will be removed if leaving the server.
 */

class ArenaRemovePlayerEvent extends KothEvent{
    private Arena $arena;
    private Player $player;
    private string $leaveReason;
    private bool $silent;

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

    public function isSilent(): bool{
        return $this->silent;
    }

    public function getLeaveReason(): string{
        return $this->leaveReason;
    }

    public function setLeaveReason(string $leaveReason): void{
        $this->leaveReason = $leaveReason;
    }

    public function getArena(): Arena{
        return $this->arena;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}