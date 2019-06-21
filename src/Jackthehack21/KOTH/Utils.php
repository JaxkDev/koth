<?php

namespace Jackthehack21\KOTH;

use pocketmine\utils\TextFormat as C;

class Utils{

    public $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
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
     *
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