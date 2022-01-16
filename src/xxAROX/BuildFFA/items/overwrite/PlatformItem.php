<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\items\overwrite;
use JetBrains\PhpStorm\Pure;
use pocketmine\block\Block;
use pocketmine\item\BlazeRod;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class PlatformItem
 * @package xxAROX\BuildFFA\items\overwrite
 * @author Jan Sohn / xxAROX
 * @date 07. Januar, 2022 - 16:56
 * @ide PhpStorm
 * @project BuildFFA
 */
class PlatformItem extends BlazeRod{
	/**
	 * PlatformItem constructor.
	 */
	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemIds::BLAZE_ROD, 0), "Platform");
	}

	/**
	 * Function applyCountdown
	 * @param xPlayer $player
	 * @return bool
	 */
	#[Pure] public function applyCountdown(xPlayer $player): bool{
		return !$player->isOnGround();
	}

	/**
	 * Function onClickAir
	 * @param xPlayer $player
	 * @param Vector3 $directionVector
	 * @return ItemUseResult
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
		$player->spawnPlatform();
		return ItemUseResult::FAIL();
	}

	/**
	 * Function onInteractBlock
	 * @param xPlayer $player
	 * @param Block $blockReplace
	 * @param Block $blockClicked
	 * @param int $face
	 * @param Vector3 $clickVector
	 * @return ItemUseResult
	 */
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): ItemUseResult{
		$player->spawnPlatform();
		return ItemUseResult::FAIL();
	}
}
