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
use pocketmine\math\Vector3;
use pocketmine\Player;
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
	 * @return void
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): bool{
		$player->spawnPlatform();
		return false;
	}

	/**
	 * Function onActivate
	 * @param xPlayer $player
	 * @param Block $blockReplace
	 * @param Block $blockClicked
	 * @param int $face
	 * @param Vector3 $clickVector
	 * @return bool
	 */
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool{
		$player->spawnPlatform();
		return false;
	}
}
