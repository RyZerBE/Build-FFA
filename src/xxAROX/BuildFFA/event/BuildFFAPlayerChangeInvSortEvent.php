<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\event;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class BuildFFAPlayerChangeInvSortEvent
 * @package xxAROX\BuildFFA\event
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 13:44
 * @ide PhpStorm
 * @project BuildFFA
 */
class BuildFFAPlayerChangeInvSortEvent extends Event implements Cancellable{
	/**
	 * BuildFFAPlayerChangeInvSortEvent constructor.
	 * @param xPlayer $player
	 * @param array $oldSort
	 * @param array $newSort
	 */
	public function __construct(protected xPlayer $player, protected array $oldSort, protected array $newSort){
	}

	/**
	 * Function getPlayer
	 * @return xPlayer
	 */
	public function getPlayer(): xPlayer{
		return $this->player;
	}

	/**
	 * Function getOldSort
	 * @return array
	 */
	public function getOldSort(): array{
		return $this->oldSort;
	}

	/**
	 * Function getNewSort
	 * @return array
	 */
	public function getNewSort(): array{
		return $this->newSort;
	}
}
