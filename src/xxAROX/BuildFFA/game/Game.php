<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\PortalParticle;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\entity\BlockEntity;
use xxAROX\BuildFFA\generic\entry\BlockBreakEntry;
use xxAROX\BuildFFA\generic\entry\BlockEntry;


/**
 * Class Game
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:30
 * @ide PhpStorm
 * @project BuildFFA
 */
class Game{
	const MAP_CHANGE_INTERVAL = (20 * 60 * 15);
	use SingletonTrait;


	/** @var Kit[] */
	protected array $kits = [];
	protected int $nextArenaChange = -1;
	protected Arena|null $arena = null;
	protected array $arenas = [];
	protected int $lastArenaChange = -1;
	/** @var BlockBreakEntry[] */
	protected array $destroyedBlocks = [];
	/** @var BlockEntry[] */
	protected array $placedBlocks = [];

	/**
	 * Game constructor.
	 * @param Arena[] $arenas
	 */
	public function __construct(array $arenas){
		self::setInstance($this);
		if (count($arenas) > 1) {
			$this->arenas = $arenas;
			$this->nextArenaChange = self::MAP_CHANGE_INTERVAL * 20;
			$this->arena = $this->arenas[array_rand($this->arenas)];
		} else {
			getLogger()->info("§3Using default Arena");
			$this->arena = new Arena(Server::getInstance()->getWorldManager()->getDefaultWorld(), new ArenaSettings());
		}
		$this->lastArenaChange = time();
		$this->initKits();
		BuildFFA::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(fn() => $this->tick()), 1);
	}

	private function initKits(): void{
		$head = VanillaItems::LEATHER_CAP()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$chest = VanillaItems::CHAINMAIL_CHESTPLATE()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
		$leg = VanillaItems::LEATHER_PANTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$feet = VanillaItems::LEATHER_PANTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$contents = [
			"sword"   => VanillaItems::GOLDEN_SWORD()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)),
			"stick"   => VanillaItems::STICK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 1))->setCount(1),
			"pickaxe" => VanillaItems::IRON_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2))->setUnbreakable(),
			"web"     => VanillaBlocks::COBWEB()->asItem()->setCount(3),
		];
		$this->kits["%buildffa.kit.rusher"] = new Kit("%buildffa.kit.rusher", $contents, $head, $chest, $leg, $feet);
	}

	private function tick(): void{
		foreach ($this->placedBlocks as $encodedPos => $entry) {
			if (microtime(true) >= $entry->getTimestamp()) {
				$entry->getPosition()->getWorld()->addParticle($entry->getPosition(), new BlockBreakParticle($entry->getPosition()->getWorld()->getBlock($entry->getPosition())));
				$fallingBlock = new BlockEntity($entry->getPosition(), $entry->getPosition()->getWorld()->getBlock($entry->getPosition()));
				$fallingBlock->spawnToAll();
				$entry->getPosition()->getWorld()->setBlock($entry->getPosition(), VanillaBlocks::AIR());
				unset($this->placedBlocks[$encodedPos]);
			}
		}
		foreach ($this->destroyedBlocks as $encodedPos => $entry) {
			if (microtime(true) >= $entry->getTimestamp()) {
				$entry->getPosition()->getWorld()->addParticle($entry->getPosition(), new PortalParticle());
				$entry->getPosition()->getWorld()->setBlock($entry->getPosition(), $entry->getLegacy());
				unset($this->destroyedBlocks[$encodedPos]);
			}
		}
	}

	public function breakBlock(Block $block): void{
		if (isset($this->placedBlocks[encodePosition($block->getPosition())])) {
			unset($this->placedBlocks[encodePosition($block->getPosition())]);
			return;
		}
		if (!isset($this->destroyedBlocks[encodePosition($block->getPosition())])) { //JIC
			$this->destroyedBlocks[encodePosition($block->getPosition())] = new BlockBreakEntry($block, $block->getPosition(), microtime(true) +$this->arena->getSettings()->blocks_cooldown);
		}
	}

	public function placeBlock(Block $block): void{
		if (isset($this->destroyedBlocks[encodePosition($block->getPosition())])) {
			return;
		}
		$extraTime = match (true) {
			$block->getId() == VanillaBlocks::END_STONE()->getId() => 5,
			$block->getId() == VanillaBlocks::EMERALD()->getId() => 10,
			default => 0
		};
		$this->placedBlocks[encodePosition($block->getPosition())] = new BlockEntry($block->getPosition(), microtime(true) +$this->arena->getSettings()->blocks_cooldown + $extraTime);
		//TODO: break animation
	}

	public function filterPlayer(Player $player): bool{
		if ($player->getGamemode()->id() != GameMode::SURVIVAL()->id()) {
			return false;
		}
		if ($player->getWorld()->getFolderName() !== $this->arena->getWorld()->getFolderName()) {
			return false;
		}
		return true;
	}
}
