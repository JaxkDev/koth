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

use JaxkDev\KOTH\Main;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;

abstract class KothEvent extends PluginEvent implements Cancellable{
    use CancellableTrait;

    private string $reason = "Event Cancelled";
    private Main $plugin;

    public function __construct(Main $plugin){
    	$this->plugin = $plugin;
        $this->plugin->getLogger()->debug("Event '".$this->getEventName()."' is being constructed...");
        parent::__construct($plugin);
    }

    public function getReason(): string{
        return $this->reason;
    }

    public function setReason(string $reason): void{
        $this->reason = $reason;
    }

    public function call(): void{
		$this->plugin->getLogger()->debug("Event '".$this->getEventName()."' Called.");
		parent::call();
	}
}