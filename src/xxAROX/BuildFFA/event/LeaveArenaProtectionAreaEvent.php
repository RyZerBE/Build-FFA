<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\event;
use pocketmine\event\Event;
use xxAROX\BuildFFA\game\Arena;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class LeaveArenaProtectionAreaEvent
 * @package xxAROX\BuildFFA\event
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 18:36
 * @ide PhpStorm
 * @project BuildFFA
 */
class LeaveArenaProtectionAreaEvent extends Event{
	/**
	 * LeaveArenaProtectionAreaEvent constructor.
	 * @param xPlayer $player
	 * @param Arena $arena
	 * @param bool $teleported
	 */
	public function __construct(protected xPlayer $player, protected Arena $arena, protected bool $teleported = false){
	}

	/**
	 * Function getPlayer
	 * @return xPlayer
	 */
	public function getPlayer(): xPlayer{
		return $this->player;
	}

	/**
	 * Function getArena
	 * @return Arena
	 */
	public function getArena(): Arena{
		return $this->arena;
	}

	/**
	 * Function getTeleported
	 * @return bool
	 */
	public function isTeleported(): bool{
		return $this->teleported;
	}
}
