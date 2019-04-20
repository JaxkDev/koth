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
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);
namespace Jackthehack21\KOTH;



use pocketmine\event\Listener;
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};;
//use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\{PlayerDeathEvent, PlayerRespawnEvent, PlayerQuitEvent, PlayerGameModeChangeEvent, PlayerCommandPreprocessEvent};

;

class EventHandler implements Listener{

    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->inGame($playerName) === true){
            //notify players in that arena that a player has left, adjust scoreboard.
            $arena = $this->plugin->getArenaByPlayer($playerName);
            $arena->removePlayer($event->getPlayer(), "Disconnected from server."); //arg1 is reason.
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->inGame($playerName) === true){
            //Re-spawn player in different spawn location.
            $arena = $this->plugin->getArenaByPlayer($playerName);
            $pos = $arena->getSpawn(true);
            $event->setRespawnPosition($pos);
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        if($this->plugin->inGame($player->getLowerCaseName()) === true){
            //todo config.
            $event->setKeepInventory(true);
        }
    }

    /*public function onLevelChange(EntityLevelChangeEvent $event){
        $targetLevel = $event->getTarget();
        //todo hack for per world FTP (decide how to handle this :/ )
    }*/

    /**
     * @param PlayerCommandPreprocessEvent $event
     */
    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
        $player = $event->getPlayer();
        if($this->plugin->inGame($player->getLowerCaseName()) === true and $this->plugin->config["block_commands"] === true and substr($event->getMessage(), 0, 5) !== "/koth"){
            $event->setCancelled(true);
        }
    }

    /**
     * @param PlayerGameModeChangeEvent $event
     */
    public function onPlayerGameModeChange(PlayerGameModeChangeEvent $event){
        if($this->plugin->inGame($event->getPlayer()->getLowerCaseName()) === true){
            if($event->getPlayer()->isOp() === false and $this->plugin->config["prevent_gamemode_change"] === true){
                $event->setCancelled(true);
                $this->plugin->getArenaByPlayer($event->getPlayer()->getLowerCaseName())->broadcastMessage($event->getPlayer()->getName()." Attempted to change gamemode.");
                //todo config msg.
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event){
        if($this->plugin->inGame($event->getPlayer()->getLowerCaseName()) === true and $this->plugin->config["prevent_break"] === true){
            $event->setCancelled(true);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event){
        if($this->plugin->inGame($event->getPlayer()->getLowerCaseName()) === true and $this->plugin->config["prevent_place"] === true){
            $event->setCancelled(true);
        }
    }

}