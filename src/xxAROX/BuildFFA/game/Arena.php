<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use pocketmine\Server;
use pocketmine\world\World;
use xxAROX\BuildFFA\event\MapChangeEvent;


/**
 * Class Arena
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:47
 * @ide PhpStorm
 * @project BuildFFA
 */
class Arena{
	private bool $active = false;

	public function __construct(private World $world, private ArenaSettings $settings){
	}

	/**
	 * Function getActive
	 * @return bool
	 */
	public function isActive(): bool{
		return $this->active;
	}

	/**
	 * Function getWorld
	 * @return World
	 */
	public function getWorld(): World{
		return $this->world;
	}

	/**
	 * Function getSettings
	 * @return ArenaSettings
	 */
	public function getSettings(): ArenaSettings{
		return $this->settings;
	}

	/**
	 * Function setActive
	 * @param bool $active
	 * @return void
	 */
	public function setActive(bool $active): void{
		$this->active = $active;

		$ev = new MapChangeEvent();
		$ev->call();

		if ($this->active) {
			foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
				$onlinePlayer->teleport($this->world->getSafeSpawn());
			}
		}
	}
}
