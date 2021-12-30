<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;


/**
 * Class Game
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:30
 * @ide PhpStorm
 * @project BuildFFA
 */
class Game{
	/** @var Kit[] */
	protected array $kits = [];
	protected int $nextArenaChange = -1;

	/**
	 * Game constructor.
	 * @param Arena[] $arenas
	 */
	public function __construct(protected array $arenas){
	}

	private function initKits(): void{
		$head = VanillaItems::LEATHER_CAP()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$chest = VanillaItems::CHAINMAIL_CHESTPLATE()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
		$leg = VanillaItems::LEATHER_PANTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$feet = VanillaItems::LEATHER_PANTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));

		$contents = [
			"sword" => VanillaItems::GOLDEN_SWORD()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)),
			"stick" => VanillaItems::STICK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 1))->setCount(1),
			"pickaxe" => VanillaItems::IRON_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2))->setUnbreakable(),
			"web" => VanillaBlocks::COBWEB()->asItem()->setCount(3),
		];
		$this->kits["%buildffa.kit.rusher"] = new Kit("%buildffa.kit.rusher", $contents, $head, $chest, $leg, $feet);
	}
}
