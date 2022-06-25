<?php

namespace JaxkDev\KOTH;

use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\utils\TextFormat as C;

class Utils{
    public Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

	/**
	 * Credit to https://gist.githubusercontent.com/LeoLopesWeb/a3e0bba7fe66a6af1e50eef3d231f2da/raw/16edbd69a709ca21d24f2774af5ccf68297aa97d/.php
	 * Slightly modified for our use.
	 */
	public static function secToHR(int $seconds): string{
	  $days = floor($seconds / 86400);
	  $hours = floor(($seconds / 3600) % 24);
	  $minutes = floor(($seconds / 60) % 60);
	  $seconds = $seconds % 60;
	  return $days > 0 ? "$days days, $hours hours, $minutes minutes" : ($hours > 0 ? "$hours hours, $minutes minutes" : ($minutes > 0 ? "$minutes minutes, $seconds seconds" : "$seconds seconds"));
	}

    public static function compareVersions(string $base, string $new): int{
        $baseParts = explode(".",$base);
        $baseParts[2] = explode("-beta",$baseParts[2])[0];
        if(sizeof(explode("-beta",explode(".",$base)[2])) >1){
            $baseParts[3] = explode("-beta",explode(".",$base)[2])[1];
        }
        $newParts = explode(".",$new);
        $newParts[2] = explode("-beta",$newParts[2])[0];
        if(sizeof(explode("-beta",explode(".",$new)[2])) >1){
            $newParts[3] = explode("-beta",explode(".",$new)[2])[1];
        }
        if(intval($newParts[0]) > intval($baseParts[0])){
            return 1;
        }
        if(intval($newParts[0]) < intval($baseParts[0])){
            return -1;
        }
        if(intval($newParts[1]) > intval($baseParts[1])){
            return 1;
        }
        if(intval($newParts[1]) < intval($baseParts[1])){
            return -1;
        }
        if(intval($newParts[2]) > intval($baseParts[2])){
            return 1;
        }
        if(intval($newParts[2]) < intval($baseParts[2])){
            return -1;
        }
        if(isset($baseParts[3])){
            if(isset($newParts[3])){
                if(intval($baseParts[3]) > intval($newParts[3])){
                    return -1;
                }
                if(intval($baseParts[3]) < intval($newParts[3])){
                    return 1;
                }
            }else{
                return 1;
            }
        }
        return 0;
    }

	/**
	 * Modified version of world managers, but checks both display name and folder name.
	 * @param string $name
	 * @return World|null
	 */
	public static function getLevelByName(string $name): ?World{
        $server = Server::getInstance();
		foreach($server->getWorldManager()->getWorlds() as $world){
			if(strtolower($world->getFolderName()) === strtolower($name) or strtolower($world->getDisplayName()) === strtolower($name)){
				return $world;
			}
		}
		if($server->getWorldManager()->loadWorld($name) === false){
			return null;
		}
		return self::getLevelByName($name);
	}

    public function colourise(string $msg): string{
        $colour = ["{PREFIX}","{BLACK}","{DARK_BLUE}","{DARK_GREEN}","{DARK_AQUA}","{DARK_RED}","{DARK_PURPLE}","{GOLD}","{GRAY}","{DARK_GRAY}","{BLUE}","{GREEN}","{AQUA}","{RED}","{LIGHT_PURPLE}","{YELLOW}","{WHITE}","{OBFUSCATED}","{BOLD}","{STRIKETHROUGH}","{UNDERLINE}","{ITALIC}","{RESET}"];
        $keys = [Main::PREFIX, C::BLACK, C::DARK_BLUE, C::DARK_GREEN, C::DARK_AQUA, C::DARK_RED, C::DARK_PURPLE, C::GOLD, C::GRAY, C::DARK_GRAY, C::BLUE, C::GREEN, C::AQUA, C::RED, C::LIGHT_PURPLE, C::YELLOW, C::WHITE, C::OBFUSCATED, C::BOLD, C::STRIKETHROUGH, C::UNDERLINE, C::ITALIC, C::RESET];
        return str_replace(
            $colour,
            $keys,
            $msg
        );
    }
}
