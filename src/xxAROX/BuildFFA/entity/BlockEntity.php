<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\entity;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\world\Position;


/**
 * Class BlockEntity
 * @package xxAROX\BuildFFA\entity
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 12:26
 * @ide PhpStorm
 * @project BuildFFA
 */
class BlockEntity extends FallingBlock{
	public function __construct(Position $position, Block $block){
		$vec = $position->floor()->add(0.5, 0.5, 0.5);
		parent::__construct(new Location($vec->x, $vec->y, $vec->z, $position->world, 0, 0), $block);
	}

	public function getBlock(): Block{
		return $this->block;
	}

	/**
	 * Function setBlock
	 * @param Block $block
	 * @return void
	 */
	public function setBlock(Block $block): void{
		$this->block = $block;
	}

	protected function entityBaseTick(int $tickDiff = 1): bool{
		if ($this->closed) {
			return false;
		}
		$hasUpdate = Entity::entityBaseTick($tickDiff);
		if (!$this->isFlaggedForDespawn()) {
			if ($this->onGround) {
				$this->flagForDespawn();
			}
		}
		return $hasUpdate;
	}
}
