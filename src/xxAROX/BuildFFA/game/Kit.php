<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
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
	 */
	public function __construct(protected string $display_name, protected array $contents, protected ?Armor $head, protected ?Armor $chest, protected ?Armor $leg, protected ?Armor $feet){
	}

	/**
	 * Function equip
	 * @param xPlayer $player
	 * @return void
	 */
	public function equip(xPlayer $player): void{
		for ($i=0; $i<9; $i++) {
			$player->getInventory()->setItem($i, $this->contents[]);
			//TODO: inv sort
		}
		$player->getArmorInventory()->setHelmet($this->head ?? ItemFactory::air());
		$player->getArmorInventory()->setChestplate($this->chest ?? ItemFactory::air());
		$player->getArmorInventory()->setLeggings($this->leg ?? ItemFactory::air());
		$player->getArmorInventory()->setBoots($this->feet ?? ItemFactory::air());
	}
}
