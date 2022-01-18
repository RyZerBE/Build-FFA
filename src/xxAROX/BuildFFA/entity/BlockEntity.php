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
use pocketmine\entity\object\FallingBlock;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\sound\PopSound;


/**
 * Class BlockEntity
 * @package xxAROX\BuildFFA\entity
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 12:26
 * @ide PhpStorm
 * @project BuildFFA
 */
class BlockEntity extends FallingBlock{
	/**
	 * BlockEntity constructor.
	 * @param Position $position
	 * @param Block $block
	 */
	public function __construct(Position $position, Block $block){
		$this->block = $block;
		$vec = $position->floor()->add(0.5, 0.5, 0.5);
		parent::__construct($position->level, self::createBaseNBT(new Location($vec->x, $vec->y, $vec->z, 0, 0, $position->level)));
	}

	/**
	 * Function setBlock
	 * @param Block $block
	 * @return void
	 */
	public function setBlock(Block $block): void{
		$this->block = $block;
	}

	/**
	 * Function entityBaseTick
	 * @param int $tickDiff
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1): bool{
		if ($this->closed) {
			return false;
		}
		$hasUpdate = Entity::entityBaseTick($tickDiff);
		if (!$this->isFlaggedForDespawn()) {
			if ($this->onGround) {
				$this->flagForDespawn();
				$this->getLevel()->addSound(new PopSound($this->getPosition()));
			}
		}
		return $hasUpdate;
	}
}
