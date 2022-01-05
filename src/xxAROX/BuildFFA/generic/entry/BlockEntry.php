<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\generic\entry;
use pocketmine\world\Position;


/**
 * Class BlockEntry
 * @package xxAROX\BuildFFA\generic\entry
 * @author Jan Sohn / xxAROX
 * @date 04. Januar, 2022 - 15:22
 * @ide PhpStorm
 * @project BuildFFA
 */
class BlockEntry{
	protected Position $position;
	protected float $timestamp;

	public function __construct(Position $position, float $timestamp){
		$this->position = $position;
		$this->timestamp = $timestamp;
	}

	/**
	 * Function getPosition
	 * @return Position
	 */
	public function getPosition(): Position{
		return $this->position;
	}

	/**
	 * Function getTimestamp
	 * @return float
	 */
	public function getTimestamp(): float{
		return $this->timestamp;
	}
}
