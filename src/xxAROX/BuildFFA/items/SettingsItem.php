<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\items;
use Frago9876543210\EasyForms\elements\FunctionalButton;
use Frago9876543210\EasyForms\forms\MenuForm;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use xxAROX\BuildFFA\game\Game;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class SettingsItem
 * @package xxAROX\BuildFFA\items
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 21:33
 * @ide PhpStorm
 * @project BuildFFA
 */
class SettingsItem extends Item{
	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemIds::COMMAND_BLOCK, 0), "BuildFFA Settings");
		$this->setCustomName("§dSettings");//TODO: language stuff
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
	 * @return ItemUseResult
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
		if (!$player->hasItemCooldown($this)) {
			$this->sendForm($player);
			$player->resetItemCooldown($this);
			return ItemUseResult::FAIL();
		}
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
			$this->sendForm($player);
			$player->resetItemCooldown($this);
			return ItemUseResult::FAIL();
		}
		return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector);
	}

	/**
	 * Function sendForm
	 * @param xPlayer $player
	 * @return void
	 */
	private function sendForm(xPlayer $player): void{
		$settings = $player->hasPermission("game.buildffa.settings");
		$setup = $player->hasPermission("game.setup");
		if (!$settings && !$setup) {
		} else if ($settings && !$setup) {
			$player->sendBuildFFASettingsForm();
		} else if (!$settings && $setup) {
			Game::getInstance()->setup($player);
		} else {
			$player->sendForm(new MenuForm("BuildFFA", "", [new FunctionalButton("Settings", fn(xPlayer $player) => $player->sendBuildFFASettingsForm()),new FunctionalButton("Setup", fn(xPlayer $player) => Game::getInstance()->setup($player))]));
		}
	}
}
