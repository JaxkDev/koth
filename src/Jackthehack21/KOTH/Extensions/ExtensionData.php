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


class ExtensionData
{

    /** @var BaseExtension */
    public $extension;

    /** @var string */
    private $api;
    private $name;
    private $author;
    private $version;

    /** @var array */
    private $plugin_depends;
    private $ext_depends;

    /**
     * ExtensionData constructor.
     * @param BaseExtension $extension
     * @param $name
     * @param $author
     * @param $version
     * @param $api
     * @param array $plugin_depends
     * @param array $ext_depends
     */
    public function __construct(BaseExtension $extension, $name, $author, $version, $api, $plugin_depends = [], $ext_depends = []){
        $this->extension = $extension;
        $this->api = $api;
        $this->name = $name;
        $this->author = $author;
        $this->version = $version; //todo add class for version to make it easier to compare etc. (same with API)
        $this->plugin_depends = $plugin_depends;
        $this->ext_depends = $ext_depends;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAuthor() : string{
        return $this->author;
    }

    /**
     * @return string
     */
    public function getAPI() : string{
        return $this->api;
    }

    /**
     * @return string
     */
    public function getVersion() : string{
        return $this->version;
    }

    public function getDependencies() : array{
        return [$this->plugin_depends, $this->ext_depends];
    }
}