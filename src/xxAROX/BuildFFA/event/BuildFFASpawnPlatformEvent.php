<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\event;
use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\item\Item;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class BuildFFASpawnPlatformEvent
 * @package xxAROX\BuildFFA\event
 * @author Jan Sohn / xxAROX
 * @date 16. Januar, 2022 - 20:52
 * @ide PhpStorm
 * @project BuildFFA
 */
class BuildFFASpawnPlatformEvent extends Event implements Cancellable{
	/**
	 * BuildFFASpawnPlatformEvent constructor.
	 * @param xPlayer $player
	 * @param Item $item
	 * @param array $affectedBlocks
	 * @param Block $block
	 */
	public function __construct(protected xPlayer $player, protected Item $item, protected array $affectedBlocks, protected Block $block){
	}

	/**
	 * Function getPlayer
	 * @return xPlayer
	 */
	public function getPlayer(): xPlayer{
		return $this->player;
	}

	/**
	 * Function getItem
	 * @return Item
	 */
	public function getItem(): Item{
		return $this->item;
	}

	/**
	 * Function getAffectedBlocks
	 * @return Block[]
	 */
	public function getAffectedBlocks(): array{
		return $this->affectedBlocks;
	}

	/**
	 * Function setAffectedBlocks
	 * @param Block[] $affectedBlocks
	 * @return void
	 */
	public function setAffectedBlocks(array $affectedBlocks): void{
		$this->affectedBlocks = $affectedBlocks;
	}

	/**
	 * Function getBlock
	 * @return Block
	 */
	public function getBlock(): Block{
		return $this->block;
	}

	/**
	 * Function setBlockIdentifier
	 * @param Block $block
	 * @return void
	 */
	public function setBlock(Block $block): void{
		$this->block = $block;
	}
}
