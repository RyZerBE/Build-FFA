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
	public int|float $respawn_height = 0;
	public int|float $protection = 8;
	public int|float $blocks_cooldown = 5;
	public bool $enable_fall_damage = true;

	/**
	 * ArenaSettings constructor.
	 * @param array $data
	 */
	public function __construct(array $data = []){
		$this->respawn_height = /*$data["respawn_height"] pm3 bug*/0 ?? $this->respawn_height;
		$this->protection = ($data["protection"] ?? $this->protection) + 1.5; //NO-CONFUSE: plus 1.5, because xPlayer::from_spawn maths.
		$this->blocks_cooldown = $data["blocks_cooldown"] ?? $this->blocks_cooldown;
		$this->enable_fall_damage = $data["enable_fall_damage"] ?? $this->enable_fall_damage;
	}

	/**
	 * Function jsonSerialize
	 * @return array
	 */
	#[ArrayShape([
		"respawn_height"     => "int|float",
		"protection"         => "int|float",
		"blocks_cooldown"    => "int|float",
		"enable_fall_damage" => "bool",
	])] public function jsonSerialize(): array{
		return [
			"respawn_height"     => $this->respawn_height,
			"protection"         => $this->protection,
			"blocks_cooldown"    => $this->blocks_cooldown,
			"enable_fall_damage" => $this->enable_fall_damage,
		];
	}
}
