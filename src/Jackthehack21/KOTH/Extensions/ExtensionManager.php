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

class ExtensionManager
{
    /** @var Main */
    private $plugin;

    public $prefix = "[Extensions] : ";

    /** @var BaseExtension[] */
    private $extensions = [];

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return bool
     */
    public function loadExtensions() : bool{
        $this->plugin->debug($this->prefix."Loading Extensions...");
        //todo filter through files in dir and verify them.
        //todo call load on all extensions.
        //todo in way future order of load.
        $this->plugin->debug($this->prefix."Successfully loaded the following extensions: TODO, list of extensions that were loaded successfully.");
        return true;
    }

    /**
     * @return bool
     */
    public function enableExtensions() : bool{
        $this->plugin->debug($this->prefix."Enabling Extensions...");
        //todo call enable on all extensions.
        //todo register events etc.
        $this->plugin->debug($this->prefix."Extensions now enabled: TODO LIST");
        return true;
    }

    /**
     * @return bool
     */
    public function disableExtensions() : bool{
        $this->plugin->debug($this->prefix."Disabling Extensions...");
        //todo call disable on all extensions.
        $this->plugin->debug($this->prefix."All extensions now disabled.");
        return true;
    }
}