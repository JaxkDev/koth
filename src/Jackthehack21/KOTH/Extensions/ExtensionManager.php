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
namespace Jackthehack21\KOTH\Extensions;

use Jackthehack21\KOTH\Main;
use Jackthehack21\KOTH\Tasks\ExtensionDownloadTask;
use Jackthehack21\KOTH\Tasks\ExtensionReleasesTask;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\HandlerList;
use Throwable;

class ExtensionManager
{
    /** @var Main */
    private $plugin;

    public $prefix = "[Extensions] : ";

    /** @var array[]|BaseExtension[][]|int[][] */
    private $extensions = [];
    // [0 => [BaseExtension,0]]; arg 1 (0) is its status, so 0-disabled,1-loaded,2-enabled,3-unknown.

    private $extensionReleases = [];

    public const BASE_URL = "https://raw.githubusercontent.com/jackthehack21/koth-extensions/release/";

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->plugin->getServer()->getAsyncPool()->submitTask(new ExtensionReleasesTask($this::BASE_URL."release.json"));
    }

    /**
     * @return array
     */
    public function getExtensionReleases(): array
    {
        return $this->extensionReleases;
    }

    /**
     * @param array $data
     */
    public function setExtensionReleases(array $data): void
    {
        $this->extensionReleases = $data;
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     * @return bool
     */
    public function handleCommand(CommandSender $sender, array $args) : bool{
        array_shift($args);
        if(count($args) === 0){
            $sender->sendMessage(C::RED."Unknown command, try /koth extensions help");
            return true;
        }
        switch ($args[0]){
            case '?':
            case 'help':
                $sender->sendMessage(C::YELLOW."[".C::AQUA."KOTH ".C::RED."-".C::GREEN." Extensions Help".C::YELLOW."]");
                $sender->sendMessage(C::GOLD."/koth extensions help ".C::RESET."- Sends extensions help.");
                $sender->sendMessage(C::GOLD."/koth extensions list ".C::RESET."- Sends list of extensions and their status.");
                $sender->sendMessage(C::GOLD."/koth extensions search (extension name) ".C::RESET."- Search our official repo for verified extensions.");
                $sender->sendMessage(C::GOLD."/koth extensions install (extension name) ".C::RESET."- Install a verified extension from our repo.");
                $sender->sendMessage(C::GOLD."/koth extensions uninstall (extension name) ".C::RESET."- Uninstall a extension (deleting it from disk)");
                return true;
            case 'list':
                $codes = ["Disabled", "Loaded", "Enabled"];
                $sender->sendMessage(C::GOLD."Extension Name | Status");
                foreach($this->extensions as $extension){
                    $sender->sendMessage($extension[0]->getExtensionData()->getName()." | ".$codes[$extension[1]]);
                }
                return true;
            case 'search':
                array_shift($args);
                if(count($args) === 0){
                    $sender->sendMessage(C::RED."Usage: /koth extensions search (extension name)");
                    return true;
                }
                $count = 0;
                $name = strtolower(join(" ",$args));
                $nameList = array_keys($this->getExtensionReleases());
                $sender->sendMessage(C::GREEN."Searching repo with key word '${name}'");
                foreach($nameList as $ext){
                    if(strpos(strtolower($ext), $name) !== false){
                        $sender->sendMessage(C::AQUA."> ".$ext);
                        $count++;
                    }
                }
                $sender->sendMessage(C::GOLD."Found ${count} results.");
                return true;
            case 'add':
            case 'install':
                array_shift($args);
                if(count($args) === 0){
                    $sender->sendMessage(C::RED."Usage: /koth extensions install (extension name)");
                    return true;
                }
                $name = strtolower(join(" ",$args));
                $nameList = array_keys($this->getExtensionReleases());
                $sender->sendMessage(C::GREEN."Searching repo for '${name}' extension...");
                foreach($nameList as $ext){
                    if(strtolower($ext) === $name){
                        $sender->sendMessage(C::AQUA."Found it, beginning download of '${ext}'");
                        $this->plugin->getServer()->getAsyncPool()->submitTask(new ExtensionDownloadTask($this::BASE_URL."{$ext}.php", "${ext}.php", $this->plugin->getDataFolder()."extensions/${ext}.php"));
                        return true;
                    }
                }
                $sender->sendMessage(C::RED."Couldn't find '${name}' try using `/koth extensions search` first.");
                return true;
            case 'delete':
            case 'uninstall':
            case 'remove':
                array_shift($args);
                if(count($args) === 0){
                    $sender->sendMessage(C::RED."Usage: /koth extensions uninstall (extension name)");
                    return true;
                }
                $name = strtolower(join(" ",$args));
                for($i = 0; $i < count($this->extensions); $i++){
                    if(strtolower($this->extensions[$i][0]->getExtensionData()->getName()) == $name){
                        $sender->sendMessage(C::GREEN."Uninstalling extension ${name}...");
                        $this->disableExtension($this->extensions[$i][0]->getExtensionData()->getName());
                        unlink($this->plugin->getDataFolder()."extensions/".$this->extensions[$i][0]->getExtensionData()->getName().".php");
                        $this->plugin->debug($this->prefix."Deleting extension ${name} from disk.");
                        unset($this->extensions[$i]);
                        $this->extensions = array_values($this->extensions); //reset index's
                        $sender->sendMessage(C::GOLD."Extension ${name} has been disabled & removed.");
                    }
                }
                return true;
        }
        $sender->sendMessage(C::RED."Unknown command, try /koth extensions help");
        return true;
    }

    /**
     * @param string $fileName
     */
    public function handleDownloaded(string $fileName): void{
        $this->plugin->debug($this->prefix."Handling downloaded extension '${fileName}'");
        if(file_exists($this->plugin->getDataFolder()."extensions/manifest.json")) {
            $manifest = json_decode(file_get_contents($this->plugin->getDataFolder() . "extensions/manifest.json"), true);
            if ($manifest === null) {
                $manifest = ["version" => 0, "verified_extensions" => []];
            }
        } else {
            $manifest = ["version" => 0, "verified_extensions" => []];
        }
        if(in_array(rtrim($fileName, ".php"), $manifest["verified_extensions"], true) === false) $manifest["verified_extensions"][] = rtrim($fileName, ".php");
        file_put_contents($this->plugin->getDataFolder() . "extensions/manifest.json", json_encode($manifest));

        $this->loadExtension($fileName, true);
        $this->enableExtension(rtrim($fileName, ".php"));
    }

    /**
     * @param string $fileName
     * @param bool $mustBeVerified
     * @return bool
     */
    public function loadExtension(string $fileName, bool $mustBeVerified) : bool{
        if(substr($fileName, -4) === ".php") {
            $name = rtrim($fileName, ".php");
            $path = $this->plugin->getDataFolder() . "extensions/${name}";
            $namespace = "Jackthehack21\\KOTH\\Extensions\\${name}";

            try{

                if($mustBeVerified) {
                    if(!file_exists($this->plugin->getDataFolder()."extensions/manifest.json")) return false;
                    $manifest = json_decode(file_get_contents($this->plugin->getDataFolder() . "extensions/manifest.json"), true);
                    if ($manifest === null) {
                        $this->plugin->debug($this->prefix . "manifest.json for extensions is corrupt, file is deleted now but all installed extensions via /koth extensions will have to be re-installed, or in config.yml set allow_unknown_extensions to true.");
                        unlink($this->plugin->getDataFolder() . "extensions/manifest.json");
                        return false;
                    }

                    if (!in_array($name, $manifest["verified_extensions"])) {
                        $this->plugin->debug($this->prefix."Skipped extension '${name}' as it is not verified.");
                        return false;
                    }
                }

                /** @noinspection PhpIncludeInspection */
                include_once $path . ".php";

                if (!is_a($namespace, BaseExtension::class, true)) {
                    $this->plugin->debug($this->prefix . "Failed to load extension '${name}' as class is not valid/found.");
                    return false;
                }

                foreach ($this->extensions as $extension) {
                    if ($extension[0]->getExtensionData()->getName() === $name) {
                        $this->plugin->debug($this->prefix . "Failed to load extension '${name}' as the extension already exists. (or has same name)");
                        return false;
                    }
                }

                $this->extensions[] = [new $namespace($this->plugin), 0];

                for($i = 0; $i < count($this->extensions); $i++){
                    if($this->extensions[$i][0]->getExtensionData()->getName() === $name){
                        if($this->extensions[$i][0]->onLoad()){
                            $this->extensions[$i][1] = 1;
                            $this->plugin->debug($this->prefix . "Extension '${name}' successfully loaded.");
                            return true;
                        }
                    }
                }
            } catch (Throwable $error){
                $this->plugin->getLogger()->error($this->prefix . "While loading extension '${name}' this error occurred:");
                $this->plugin->getLogger()->logException($error);
                return false;
            }

            $this->plugin->debug($this->prefix . "Extension '${name}' added to extensions list, but failed to load.");
            return true;
        }
        return false;
    }

    /**
     * @param bool $allowUnknown
     */
    public function loadExtensions(bool $allowUnknown = false) : void{
        $this->plugin->debug($this->prefix."Loading ".($allowUnknown ? "all":"only verified")." extensions...");
        if(!is_dir($this->plugin->getDataFolder()."extensions")) @mkdir($this->plugin->getDataFolder()."extensions");
        $count = 0;
        $content = scandir($this->plugin->getDataFolder()."extensions/");
        for($i = 0; $i < count($content); $i++){
            if($this->loadExtension($content[$i], !$allowUnknown) === true){
                $count++;
            }
        }

        //todo in way future order of load.

        $this->plugin->debug($this->prefix."Successfully loaded ${count} extensions.");
        return;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function enableExtension(string $name): bool{
        for($i = 0; $i < count($this->extensions); $i++){
            try {
                if ($this->extensions[$i][1] != 1) continue;
                if ($this->extensions[$i][0]->getExtensionData()->getName() !== $name) continue;
                if ($this->extensions[$i][0]->onEnable() === false) {
                    $this->plugin->debug($this->prefix . "Extension '${name}' failed to enable.");
                    return false;
                } else {
                    $this->plugin->debug($this->prefix . "Extension '${name}' Enabled.");
                    $this->plugin->getServer()->getPluginManager()->registerEvents($this->extensions[$i][0], $this->plugin);
                    $this->extensions[$i][1] = 2;
                    return true;
                }
            } catch (Throwable $error){
                $this->plugin->getLogger()->error($this->prefix . "While enabling extension '${name}' this error occurred: ");
                $this->plugin->getLogger()->logException($error);
                return false;
            }
        }
        return false;
    }

    public function enableExtensions() : void{
        $this->plugin->debug($this->prefix."Enabling all extensions...");
        $count = 0;
        for($i = 0; $i < count($this->extensions); $i++){
            if($this->enableExtension($this->extensions[$i][0]->getExtensionData()->getName()) === true){
                $count++;
            }
        }
        $this->plugin->debug($this->prefix."Successfully enabled ${count} extensions.");
        return;
    }

    /**
     * @param string $name
     */
    public function disableExtension(string $name) : void{
        $this->plugin->debug($this->prefix."Disabling extension ${name}...");
        for($i = 0; $i < count($this->extensions); $i++){
            if($this->extensions[$i][1] == 0) continue;
            if($this->extensions[$i][0]->getExtensionData()->getName() != $name) continue;
            $this->extensions[$i][0]->onDisable();
            $this->extensions[$i][1] = 0;
            HandlerList::unregisterAll($this->extensions[$i][0]); //unregister events. (something i completely forgot to do.)
            $this->plugin->debug($this->prefix."Extension ${name} disabled, cleaning manifest.json");

            if(file_exists($this->plugin->getDataFolder()."extensions/manifest.json")) {
                $manifest = json_decode(file_get_contents($this->plugin->getDataFolder() . "extensions/manifest.json"), true);
                if ($manifest === null) {
                    return;
                }
            } else {
                return;
            }
            if (($key = array_search($this->extensions[$i][0]->getExtensionData()->getName(), $manifest["verified_extensions"])) !== false) {
                unset($manifest["verified_extensions"][$key]);
                $manifest["verified_extensions"] = array_values($manifest["verified_extensions"]);
            }
            file_put_contents($this->plugin->getDataFolder() . "extensions/manifest.json", json_encode($manifest));
            return;
        }
    }

    public function disableExtensions() : void{
        $this->plugin->debug($this->prefix."Disabling Extensions...");
        foreach ($this->extensions as $ext){
            $this->disableExtension($ext[0]->getExtensionData()->getName());
        }
        $this->plugin->debug($this->prefix."All extensions now disabled.");
        $this->extensions = []; //todo
        return;
    }
}