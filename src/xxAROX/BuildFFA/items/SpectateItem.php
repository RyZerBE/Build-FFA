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
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;
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
	/**
	 * SpectateItem constructor.
	 */
	public function __construct(){
		parent::__construct(ItemIds::ENDER_EYE, 0, "Spectate");
		$this->setCustomName("§aSpectate"); // TODO: language stuff
		applyReadonlyTag($this);
	}

	/**
	 * Function getCooldownTicks
	 * @return int
	 */
	public function getCooldownTicks(): int{
		return 20;
	}

	/**
	 * Function onClickAir
	 * @param xPlayer $player
	 * @param Vector3 $directionVector
	 * @return bool
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): bool{
		if (!$player->hasItemCooldown($this)) {
			$player->spectate();
			return false;
		}
		$player->resetItemCooldown($this);
		return parent::onClickAir($player, $directionVector);
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
		if (!$player->hasItemCooldown($this)) {
			$player->spectate();
			return false;
		}
		$player->resetItemCooldown($this);
		return parent::onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
	}
}
