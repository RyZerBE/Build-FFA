<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use pocketmine\world\World;


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

	public function __construct(private string $worldName, private ArenaSettings $settings){
	}

	/**
	 * Function getActive
	 * @return bool
	 */
	public function isActive(): bool{
		return $this->active;
	}

	/**
	 * Function getWorldName
	 * @return string
	 */
	public function getWorldName(): string{
		return $this->worldName;
	}

	/**
	 * Function getSettings
	 * @return ArenaSettings
	 */
	public function getSettings(): ArenaSettings{
		return $this->settings;
	}
}
