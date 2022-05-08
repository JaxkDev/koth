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
*   Copyright (C) 2019-2020 JaxkDev
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
*   Discord :: JaxkDev#8860
*   Email   :: JaxkDev@gmail.com
*/

declare(strict_types=1);
namespace Jackthehack21\KOTH\Particles;

use Jackthehack21\KOTH\Main;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\world\particle\FloatingTextParticle;

/**
 * Class FloatingText
 *
 * This class's sole purpose is to update every time its changed.
 */

class FloatingText extends FloatingTextParticle {

    protected World $level;

    protected Vector3 $position;

    public function __construct(Main $plugin, World $level, Vector3 $position, string $text, string $title = "")
    {
        parent::__construct($text, $title);
        $this->level = $level;
        $this->position = $position;
    }

    /**
     * @param string $text
     */
    public function setText(string $text) : void{
        $this->text = $text;
        $this->update();
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title) : void{
        $this->title = $title;
        $this->update();
    }

    /**
     * @param bool $value
     */
    public function setInvisible(bool $value = true) : void{
        $this->invisible = $value;
        $this->update();
    }

    public function update() : void{
        $this->level->addParticle($this->position, $this);
    }

}