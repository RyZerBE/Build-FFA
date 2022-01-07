<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use JetBrains\PhpStorm\Pure;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\items\PlaceHolderItem;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class Kit
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:54
 * @ide PhpStorm
 * @project BuildFFA
 */
class Kit{
	/**
	 * Kit constructor.
	 * @param string $display_name
	 * @param Item[] $contents
	 * @param Item $offhand
	 * @param null|Armor $head
	 * @param null|Armor $chest
	 * @param null|Armor $leg
	 * @param null|Armor $feet
	 */
	public function __construct(protected string $display_name, protected array $contents, protected Item $offhand, protected ?Armor $head, protected ?Armor $chest, protected ?Armor $leg, protected ?Armor $feet){
		foreach ($this->contents as $type => $item) {
			if ($item instanceof PlaceHolderItem) {
				$item->getPlaceholdersItem()->getNamedTag()->setString(BuildFFA::TAG_SORT_TYPE, $type);
			}
			$item->setNamedTag($item->getNamedTag()->setString(BuildFFA::TAG_SORT_TYPE, $type));
		}
	}

	/**
	 * Function getPlaceholderByIdentifier
	 * @param string $identifier
	 * @return null|PlaceHolderItem
	 */
	#[Pure] public function getPlaceholderByIdentifier(string $identifier): ?PlaceHolderItem{
		foreach ($this->contents as $type => $item) {
			if ($item instanceof PlaceHolderItem && $item->getPlaceholderIdentifier() === $identifier) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * Function equip
	 * @param xPlayer $player
	 * @return void
	 */
	public function equip(xPlayer $player): void{
		$invSort = $player->getInvSort();
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getOffHandInventory()->clearAll();
		for ($slot=9; $slot<$player->getInventory()->getSize(); $slot++) {
			$player->getInventory()->setItem($slot, applyReadonlyTag(VanillaBlocks::BARRIER()->asItem()->setCustomName("§r")));
		}
		for ($slot=0; $slot<$player->getCraftingGrid()->getSize(); $slot++) {
			$player->getCraftingGrid()->setItem($slot, applyReadonlyTag(VanillaBlocks::BARRIER()->asItem()->setCustomName("§r")));
		}
		foreach ($this->contents as $type => $item) {
			if ($item instanceof PlaceHolderItem) {
				if (isset($invSort[$type])) {
					$player->getInventory()->setItem($invSort[$type], $item->getPlaceholdersItem());
				} else {
					$player->getInventory()->addItem($item);
				}
			}
			if (isset($invSort[$type])) {
				$player->getInventory()->setItem($invSort[$type], /*clone/*should 'all player same item-name' fix*/ $item);
			} else {
				$player->getInventory()->addItem(/*clone/*should 'all player same item-name' fix*/ $item);
			}
		}
		$player->getOffHandInventory()->setItem(0, $this->offhand);
		$player->getArmorInventory()->setHelmet($this->head ?? ItemFactory::air());
		$player->getArmorInventory()->setChestplate($this->chest ?? ItemFactory::air());
		$player->getArmorInventory()->setLeggings($this->leg ?? ItemFactory::air());
		$player->getArmorInventory()->setBoots($this->feet ?? ItemFactory::air());
	}

	/**
	 * Function getDisplayName
	 * @return string
	 */
	public function getDisplayName(): string{
		return $this->display_name;
	}

	/**
	 * Function getContents
	 * @return array
	 */
	public function getContents(): array{
		return $this->contents;
	}

	/**
	 * Function getOffhand
	 * @return Item
	 */
	public function getOffhand(): Item{
		return $this->offhand;
	}

	/**
	 * Function getHead
	 * @return ?Armor
	 */
	public function getHead(): ?Armor{
		return $this->head;
	}

	/**
	 * Function getChest
	 * @return ?Armor
	 */
	public function getChest(): ?Armor{
		return $this->chest;
	}

	/**
	 * Function getLeg
	 * @return ?Armor
	 */
	public function getLeg(): ?Armor{
		return $this->leg;
	}

	/**
	 * Function getFeet
	 * @return ?Armor
	 */
	public function getFeet(): ?Armor{
		return $this->feet;
	}
}
