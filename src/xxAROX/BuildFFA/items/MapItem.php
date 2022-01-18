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
 * Class MapItem
 * @package xxAROX\BuildFFA\items
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 19:58
 * @ide PhpStorm
 * @project BuildFFA
 */
class MapItem extends Item{
	/**
	 * MapItem constructor.
	 */
	public function __construct(){
		parent::__construct(ItemIds::MAP, 0, "Choose map");
		$this->setCustomName("§eMap");//TODO: language	stuff
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
			$player->sendMapSelect();
			$player->resetItemCooldown($this);
			return false;
		}
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
			$player->sendMapSelect();
			$player->resetItemCooldown($this);
			return false;
		}
		return parent::onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
	}
}
