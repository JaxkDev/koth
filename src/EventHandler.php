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

namespace JaxkDev\KOTH;

use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
//use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;


class EventHandler implements Listener{
    private Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if(($arena = $this->plugin->getArenaByPlayer($playerName)) !== null){
            $this->plugin->getLogger()->debug($playerName." has left the game, informing arena...");
            $arena->removePlayer($event->getPlayer(), "Disconnected from server.");
        }
    }

    public function onRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if(($arena = $this->plugin->getArenaByPlayer($playerName)) !== null){
            $this->plugin->getLogger()->debug($playerName." was re-spawned.");
            $event->setRespawnPosition($arena->getSpawn(true));
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->getArenaByPlayer($playerName) !== null and $this->plugin->config["keep_inventory"] === true){
            $this->plugin->getLogger()->debug($playerName."'s inventory was not reset (death)");
            $event->setKeepInventory(true);
        }
    }

    /*public function onLevelChange(EntityLevelChangeEvent $event){
        $targetLevel = $event->getTarget();
        //todo hack for per world FTP (decide how to handle this :/ ) (Beta4)
    }*/

    public function onCommand(CommandEvent $event){
        $player = $event->getSender();
        $playerName = strtolower($player->getName());
        if($this->plugin->getArenaByPlayer($playerName) !== null){
        	if($this->plugin->config["block_commands"] === true and !str_starts_with($event->getCommand(), "koth")){
				$this->plugin->getLogger()->debug($player->getName() . " tried to use command '/" . $event->getCommand() . "' but was cancelled.");
				$event->cancel();
				$player->sendMessage(Main::PREFIX.C::RED."You are not allowed to use commands in-game except: /koth");//TODO messages.yml
			}
        }
    }
    
    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->getArenaByPlayer($playerName) !== null){
            if($this->plugin->config["block_messages"] === true){
                $this->plugin->getLogger()->debug($player->getName() . " tried to send '".$event->getMessage()."' globally, but was cancelled.");
                $player->sendMessage(Main::PREFIX.C::RED."You are not allowed to chat while in-game.");//TODO messages.yml
                $event->cancel();
            }
        }
    }

    public function onPlayerGameModeChange(PlayerGameModeChangeEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->getArenaByPlayer($playerName) !== null){
            if($this->plugin->config["prevent_gamemode_change"] === true){
                $this->plugin->getLogger()->debug($playerName." attempted to change gamemode but was stopped.");
                $player->sendMessage(Main::PREFIX.C::RED."You are not allowed to changed gamemode while in game.");//TODO messages.yml
                $event->cancel();
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->getArenaByPlayer($playerName) !== null and $this->plugin->config["prevent_break"] === true){
            $this->plugin->getLogger()->debug($playerName." attempted to break a block but was stopped.");
			$player->sendMessage(Main::PREFIX.C::RED."You are not allowed to break things while in game.");//TODO messages.yml
			$event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        $playerName = strtolower($player->getName());
        if($this->plugin->getArenaByPlayer($playerName) !== null and $this->plugin->config["prevent_place"] === true){
            $this->plugin->getLogger()->debug($playerName." attempted to place a block but was stopped.");
			$player->sendMessage(Main::PREFIX.C::RED."You are not allowed to place things while in game.");//TODO messages.yml
			$event->cancel();
        }
    }

}