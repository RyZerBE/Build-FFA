<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\event;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use xxAROX\BuildFFA\game\Arena;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class BuildFFAPlayerRespawnEvent
 * @package xxAROX\BuildFFA\event
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 13:42
 * @ide PhpStorm
 * @project BuildFFA
 */
class BuildFFAPlayerRespawnEvent extends Event implements Cancellable{
	use CancellableTrait;


	/**
	 * BuildFFAPlayerRespawnEvent constructor.
	 * @param xPlayer $player
	 * @param Arena $arena
	 */
	public function __construct(protected xPlayer $player, protected Arena $arena){
	}

	/**
	 * Function getArena
	 * @return Arena
	 */
	public function getArena(): Arena{
		return $this->arena;
	}

	/**
	 * Function getPlayer
	 * @return xPlayer
	 */
	public function getPlayer(): xPlayer{
		return $this->player;
	}
}
