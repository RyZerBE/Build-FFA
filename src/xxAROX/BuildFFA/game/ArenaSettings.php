<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;


/**
 * Class ArenaSettings
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:48
 * @ide PhpStorm
 * @project BuildFFA
 */
class ArenaSettings implements JsonSerializable{
	public int $respawn_height = 0;
	public int $protection = 8;
	public int $blocks_cooldown = 10;

	/**
	 * ArenaSettings constructor.
	 * @param array $data
	 */
	public function __construct(array $data = []){
		$this->respawn_height = $data["respawn_height"] ?? $this->respawn_height;
		$this->protection = $data["protection"] ?? $this->protection;
		$this->blocks_cooldown = $data["blocks_cooldown"] ?? $this->blocks_cooldown;
	}

	/**
	 * Function jsonSerialize
	 * @return array
	 */
	#[ArrayShape([
		"respawnHeight"   => "int",
		"protection"      => "int",
		"blocks_cooldown" => "int",
	])] public function jsonSerialize(): array{
		return [
			"respawnHeight"   => $this->respawn_height,
			"protection"      => $this->protection,
			"blocks_cooldown" => $this->blocks_cooldown,
		];
	}
}