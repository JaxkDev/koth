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
namespace Jack\KOTH;

use pocketmine\Player;

use Jack\KOTH\Main;
/*

NOTES:
- so if king dies out of box or goes out of box, its race to the box, 
  if killed in box from someone inside box that killer is king
  if someone outside the box kills him next to box is king
  if multple people in box when next king selection, person closest to middle is crowned.

- EventHandler handles all events from pmmp, then passed here.

*/

class Arena{
    public $spawns = []; //[[12,50,10],[],[],[]] list of spawn points.
    public $hill = []; //[[20,50,20],[30,50,20]] two points corner to corner.
    public $players = []; //list of all players currently ingame.
    public $limit;

    public function __construct(Main $plugin, string $name, int $limit, array $hill, array $spawns){
        $this->plugin = $plugin;
        $this->hill = $hill;
        $this->limit = $limit;
        $this->name = $name;
        $this->spawns = $spawns;
    }

    public function spawnPlayer(bool $random){}
    public function addPlayer(Player $player){}
}