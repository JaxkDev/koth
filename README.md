[![HitCount](http://hits.dwyl.io/Jackthehack21/KOTH.svg)](http://hits.dwyl.io/Jackthehack21/KOTH)
<a href="https://tiny.cc/JaxksDC"><img src="https://discordapp.com/api/guilds/554059221847638040/embed.png" alt="Discord server"/></a>
<a href="https://poggit.pmmp.io/p/KOTH"><img src="https://poggit.pmmp.io/shield.state/KOTH"></a>

# K.O.T.H
King Of The Hill mini-game, Pocketmine-MP Plugin by Jackthehack21.

## NOTICE'S
 **This plugin is in BETA (Pre-Release), so *please* dont spam out comments/issues with broken things or things that you expect there and are not.**
 
 *Just because its the only plugin released, doesnt mean its the best one.*
 
 
## Features
### Implemented
 - Check for updates on server start !
 - Messages are customisable.
 - Multiple Arena's.
 - Custom spawn & hill points.
 - Supports Arena's in different worlds.
 - Custom player amount, and game time.
 - Block break/place disabled during in-game.
 - Changing gamemode during a game is cancelled.
 - Rewards given to king at end of game.
 - Leave a arena.
 - 'King' is displayed above player name. (nametag)
 - Some values are in config.yml including a help file.
 - On death keep inventory. (todo config)

### Upcoming
 - Change of game rules.
 - option to download update (but not install, too many security issues there)
 - Much more events for other developers to be able to customise the game EVEN MORE !
 - HouseCleaning and a load of testing to release v1.0.0 :)
 - Default arena values to be in config.
 - Something extra for when winner is announced ***wink***

## About the game
### KOTH
King.Of.The.Hill

King of the hill is a mini game most players will be familiar with, either through minecraft or by playing pretty much any other strategy game. 

The aim of king of the hill is to conquer the castle, fort or anything else in the middle of the arena, the arena is usually a large map of some sort with surrounding walls.
The top point usually on a hill with a height advantage is the throne, castle, fort whatever you call it.

players all spawn into the one map/arena, and then using the equipment/items on them they take on position of king.
They remain king for as long as they stay in the specified area/fort/castle if they die, another player in the area takes place as king or next person who goes there.

The winner is announced at the end of the game (When a timer runs out), the king currently in power or the previous king will be winner.

At the end the winner is given rewards and a *celebration* occurs (shortly).
(This is not yet implemented - as of beta1)

### Joining a game
To join a game/arena the arena must meet some criteria,
1. The arena cannot be full or not ready (you can check using `/koth list`)
2. The arena world must exist, cannot be deleted or re-named (if so you will have to delete and re-create it)
3. The arena must have spawn points.

If the arena meets all the above you can join using `/koth join <arena name>`

### Starting a game
The game will start the pregame - counter when the minimum amount of players has joined.

(In future you can force start)

### Leaving a game
As of Beta2 you can only leave by Leaving the server
or using `/koth leave`

### Winning a game
**NOTICE: THIS IS PLANNED TO CHANGE IN BETA4/5**

The game runs on a timer, during which anyone can move, attack and kill the king.
However when the timer runs out the last standing king, or the previous king will be crowned.
(And in future rewards and *other* things will be added)

(Notice, players are frozen during 'presentations of the king')

## Setting up
### Creating/Removing arena(s)
To create a new arena use the command `/koth new <arena name>` (No spaces are allowed in arena, for now)
To remove a arena use the command `/koth rem <arena name>` (The arena cannot be in use when deleting)

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

### Config Options
plugin_enabled - Disable/enable the plugin.

debug - Show debug messages.

check_updates - Not yet used.

language - Choose one ["eng"] for the correct help file.

KingTextParticles - Displays KingText in middle of king area.

nametag_enabled - Enable/disable nametag feature.

nametag_format - NameTag format, you can use any colour code. see help file for more info.

start_countdown - How long until game starts when the minimum amount of players are in-game.

__For more info see the help file in plugin_data/KOTH/help_eng.txt__

## Extensions:
 - <https://github.com/jackthehack21/koth-extensions>

## Known Bugs:
 - Floating Text Particles will appear in every world but at same position.

To report bugs please make a issue over on [github](https://github.com/jackthehack21/koth/issues/new) and please follow the guidelines.

## Credits:
_Developer:_ Jackthehack21 (aka JaxkDev)

_Icon Creator:_ WowAssasin (WowAssasin#6608)

_Idea generator:_ GOLDVAGE (GOLDVAGE#2712)


### License:
    KOTH - King of the hill
    Copyright (C) 2019 Jackthehack21 (Jack Honour/Jackthehaxk21/JaxkDev)
    
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
    Email   :: gangnam253@gmail.com
