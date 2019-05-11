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

class ExtensionDownloadTask extends AsyncTask
{
    /** @var string */
    public $url;
    public $fileName;
    public $savePath;

    public function __construct(string $url, string $fileName, string $savePath)
    {
        $this->url = $url;
        $this->fileName = $fileName;
        $this->savePath = $savePath;
    }

    public function onRun()
    {
        $fp = fopen($this->savePath, 'w+');
        if($fp === false){
            $this->setResult(["response" => 500, "error" => "Could not open path: ".$this->savePath]);
            return;
        }

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //your internet is crap if it cant handle a couple of kb in a min.
        curl_exec($ch);

        if(curl_errno($ch)){
            $this->setResult(["response" => 500, "error" => curl_error($ch)]);
            return;
        }
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        $this->setResult(["response" => $statusCode, "error" => ""]);
    }

    public function onCompletion(Server $server)
    {
        $plugin = $server->getPluginManager()->getPlugin('KOTH');
        if(!$plugin instanceof Main){
            return;
        }
        $result = $this->getResult();
        if($result["error"] !== ""){
            $path = $this->url;
            $plugin->debug("[Download Task] : Failed to get url '${path}' message > ".$result["error"]);
        }
        $plugin->ExtensionManager->handleDownloaded($this->fileName);
    }
}