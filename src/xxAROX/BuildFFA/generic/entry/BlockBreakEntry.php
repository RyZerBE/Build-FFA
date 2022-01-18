<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\generic\entry;
use JetBrains\PhpStorm\Pure;
use pocketmine\block\Block;
use pocketmine\level\Position;


/**
 * Class BlockBreakEntry
 * @package xxAROX\BuildFFA\generic\entry
 * @author Jan Sohn / xxAROX
 * @date 04. Januar, 2022 - 15:24
 * @ide PhpStorm
 * @project BuildFFA
 */
class BlockBreakEntry extends BlockEntry{
	/**
	 * BlockBreakEntry constructor.
	 * @param Block $legacy
	 * @param Position $position
	 * @param float $timestamp
	 */
	#[Pure] public function __construct(protected Block $legacy, Position $position, float $timestamp){
		parent::__construct($position, $timestamp);
	}

	/**
	 * Function getLegacy
	 * @return Block
	 */
	public function getLegacy(): Block{
		return $this->legacy;
	}
}
