<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA;
use xxAROX\BuildFFA\event\BuildFFAPlayerChangeInvSortEvent;
use xxAROX\BuildFFA\event\BuildFFAPlayerRespawnEvent;
use xxAROX\BuildFFA\event\BuildFFAPlayerSpectatorEvent;
use xxAROX\BuildFFA\event\EnterArenaProtectionAreaEvent;
use xxAROX\BuildFFA\event\LeaveArenaProtectionAreaEvent;
use xxAROX\BuildFFA\event\MapChangeEvent;


/**
 * Class Listener
 * @package xxAROX\BuildFFA
 * @author Jan Sohn / xxAROX
 * @date 04. Januar, 2022 - 15:58
 * @ide PhpStorm
 * @project BuildFFA
 */
class Listener implements \pocketmine\event\Listener{
	/**
	 * Function BuildFFAPlayerChangeInvSortEvent
	 * @param BuildFFAPlayerChangeInvSortEvent $event
	 * @return void
	 */
	public function BuildFFAPlayerChangeInvSortEvent(BuildFFAPlayerChangeInvSortEvent $event): void{
	}

	/**
	 * Function BuildFFAPlayerRespawnEvent
	 * @param BuildFFAPlayerRespawnEvent $event
	 * @return void
	 */
	public function BuildFFAPlayerRespawnEvent(BuildFFAPlayerRespawnEvent $event): void{
	}

	/**
	 * Function MapChangeEvent
	 * @param MapChangeEvent $event
	 * @return void
	 */
	public function MapChangeEvent(MapChangeEvent $event): void{
	}

	/**
	 * Function EnterArenaProtectionAreaEvent
	 * @param EnterArenaProtectionAreaEvent $event
	 * @return void
	 */
	public function EnterArenaProtectionAreaEvent(EnterArenaProtectionAreaEvent $event): void{
	}

	/**
	 * Function LeaveArenaProtectionAreaEvent
	 * @param LeaveArenaProtectionAreaEvent $event
	 * @return void
	 */
	public function LeaveArenaProtectionAreaEvent(LeaveArenaProtectionAreaEvent $event): void{
	}

	/**
	 * Function BuildFFAPlayerSpectatorEvent
	 * @param BuildFFAPlayerSpectatorEvent $event
	 * @return void
	 */
	public function BuildFFAPlayerSpectatorEvent(BuildFFAPlayerSpectatorEvent $event): void{
	}
}
