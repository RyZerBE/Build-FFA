<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\items;
use pocketmine\block\Block;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;


/**
 * Trait NonPlaceableItemTrait
 * @package xxAROX\BuildFFA\items
 * @author Jan Sohn / xxAROX
 * @date 06. Januar, 2022 - 21:31
 * @ide PhpStorm
 * @project BuildFFA
 */
trait NonPlaceableItemTrait{
	/**
	 * Function onInteractBlock
	 * @param Player $player
	 * @param Block $blockReplace
	 * @param Block $blockClicked
	 * @param int $face
	 * @param Vector3 $clickVector
	 * @return ItemUseResult
	 */
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): ItemUseResult{
		return ItemUseResult::FAIL();
	}
}
