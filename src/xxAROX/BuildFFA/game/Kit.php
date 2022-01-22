<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use JetBrains\PhpStorm\Pure;
use pocketmine\block\BlockIds;
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
     * @param null|Armor $head
     * @param null|Armor $chest
     * @param null|Armor $leg
     * @param null|Armor $feet
     * @param bool $fillUpAfterDeath
     */
	public function __construct(protected string $display_name, protected array $contents, protected ?Armor $head, protected ?Armor $chest, protected ?Armor $leg, protected ?Armor $feet, protected $fillUpAfterDeath = false){
		foreach ($this->contents as $type => $item) {
			if ($item instanceof PlaceHolderItem) {
				$nbt = $item->getPlaceholdersItem()->getNamedTag();
				$nbt->setString(BuildFFA::TAG_SORT_TYPE, $type);
				$item->getPlaceholdersItem()->setNamedTag($nbt);
			}
			$nbt = $item->getNamedTag();
			$nbt->setString(BuildFFA::TAG_SORT_TYPE, $type);
			$item->setNamedTag($nbt);
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
		for ($slot = 9; $slot < $player->getInventory()->getSize(); $slot++) {
			$player->getInventory()->setItem($slot, applyReadonlyTag(Item::get(-161)->setCustomName("§r")));
		}
		for ($slot = 0; $slot < $player->getCraftingGrid()->getSize(); $slot++) {
			$player->getCraftingGrid()->setItem($slot, applyReadonlyTag(Item::get(-161)->setCustomName("§r")));
		}
		$checked = [];
		foreach ($this->contents as $type => $item) {
			$slot = ($invSort[$type] ?? $type);
			if (isset($checked[$slot])) {
				unset($invSort[$type]);
			} else if ($type == $slot) {
				$checked[] = $type;
				$invSort[$type] = array_flip($checked)[$type];
			} else {
				$checked[$slot] = $type;
			}
		}
		unset($checked, $slot);
		foreach ($this->contents as $type => $item) {
			if (isset($invSort[$type])) {
			    if($player->getInventory()->getItem($invSort[$type])->getId() === BlockIds::AIR) {
                    $player->getInventory()->setItem($invSort[$type], $item instanceof PlaceHolderItem
                        ? $item->getPlaceholdersItem() : $item);
                }else {
                    $player->getInventory()->addItem($item instanceof PlaceHolderItem ? $item->getPlaceholdersItem()
                        : $item);
                }
			} else {
				$player->getInventory()->addItem($item instanceof PlaceHolderItem ? $item->getPlaceholdersItem()
					: $item);
			}
		}
		$player->getArmorInventory()->setHelmet($this->head ?? ItemFactory::get(0));
		$player->getArmorInventory()->setChestplate($this->chest ?? ItemFactory::get(0));
		$player->getArmorInventory()->setLeggings($this->leg ?? ItemFactory::get(0));
		$player->getArmorInventory()->setBoots($this->feet ?? ItemFactory::get(0));
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

    /**
     * @return bool
     */
    public function fillUpAfterDeath(): bool{
        return $this->fillUpAfterDeath;
    }

    public function onFillUp(xPlayer $player): void{
        if(!$this->fillUpAfterDeath) return;

        $this->equip($player);
    }
}
