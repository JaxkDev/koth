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
*   Copyright (C) 2019-2020 JaxkDev
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

namespace JaxkDev\KOTH;

use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};;
//use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\{PlayerDeathEvent, PlayerRespawnEvent, PlayerQuitEvent, PlayerGameModeChangeEvent, PlayerCommandPreprocessEvent};;


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
            $this->plugin->debug($playerName." has left the game, informing arena...");
            $arena = $this->plugin->getArenaByPlayer($playerName);
            $arena->removePlayer($event->getPlayer(), "Disconnected from server.");
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->inGame($playerName) === true){
            $this->plugin->debug($playerName." was re-spawned.");
            $event->setRespawnPosition($this->plugin->getArenaByPlayer($playerName)->getSpawn(true));
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        if($this->plugin->inGame($player->getLowerCaseName()) === true and $this->plugin->config["keep_inventory"] === true){
            $this->plugin->debug($player->getLowerCaseName()."'s inventory was not reset (death)");
            $event->setKeepInventory(true);
        }
    }

    /*public function onLevelChange(EntityLevelChangeEvent $event){
        $targetLevel = $event->getTarget();
        //todo hack for per world FTP (decide how to handle this :/ ) (Beta4)
    }*/

    /**
     * @param PlayerCommandPreprocessEvent $event
     */
    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
        $player = $event->getPlayer();
        if($this->plugin->inGame($player->getLowerCaseName()) === true){
        	if($this->plugin->config["block_commands"] === true and substr($event->getMessage(), 0, 5) !== "/koth" and substr($event->getMessage(), 0, 1) === "/") {
				$this->plugin->debug($player->getName() . " tried to use command '" . $event->getMessage() . "' but was cancelled.");
				$event->setCancelled(true);
				$player->sendMessage($this->plugin->prefix.C::RED."You are not allowed to use commands in game except: /koth, ");//TODO messages.yml
			}
        	elseif($this->plugin->config["block_messages"] === true){
        		$this->plugin->debug($player->getName() . " tried to send '".$event->getMessage()."' globally, but was cancelled.");
				$player->sendMessage($this->plugin->prefix.C::RED."You are not allowed to chat while in game.");//TODO messages.yml
        		$event->setCancelled(true);
			}
        }
    }

    /**
     * @param PlayerGameModeChangeEvent $event
     */
    public function onPlayerGameModeChange(PlayerGameModeChangeEvent $event){
        if($this->plugin->inGame($event->getPlayer()->getLowerCaseName()) === true){
            if($event->getPlayer()->isOp() === false and $this->plugin->config["prevent_gamemode_change"] === true){
                $this->plugin->debug($event->getPlayer()->getName()." attempted to change gamemode but was stopped.");
                $event->getPlayer()->sendMessage($this->plugin->prefix.C::RED."You are not allowed to changed gamemode while in game.");//TODO messages.yml
                $event->setCancelled(true);
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event){
        if($this->plugin->inGame($event->getPlayer()->getLowerCaseName()) === true and $this->plugin->config["prevent_break"] === true){
            $this->plugin->debug($event->getPlayer()->getName()." attempted to break a block but was stopped.");
			$event->getPlayer()->sendMessage($this->plugin->prefix.C::RED."You are not allowed to break things while in game.");//TODO messages.yml
			$event->setCancelled(true);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event){
        if($this->plugin->inGame($event->getPlayer()->getLowerCaseName()) === true and $this->plugin->config["prevent_place"] === true){
            $this->plugin->debug($event->getPlayer()->getName()." attempted to place a block but was stopped.");
			$event->getPlayer()->sendMessage($this->plugin->prefix.C::RED."You are not allowed to place things while in game.");//TODO messages.yml
			$event->setCancelled(true);
        }
    }

}