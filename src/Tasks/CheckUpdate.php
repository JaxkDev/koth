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

namespace JaxkDev\KOTH\Tasks;

use Error;
use JaxkDev\KOTH\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CheckUpdate extends AsyncTask{

    private string $version;

    public function __construct(string $version){
        $this->version = $version;
    }

    public function onRun(): void{
        $ch = curl_init("https://poggit.pmmp.io/plugins.min.json?name=KOTH");
        if($ch === false){
            throw new Error("Failed to initialize cURL");
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //give it 30 secs.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        if(curl_errno($ch)){
            throw new Error(curl_error($ch));
        }
        if($data === false){
            throw new Error("Failed to get data from poggit.");
        }
        $data = json_decode((string)$data, true);
        if($data === false){
            throw new Error("Failed to decode data from poggit.");
        }
        curl_close($ch);
        $this->setResult($data);
    }

    public function onCompletion(): void{
        /** @var string[] $data */
        $data = $this->getResult();
        if(Utils::compareVersions($this->version, $data["version"])){
            Server::getInstance()->getLogger()->notice("A new version of KOTH is available on poggit! (Version: {$data["version"]})");
        }
    }
}
