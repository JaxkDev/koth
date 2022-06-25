<?php

namespace Jackthehack21\KOTH;

use pocketmine\level\Level;
use pocketmine\utils\TextFormat as C;

class Utils{

    public $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }
	
	/**
	 * Credit to https://gist.githubusercontent.com/LeoLopesWeb/a3e0bba7fe66a6af1e50eef3d231f2da/raw/16edbd69a709ca21d24f2774af5ccf68297aa97d/.php
	 * Slightly modified for our use.
	 * @param int $seconds
	 *
	 * @return string
	 */
	function secToHR(int $seconds) {
	  $days = floor($seconds / 86400);
	  $hours = floor(($seconds / 3600) % 24);
	  $minutes = floor(($seconds / 60) % 60);
	  $seconds = $seconds % 60;
	  return $days > 0 ? "$days days, $hours hours, $minutes minutes" : ($hours > 0 ? "$hours hours, $minutes minutes" : ($minutes > 0 ? "$minutes minutes, $seconds seconds" : "$seconds seconds"));
	}

    /**
     * @param string $base
     * @param string $new
     *
     * @return int
     */
    public function compareVersions(string $base, string $new) : int{
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
            } else {
                return 1;
            }
        }
        return 0;
    }

    /**
     * @param string $search
     * @param array $arr
     *
     * @return int
     */
    public function getClosest(string $search, array $arr) : int{
        //https://stackoverflow.com/a/5464961 - Thanks :)
        $closest = null;
        foreach ($arr as $item) {
            if ($closest === null || abs($search - $closest) > abs($item - $search)) {
                $closest = $item;
            }
        }
        return $closest;
    }

    /**
     * NOTICE: Use with caution, if used incorrectly can have significant consequences.
     * TODO remove, too dangerous (some people like to give perms to everything that pops up)
     * @param string $dir
     */
    public function rmalldir(string $dir): void{
        if($dir == "" or $dir == "/" or $dir == "C:/") return; //tiny safeguard.
        $tmp = scandir($dir);
        foreach ($tmp as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            if (is_dir($path)) {
                $this->rmalldir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

	/**
	 * Modified version of PMMP's one.
	 * @param string $name
	 * @return Level|null
	 */
	public function getLevelByName(string $name) : ?Level{
		foreach($this->plugin->getServer()->getLevels() as $level){
			if(strtolower($level->getFolderName()) === strtolower($name) or strtolower($level->getName()) === strtolower($name)){
				return $level;
			}
		}
		if($this->plugin->getServer()->loadLevel($name) === false) {
			$this->plugin->debug("Failed to find or load the level '" . $name . "'");
			return null;
		}
		else $this->plugin->debug("Loaded level '".$name."'");
		return $this->getLevelByName($name);
	}

    /**
     * @param string $msg
     *
     * @return string
     */
    public function colourise(string $msg) : string{
        $colour = array("{PREFIX}","{BLACK}","{DARK_BLUE}","{DARK_GREEN}","{DARK_AQUA}","{DARK_RED}","{DARK_PURPLE}","{GOLD}","{GRAY}","{DARK_GRAY}","{BLUE}","{GREEN}","{AQUA}","{RED}","{LIGHT_PURPLE}","{YELLOW}","{WHITE}","{OBFUSCATED}","{BOLD}","{STRIKETHROUGH}","{UNDERLINE}","{ITALIC}","{RESET}");
        $keys = array($this->plugin->prefix, C::BLACK, C::DARK_BLUE, C::DARK_GREEN, C::DARK_AQUA, C::DARK_RED, C::DARK_PURPLE, C::GOLD, C::GRAY, C::DARK_GRAY, C::BLUE, C::GREEN, C::AQUA, C::RED, C::LIGHT_PURPLE, C::YELLOW, C::WHITE, C::OBFUSCATED, C::BOLD, C::STRIKETHROUGH, C::UNDERLINE, C::ITALIC, C::RESET);
        return str_replace(
            $colour,
            $keys,
            $msg
        );
    }
}
