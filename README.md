[![HitCount](http://hits.dwyl.io/JaxkDev/KOTH.svg)](http://hits.dwyl.io/JaxkDev/KOTH)
<a href="https://tiny.cc/JaxksDC"><img src="https://discordapp.com/api/guilds/554059221847638040/embed.png" alt="Discord server"/></a>
<a href="https://poggit.pmmp.io/p/KOTH"><img src="https://poggit.pmmp.io/shield.state/KOTH"></a>

# K.O.T.H
King Of The Hill mini-game, Pocketmine-MP Plugin by JaxkDev.

## NOTICE'S
 **This plugin is in BETA (Pre-Release), so *please* don't spam out comments/issues with broken things or things that you expect there and are not.**
 
 *Just because it's the only plugin released, doesn't mean it's the best one.*
 
 *Please feel free to create a PR to modify/add new help files for different languages !*
 
 
## Features
### Implemented
 - Console has its own commands.
 - Enable/Disable arena's !
 - Much more events for other developers to be able to customise the game EVEN MORE !
 - Automatically install updates on server start ! - 
 - Check for updates on server start !             - Both won't work on android (CURL)
 - Messages are customisable.
 - Multiple Arena's.
 - Custom spawn & hill points.
 - Supports Arena's in different worlds.
 - Custom player amount, and game time.
 - Block break/place disabled during in-game (optional).
 - Changing gamemode during a game is cancelled (optional).
 - Rewards given to king at end of game.
 - Leave a arena.
 - 'King' is displayed above player name. (nametag, optional)
 - Some values are in config.yml including a help file.
 - On death keep inventory.

### Upcoming
 - Change of game layout (different types will be extensions) - Beta5/6 (check the extensions [repo](https://github.com/JaxkDev/Koth-Extensions) for more info)
 - Something extra for when winner is announced *wink*    - Beta5

## About the game
### KOTH
King.Of.The.Hill

King of the hill is a mini-game most players will be familiar with, either through minecraft or by playing pretty much any other strategy game. 

The aim of king of the hill is to conquer the castle, fort or anything else in the middle of the arena, the arena is usually a large map of some sort with surrounding walls.
The top point usually on a hill with a height advantage is the throne, castle, fort whatever you call it.

players all spawn into the one map/arena, and then using the equipment/items on them, they take on position of king.
They remain king for as long as they stay in the specified area/fort/castle if they die, another player in the area takes place as king or next person who goes there.

The winner is announced at the end of the game (When a timer runs out), the king currently in power or the previous king will be winner.

At the end the winner is given rewards and a *celebration* occurs (shortly).
(This is not yet implemented - as of beta4)

### Joining a game
To join a game/arena the arena must meet some criteria,
1. The arena cannot be full or not ready (you can check using `/koth list`)
2. The arena world must exist, cannot be deleted or re-named (if so you will have to reset the arena positions)
3. The arena must have spawn points.

If the arena meets all the above you can join using `/koth join <arena name>`

### Starting a game
The game will start the pregame counter when the minimum amount of players has joined.
or if auto-start is disabled in config you can type `/koth forcestart` or `/koth start`
the only difference is forcestart does not check the arena status whereas ^^^ only starts if status is `Ready`

### Leaving a game
You can leave a game by using the command `/koth leave` or quiting the game.

### Winning a game
**NOTICE: There is plans for different types of ways of winning a game, targeted for beta 4/5**

The game runs on a timer, during which anyone can move, attack and kill the king.
However, when the timer runs out the last standing king, or the previous king will be crowned.
(And in future rewards and *other* things will be added)

(Notice, players are frozen during 'presentations of the king')

## Setting up
### Creating/Removing arena(s)
To create a new arena use the command `/koth new <arena name>` (No spaces are allowed in arena, for now)
To remove an arena use the command `/koth rem <arena name>` (The arena cannot be in use when deleting)

To check arena status type `/koth list` or `/koth info <arena name>`
### Setting positions/Values
 1. `/koth setpos1 <arena name>`
   (Make sure you are standing on one corner of the throne/hill.)
 2. `/koth setpos2 <arena name>`
   (Now stand on the opposite corner of the throne/hill.)
 3. `/koth setspawn <arena name>`
   (This command can be used as many times as you like, again be standing on the spawn location you want to set.)
 4. `/koth addreward <arena name> <command>`
   example: `/koth addreward arena1 give {PLAYER} 20 1` <- this would give the winner 1 web.
### Editing Values
If your provider is yaml feel free to edit the data file but any damaged caused is directly your fault.

You can use commands to modify some but not all data (as of beta3) check /koth help
- `/koth setpos1/2` (Can be re-used to overwrite old positions)
- `/koth setspawn` (Can be used multiple times, `/koth remspawn` is in Beta4)

### Config:
When the plugin is first run it will make a file in plugin_data/koth/ named config.yml
in the file you will find a series of values feel free to change any of them except version

For more info see the help file found in the same directory.

## Extensions:
 - <https://github.com/JaxkDev/koth-extensions>
 To install extensions place the phar from the releases section into the plugins directory of your server and reboot server.
 Any issues should be reported in that repository if the extension crashes.

## Known Bugs:
 - Floating Text Particles will appear in every world but at same position, disable floating_text_particles if this is an issue for your server setup.

To report bugs please make an issue over on [GitHub](https://github.com/JaxkDev/koth/issues/new) and please *follow the template!*

## Credits:
_Developer:_ JaxkDev

_Icon Creator:_ WowAssasin (WowAssasin#6608)

_Requested Originally By:_ GOLDVAGE (GOLDVAGE#2712) (My Idea generator, hehe)


### License:
    KOTH - King of the hill
    Copyright (C) 2019 JaxkDev
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
    
    Twitter :: @JaxkDev
    Discord :: Jackthehaxk21#8860
    Email   :: JaxkDev@gmail.com
