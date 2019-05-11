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
use Jackthehack21\KOTH\Tasks\ExtensionReleasesTask;

class ExtensionManager
{
    /** @var Main */
    private $plugin;

    public $prefix = "[Extensions] : ";

    /** @var array[]|BaseExtension[][]|int[][] */
    private $extensions = [];
    // [0 => [BaseExtension,0]]; arg 1 (0) is its status, so 0-disabled,1-loaded,2-enabled,3-unknown.

    private $extensionReleases = [];

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->plugin->getServer()->getAsyncPool()->submitTask(new ExtensionReleasesTask("https://raw.githubusercontent.com/jackthehack21/koth-extensions/release/release.json"));
    }

    /**
     * @param array $data
     */
    public function updateExtensionReleases(array $data): void
    {
        $this->extensionReleases = $data;
    }

    /**
     * @param string $fileName
     */
    public function handleDownloaded(string $fileName): void{
        if(file_exists($this->plugin->getDataFolder())) {
            $manifest = json_decode(file_get_contents($this->plugin->getDataFolder() . "extensions/manifest.json"), true);
            if ($manifest === null) {
                $manifest = ["version" => 0, "verified_extensions" => []];
            }
        } else {
            $manifest = ["version" => 0, "verified_extensions" => []];
        }
        $manifest["verified_extensions"][] = rtrim($fileName, ".php");
        file_put_contents($this->plugin->getDataFolder() . "extensions/manifest.json", json_encode($manifest));

        $this->loadExtension($fileName, true);
        $this->enableExtension(rtrim($fileName, ".php"));
    }

    /**
     * @param string $fileName
     * @param bool $mustBeVerified
     * @return bool
     *
     * @internal
     */
    public function loadExtension(string $fileName, bool $mustBeVerified) : bool{
        if(substr($fileName, -4) === ".php") {
            $name = rtrim($fileName, ".php");
            $path = $this->plugin->getDataFolder() . "extensions/${name}";
            $namespace = "Jackthehack21\\KOTH\\Extensions\\${name}";

            if($mustBeVerified) {
                if(!file_exists($this->plugin->getDataFolder()."extensions/manifest.json")) return false;
                $manifest = json_decode(file_get_contents($this->plugin->getDataFolder() . "extensions/manifest.json"), true);
                if ($manifest === null) {
                    $this->plugin->debug($this->prefix . "manifest.json for extensions is corrupt, file is deleted now but all installed extensions via /koth extensions will have to be re-installed, or in config.yml set allow_unknown_extensions to true.");
                    unlink($this->plugin->getDataFolder() . "extensions/manifest.json");
                    return false;
                }

                if (!in_array($name, $manifest["verified_extensions"])) {
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
            $this->plugin->debug($this->prefix . "Extension '${name}' added to extensions list.");
            return true;
        }
        return false;
    }

    /**
     * @param bool $allowUnknown
     */
    public function loadExtensions(bool $allowUnknown = false) : void{
        $this->plugin->debug($this->prefix."Loading ".($allowUnknown ? "all":"only verified")." extensions...");
        @mkdir($this->plugin->getDataFolder()."extensions");
        $count = 0;
        $content = scandir($this->plugin->getDataFolder()."extensions/");
        for($i = 0; $i < count($content); $i++){
            $this->loadExtension($content[$i], !$allowUnknown);
        }

        for($i = 0; $i < count($this->extensions); $i++){
            if($this->extensions[$i][0]->onLoad() === false){
                $this->plugin->debug($this->prefix."Extension '".$this->extensions[$i][0]->getExtensionData()->getName()."' failed to load.");
                $this->extensions[$i][1] = 0;
            } else {
                $this->plugin->debug($this->prefix."Extension '".$this->extensions[$i][0]->getExtensionData()->getName()."' loaded.");
                $this->extensions[$i][1] = 1;
                $count++;
            }
        }
        //todo in way future order of load.

        $this->plugin->debug($this->prefix."Successfully loaded ".$count." extensions.");
        return;
    }

    /**
     * @param string $name
     */
    public function enableExtension(string $name): void{
        //todo
        return;
    }

    public function enableExtensions() : void{
        $this->plugin->debug($this->prefix."Enabling Extensions...");
        $count = 0;
        for($i = 0; $i < count($this->extensions); $i++){
            if(!$this->extensions[$i][0]->onEnable()){
                $this->plugin->debug($this->prefix."Extension '".$this->extensions[$i][0]->getExtensionData()->getName()."' failed to enable.");
                $this->extensions[$i][1] = 0;
            } else {
                $this->plugin->debug($this->prefix."Extension '".$this->extensions[$i][0]->getExtensionData()->getName()."' Enabled.");
                $this->plugin->getServer()->getPluginManager()->registerEvents($this->extensions[$i][0], $this->plugin);
                $this->extensions[$i][1] = 2;
                $count++;
            }
        }
        $this->extensions = array_values($this->extensions); //reset index's
        $this->plugin->debug($this->prefix."Successfully enabled ".$count." extensions.");
        return;
    }

    public function disableExtensions() : void{
        $this->plugin->debug($this->prefix."Disabling Extensions...");
        foreach($this->extensions as $extension){
            if($extension[1] == 0) continue;
            $extension[0]->onDisable();
        }
        $this->plugin->debug($this->prefix."All extensions now disabled.");
        $this->extensions = [];
        return;
    }
}