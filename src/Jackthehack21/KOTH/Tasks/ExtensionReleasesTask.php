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
namespace Jackthehack21\KOTH\Tasks;

use Jackthehack21\KOTH\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class ExtensionReleasesTask extends AsyncTask
{
    /** @var string */
    public $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function onRun()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $curlerror = curl_error($ch);
        curl_close($ch);
        $result = [];
        if($curlerror !== ""){
            $result["error"] = $curlerror;
        } else {
            $result["error"] = "";
        }
        $result["response"] = $response;
        $this->setResult($result);
    }

    public function onCompletion(Server $server)
    {
        $plugin = $server->getPluginManager()->getPlugin('KOTH');
        if(!$plugin instanceof Main){
            return;
        }
        $result = $this->getResult();
        if($result["error"] !== ""){
            $plugin->debug("[Releases Task] : ERROR > ${$result["error"]}");
            return;
        }
        $data = $result["response"];
        $data = preg_replace('!^[ \t]*/\*.*?\*/[ \t]*[\r\n]!s', '', $data);
        $data = preg_replace('![ \t]*//.*[ \t]*[\r\n]!', '', $data); //bit of housekeeping :)
        $data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $data);
        $dataJ = json_decode($data, true);
        if($dataJ === null){
            $plugin->debug("[Releases Task] : Error > Invalid JSON received: ".$data);
            return;
        }
        $plugin->debug("[Releases Task] : Success, received the following data ${data}");
        $plugin->ExtensionManager->updateExtensionReleases($dataJ);
    }
}