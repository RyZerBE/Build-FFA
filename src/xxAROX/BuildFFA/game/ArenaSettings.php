<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use JetBrains\PhpStorm\ArrayShape;


/**
 * Class ArenaSettings
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:48
 * @ide PhpStorm
 * @project BuildFFA
 */
class ArenaSettings implements \JsonSerializable{
	/**
	 * ArenaSettings constructor.
	 * @param string $worldName
	 * @param int $respawnHeight
	 */
	public function __construct(public string $worldName, public int $respawnHeight = 0){
	}

	/**
	 * Function jsonSerialize
	 * @return array
	 */
	#[ArrayShape(["world" => "string", "respawnHeight" => "int"])] public function jsonSerialize(){
		return [
			"world"         => $this->worldName,
			"respawnHeight" => $this->respawnHeight,
		];
	}
}
