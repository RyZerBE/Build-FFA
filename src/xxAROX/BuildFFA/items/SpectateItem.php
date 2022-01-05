<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\items;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class SpectateItem
 * @package xxAROX\BuildFFA\items
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 20:04
 * @ide PhpStorm
 * @project BuildFFA
 */
class SpectateItem extends Item{
	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemIds::ENDER_EYE, 0), "Spectate");
		$this->setCustomName("§aSpectate"); // TODO: language stuff
	}

	/**
	 * Function onClickAir
	 * @param xPlayer $player
	 * @param Vector3 $directionVector
	 * @return ItemUseResult
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
		if (!$player->hasItemCooldown($this)) {
			$player->spectate();
			return ItemUseResult::FAIL();
		}
		$player->resetItemCooldown($this);
		return parent::onClickAir($player, $directionVector);
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
		if (!$player->hasItemCooldown($this)) {
			$player->spectate();
			return ItemUseResult::FAIL();
		}
		$player->resetItemCooldown($this);
		return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector);
	}
}
