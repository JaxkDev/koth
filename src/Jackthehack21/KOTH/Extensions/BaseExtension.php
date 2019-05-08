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
use pocketmine\Server;

abstract class BaseExtension implements Extension
{

    /** @var Main */
    public $plugin;

    /** @var ExtensionData */
    private $extensionData;

    public function __construct(Main $plugin, $name, $author, $version, $api, $plugin_depends = [], $ext_depends = []){
        $this->plugin = $plugin;
        $this->extensionData = new ExtensionData($this, $name, $author, $version, $api, $plugin_depends, $ext_depends);
    }

    public function getExtensionData(): ExtensionData
    {
        return $this->extensionData;
    }

    public function onLoad() : bool{
        return false;
    }

    public function onEnable() : bool{
        return false;
    }

    public function onDisable() : bool{
        return false;
    }

    public function getServer() : Server{
        return $this->plugin->getServer();
    }
}