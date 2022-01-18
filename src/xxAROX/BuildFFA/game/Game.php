<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use Closure;
use Frago9876543210\EasyForms\elements\FunctionalButton;
use Frago9876543210\EasyForms\elements\Slider;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;
use Frago9876543210\EasyForms\forms\MenuForm;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\sound\FizzSound;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\entity\BlockEntity;
use xxAROX\BuildFFA\generic\entry\BlockBreakEntry;
use xxAROX\BuildFFA\generic\entry\BlockEntry;
use xxAROX\BuildFFA\items\overwrite\PlatformItem;
use xxAROX\BuildFFA\items\PlaceHolderItem;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class Game
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:30
 * @ide PhpStorm
 * @project BuildFFA
 */
class Game{
	const MAP_CHANGE_INTERVAL = (60 * 15);
	use SingletonTrait;


	public array $mapVotes = [];
	protected ?BossBar $bossBar = null;
	/** @var Kit[] */
	protected array $kits = [];
	protected int $lastArenaChange = -1;
	protected int $nextArenaChange = -1;
	protected Arena|null $arena = null;
	protected array $arenas = [];
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
		if (count($arenas) >= 1) {
			if (count($arenas) > 1) {
				$this->lastArenaChange = time();
				$this->nextArenaChange = Server::getInstance()->getTick() + (self::MAP_CHANGE_INTERVAL * 20);
				//$this->bossBar = new BossBar();
			}
			$this->arenas = $arenas;
			$this->arena = $this->arenas[array_rand($this->arenas)];
		} else {
			BuildFFA::getInstance()->getLogger()->info("§3Preparing default Arena..");
			$this->arena = new Arena(Server::getInstance()->getDefaultLevel(), new ArenaSettings());
			$this->arena->getWorld()->setTime(Level::TIME_NOON);
			$this->arena->getWorld()->stopTime();
			$this->arena->getWorld()->setAutoSave(false);
		}
		foreach ($this->arenas as $a) {
			$this->mapVotes[$a->getWorld()->getFolderName()] = 0;
		}
		$this->initKits();
		BuildFFA::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(fn() => $this->tick()), 1);
	}

	/**
	 * Function initKits
	 * @return void
	 */
	private function initKits(): void{
		$head = ItemFactory::get(ItemIds::LEATHER_CAP);
		$head->setUnbreakable();
		$head->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1));
		$chest = ItemFactory::get(ItemIds::LEATHER_CHESTPLATE);
		$chest->setUnbreakable();
		$chest->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 2));
		$leg = ItemFactory::get(ItemIds::LEATHER_PANTS);
		$leg->setUnbreakable();
		$leg->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1));
		$feet = ItemFactory::get(ItemIds::LEATHER_BOOTS);
		$feet->setUnbreakable();
		$feet->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1));
		$basicBlocks = ItemFactory::get(BlockIds::SANDSTONE)->setCount(64);
		$basicStick = ItemFactory::get(ItemIds::STICK)->setCount(1);
		$basicStick->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::KNOCKBACK), 1));
		$basicPickaxe = ItemFactory::get(ItemIds::IRON_PICKAXE);
		$basicPickaxe->setUnbreakable();
		$basicPickaxe->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 2));
		$basicSword = ItemFactory::get(ItemIds::GOLDEN_SWORD);
		$basicSword->setUnbreakable();
		$basicSword->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 1));
		$basicBow = ItemFactory::get(ItemIds::BOW);
		$basicBow->setUnbreakable();
		$basicBow->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::INFINITY), 1));
		$basicWebs = (new PlaceHolderItem(BlockIds::INVISIBLE_BEDROCK, 0, $w = ItemFactory::get(BlockIds::WEB)->setCount(3), 3));
		$nbt = $w->getNamedTag();
		$nbt->setByte("pop", intval(true));
		$w->setNamedTag($nbt);
		$basicEnderpearl = (new PlaceHolderItem(ItemIds::ENDER_EYE, 0, $w = ItemFactory::get(ItemIds::ENDER_PEARL)->setCount(1), 16));
		$nbt = $w->getNamedTag();
		$nbt->setByte("pop", intval(true));
		$w->setNamedTag($nbt);
		$platform = (new PlaceHolderItem(ItemIds::STICK, 0, $w = new PlatformItem(), 16, Closure::fromCallable([
			$w,
			"applyCountdown",
		])));
		$nbt = $w->getNamedTag();
		$nbt->setByte("pop", intval(true));
		$w->setNamedTag($nbt);
		$contents = [
			"sword"   => $basicSword,
			"stick"   => $basicStick,
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
			"blocks"  => $basicBlocks,
		];
		$this->kits["%buildffa.kit.rusher"] = new Kit("%buildffa.kit.rusher", $contents, $head, $chest, $leg, $feet);
		$contents = [
			"sword"   => $basicSword,
			"stick"   => $basicStick,
			"bow"     => $basicBow,
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
			"blocks"  => $basicBlocks,
			"arrow"  => ItemFactory::get(ItemIds::ARROW)->setCount(1),
		];
		$this->kits["%buildffa.kit.archer"] = new Kit("%buildffa.kit.archer", $contents, $head, $chest, $leg, $feet);
		$basicStick2 = clone $basicStick;
		$basicStick2->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::KNOCKBACK), 2));
		$contents = [
			"sword"   => $basicSword,
			"stick"   => $basicStick2,
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
			"blocks"  => $basicBlocks,
		];
		$this->kits["%buildffa.kit.knocker"] = new Kit("%buildffa.kit.knocker", $contents, $head, $chest, $leg, $feet);
		$contents = [
			"sword"      => $basicSword,
			"stick"      => $basicStick,
			"enderpearl" => $basicEnderpearl,
			"pickaxe"    => $basicPickaxe,
			"web"        => $basicWebs,
			"blocks"     => $basicBlocks,
		];
		$this->kits["%buildffa.kit.enderpearl"] = new Kit("%buildffa.kit.enderpearl", $contents, $head, $chest, $leg, $feet);
		$contents = [
			"sword"    => $basicSword,
			"stick"    => $basicStick,
			"platform" => $platform,
			"pickaxe"  => $basicPickaxe,
			"web"      => $basicWebs,
			"blocks"   => $basicBlocks,
		];
		$this->kits["%buildffa.kit.platform"] = new Kit("%buildffa.kit.platform", $contents, $head, $chest, $leg, $feet);
	}

	/**
	 * Function tick
	 * @return void
	 */
	private function tick(): void{
		if (!is_null($this->bossBar)) {
			$this->bossBar->setPercentage(((self::MAP_CHANGE_INTERVAL * 20) / 100 * ($this->nextArenaChange - Server::getInstance()->getTick())) / 100);
			foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
				$minutes = intval(round((($this->nextArenaChange - Server::getInstance()->getTick()) / 20 / 60)));
				if ($minutes > 0) {
					$onlinePlayer->sendActionBarMessage("Map reset in " . $minutes . " minutes.");
				} else {
					$onlinePlayer->sendActionBarMessage("Map reset in <0 minutes.");
				}
				$this->bossBar->addPlayer($onlinePlayer);
			}
		}
		if ($this->lastArenaChange != -1 && Server::getInstance()->getTick() >= $this->nextArenaChange) {
			$totalVotes = 0;
			foreach ($this->mapVotes as $mapVote => $amount) {
				$totalVotes += $amount;
			}
			$_maps = $this->mapVotes;
			$maps = array_flip($_maps);
			if ($totalVotes == 0) {
				unset($_maps[$this->arena->getWorld()->getFolderName()]);
				$maps = array_flip($_maps);
			}
			shuffle($maps);
			$worldName = $maps[max($this->mapVotes)];
			unset($_maps, $maps);
			foreach ($this->arenas as $arena) {
				if ($arena->getWorld()->getFolderName() == $worldName) {
					$arena->setActive(true);
					unset($this->arena);
					$this->arena = $arena;
					$this->lastArenaChange = time();
					$this->nextArenaChange = Server::getInstance()->getTick() + (Game::MAP_CHANGE_INTERVAL * 20);
					unset($current);
					break;
				}
			}
		}
		foreach ($this->placedBlocks as $encodedPos => $entry) {
			if (microtime(true) >= $entry->getTimestamp()) {
				$entry->getPosition()->getLevel()->addParticle(new DestroyBlockParticle($entry->getPosition(), $entry->getPosition()->getLevel()->getBlock($entry->getPosition())));
				$fallingBlock = new BlockEntity($entry->getPosition(), $entry->getPosition()->getLevel()->getBlock($entry->getPosition()));
				$fallingBlock->spawnToAll();
				$entry->getPosition()->getLevel()->setBlock($entry->getPosition(), BlockFactory::get(0));
				unset($this->placedBlocks[$encodedPos]);
			}
		}
		foreach ($this->destroyedBlocks as $encodedPos => $entry) {
			if (microtime(true) >= $entry->getTimestamp()) {
				$current = $entry->getPosition()->getLevel()->getBlock($entry->getPosition());
				if ($current->getId() != BlockIds::AIR) {
					$entry->getPosition()->getLevel()->addParticle(new DestroyBlockParticle($entry->getPosition(), $current));
					$entry->getPosition()->getLevel()->addSound(new FizzSound($entry->getPosition()));
					$entry->getPosition()->getLevel()->setBlock($entry->getPosition(), $entry->getLegacy());
				}
				unset($this->destroyedBlocks[$encodedPos]);
			}
		}
	}

	/**
	 * Function skip
	 * @return void
	 */
	public function skip(): void{
		$this->nextArenaChange = 0;
	}

	/**
	 * Function getKit
	 * @param null|string $name
	 * @return Kit
	 */
	public function getKit(?string $name): Kit{
		return $this->kits[$name] ?? $this->kits[array_rand($this->kits)];
	}

	/**
	 * Function breakBlock
	 * @param Block $block
	 * @param int $additionalSeconds
	 * @return void
	 */
	public function breakBlock(Block $block, int $additionalSeconds = 0): void{
		if (isset($this->placedBlocks[encodePosition($block->asPosition())])) {
			unset($this->placedBlocks[encodePosition($block->asPosition())]);
			return;
		}
		if (!isset($this->destroyedBlocks[encodePosition($block->asPosition())])) { //JIC
			$this->destroyedBlocks[encodePosition($block->asPosition())] = new BlockBreakEntry($block, $block->asPosition(), microtime(true) + $this->arena->getSettings()->blocks_cooldown + $additionalSeconds);
		}
	}

	/**
	 * Function placeBlock
	 * @param Block $block
	 * @param int $additionalSeconds
	 * @return void
	 */
	public function placeBlock(Block $block, int $additionalSeconds = 0): void{
		if (isset($this->destroyedBlocks[encodePosition($block->asPosition())])) {
			return;
		}
		$extraTime = match (true) {
			$block->getId() == BlockIds::END_STONE => 5,
			$block->getId() == BlockIds::EMERALD_BLOCK => 10,
			default => 0
		};
		$this->placedBlocks[encodePosition($block->asPosition())] = new BlockEntry($block->asPosition(), microtime(true) + $this->arena->getSettings()->blocks_cooldown + $extraTime + $additionalSeconds);
		$packet = new LevelEventPacket();
		$packet->evid = LevelEventPacket::EVENT_BLOCK_START_BREAK;
		$packet->data = intval(round(65535 / (20 * ($this->arena->getSettings()->blocks_cooldown + $extraTime + $additionalSeconds))));
		$packet->position = $block->asPosition();
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $packet);
	}

	/**
	 * Function filterPlayer
	 * @param Player $player
	 * @return bool
	 */
	public function filterPlayer(Player $player): bool{
		if ($player->getGamemode() != Player::SURVIVAL) {
			return false;
		}
		if ($player->getLevel()->getFolderName() !== $this->arena->getWorld()->getFolderName()) {
			return false;
		}
		return true;
	}

	/**
	 * Function setup
	 * @param xPlayer $player
	 * @return void
	 */
	public function setup(xPlayer $player): void{
		$worlds = [];
		foreach (array_diff(scandir(Server::getInstance()->getDataPath() . "worlds/"), ["..", "."]) as $world) {
			if (!in_array($world, array_map(fn(Arena $arena) => $arena->getWorld()->getFolderName(), Game::getInstance()->getArenas()))) {
				$worlds[] = new FunctionalButton($world, function (xPlayer $player) use ($world): void{
					$player->sendMessage("now break block at respawn_height");
					$player->setup = new Setup($player, BuildFFA::getInstance()->getDataFolder() . "config.yml", $world, 3, [
						BlockBreakEvent::class => function (BlockBreakEvent $event): void{
							/** @var xPlayer $player */
							$player = $event->getPlayer();
							if ($player->setup->getCurrentStage() == 1) {
								$player->setup->configuration["respawn_height"] = $event->getBlock()->asPosition()->y;
								$player->setup->sendMessage("respawn_height set to " . $event->getBlock()->asPosition()->y);
								$player->setup->sendMessage("now break block at spawn protection border");
							} else if ($player->setup->getCurrentStage() == 2) {
								$player->setup->configuration["protection"] = $event->getBlock()->asPosition()->distance($player->getLevel()->getSpawnLocation());
								$player->setup->sendMessage("spawn protection set to " . $event->getBlock()->asPosition()->distance($player->getLevel()->getSpawnLocation()));
								$player->sendForm(new CustomForm("Select block cooldown", [new Slider("Seconds", 0.5, 30, 0.5, 5)], function (xPlayer $player, CustomFormResponse $response): void{
									$count = $response->getSlider()->getValue();
									$player->setup->configuration["blocks_cooldown"] = $count;
									$player->setup->sendMessage("blocks_cooldown set to " . $count);
									$player->setup->nextStage();
								}, function (xPlayer $player): void{
									$player->setup->leave();
								}));
							}
							$player->setup->nextStage();
						},
					]);
				});
			}
		}
		if (count($worlds) == 0) {
			$player->sendMessage("§cNo new maps found!");
			return;
		}
		$player->sendForm(new MenuForm("New Map", "", $worlds));
	}

	/**
	 * Function getArenas
	 * @return array
	 */
	public function getArenas(): array{
		return $this->arenas;
	}

	/**
	 * Function addArena
	 * @param Arena $arena
	 * @return void
	 */
	public function addArena(Arena $arena): void{
		$this->arenas[] = $arena;
	}

	/**
	 * Function getArena
	 * @return ?Arena
	 */
	public function getArena(): ?Arena{
		return $this->arena;
	}

	/**
	 * Function getKits
	 * @return array
	 */
	public function getKits(): array{
		return $this->kits;
	}

	/**
	 * Function getLastArenaChange
	 * @return int
	 */
	public function getLastArenaChange(): int{
		return $this->lastArenaChange;
	}

	/**
	 * Function getNextArenaChange
	 * @return float|int
	 */
	public function getNextArenaChange(): float|int{
		return $this->nextArenaChange;
	}
}
