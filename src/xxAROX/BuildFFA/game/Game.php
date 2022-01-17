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
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\sound\FizzSound;
use pocketmine\world\World;
use xenialdan\apibossbar\BossBar;
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
	const MAP_CHANGE_INTERVAL = (60 * 1);
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
				$this->bossBar = new BossBar();
			}
			$this->arenas = $arenas;
			$this->arena = $this->arenas[array_rand($this->arenas)];
		} else {
			getLogger()->info("§3Preparing default Arena..");
			$this->arena = new Arena(Server::getInstance()->getWorldManager()->getDefaultWorld(), new ArenaSettings());
			$this->arena->getWorld()->setTime(World::TIME_NOON);
			$this->arena->getWorld()->stopTime();
			$this->arena->getWorld()->setAutoSave(false);
		}
		foreach ($this->arenas as $a) {
			$this->mapVotes[$a->getWorld()->getFolderName()] = 0;
		}
		$this->initKits();
		BuildFFA::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(fn() => $this->tick()), 1);
	}

	private function initKits(): void{
		$head = VanillaItems::LEATHER_CAP()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$chest = VanillaItems::CHAINMAIL_CHESTPLATE()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
		$leg = VanillaItems::LEATHER_PANTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$feet = VanillaItems::LEATHER_BOOTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
		$air = ItemFactory::air();
		$basicBlocks = VanillaBlocks::SANDSTONE()->asItem()->setCount(64);
		$basicStick = VanillaItems::STICK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 1))->setCount(1);
		$basicPickaxe = VanillaItems::IRON_PICKAXE()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2));
		$basicSword = VanillaItems::GOLDEN_SWORD()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1));
		$basicBow = VanillaItems::BOW()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
		$basicWebs = (new PlaceHolderItem(new ItemIdentifier(ItemIds::BARRIER, 0), $w = VanillaBlocks::COBWEB()->asItem()->setCount(3), 3));
		$w->setNamedTag($w->getNamedTag()->setByte("pop", intval(true)));
		$basicEnderpearl = (new PlaceHolderItem(new ItemIdentifier(ItemIds::ENDER_EYE, 0), $w = VanillaItems::ENDER_PEARL()->setCount(1), 16));
		$w->setNamedTag($w->getNamedTag()->setByte("pop", intval(true)));
		$platform = (new PlaceHolderItem(new ItemIdentifier(ItemIds::STICK, 0), $w = new PlatformItem(), 16, Closure::fromCallable([
			$w,
			"applyCountdown",
		])));
		$w->setNamedTag($w->getNamedTag()->setByte("pop", intval(true)));
		$contents = [
			"sword"   => $basicSword,
			"stick"   => $basicStick,
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
			"blocks"  => $basicBlocks,
		];
		$this->kits["%buildffa.kit.rusher"] = new Kit("%buildffa.kit.rusher", $contents, $air, $head, $chest, $leg, $feet);
		$contents = [
			"sword"   => $basicSword,
			"stick"   => $basicStick,
			"bow"     => $basicBow,
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
			"blocks"  => $basicBlocks,
		];
		$this->kits["%buildffa.kit.archer"] = new Kit("%buildffa.kit.archer", $contents, VanillaItems::ARROW()->setCount(1), $head, $chest, $leg, $feet);
		$contents = [
			"sword"   => $basicSword,
			"stick"   => $basicStick->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 2)),
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
			"blocks"  => $basicBlocks,
		];
		$this->kits["%buildffa.kit.knocker"] = new Kit("%buildffa.kit.knocker", $contents, $air, $head, $chest, $leg, $feet);
		$contents = [
			"sword"      => $basicSword,
			"stick"      => $basicStick,
			"enderpearl" => $basicEnderpearl,
			"pickaxe"    => $basicPickaxe,
			"web"        => $basicWebs,
			"blocks"     => $basicBlocks,
		];
		$this->kits["%buildffa.kit.enderpearl"] = new Kit("%buildffa.kit.enderpearl", $contents, $air, $head, $chest, $leg, $feet);
		$contents = [
			"sword"    => $basicSword,
			"stick"    => $basicStick,
			"platform" => $platform,
			"pickaxe"  => $basicPickaxe,
			"web"      => $basicWebs,
			"blocks"   => $basicBlocks,
		];
		$this->kits["%buildffa.kit.platform"] = new Kit("%buildffa.kit.platform", $contents, $air, $head, $chest, $leg, $feet);
	}

	private function tick(): void{
		if (!is_null($this->bossBar)) {
			$this->bossBar->setPercentage(((self::MAP_CHANGE_INTERVAL * 20) / 100 * ($this->nextArenaChange - Server::getInstance()->getTick())) / 100);
			foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
				$minutes = intval(round((($this->nextArenaChange - Server::getInstance()->getTick()) / 20 / 60)));
				if ($minutes > 0) {
					$onlinePlayer->sendActionBarMessage("Map reset in " . $minutes . " minutes.");
				} else {
					$onlinePlayer->sendActionBarMessage("Map reset in > 0 minutes.");
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
				$entry->getPosition()->getWorld()->addParticle($entry->getPosition(), new BlockBreakParticle($entry->getPosition()->getWorld()->getBlock($entry->getPosition())));
				$fallingBlock = new BlockEntity($entry->getPosition(), $entry->getPosition()->getWorld()->getBlock($entry->getPosition()));
				$fallingBlock->spawnToAll();
				$entry->getPosition()->getWorld()->setBlock($entry->getPosition(), VanillaBlocks::AIR());
				unset($this->placedBlocks[$encodedPos]);
			}
		}
		foreach ($this->destroyedBlocks as $encodedPos => $entry) {
			if (microtime(true) >= $entry->getTimestamp()) {
				$current = $entry->getPosition()->getWorld()->getBlock($entry->getPosition());
				if ($current->getId() != BlockLegacyIds::AIR) {
					$entry->getPosition()->getWorld()->addParticle($entry->getPosition(), new BlockBreakParticle($current));
					$entry->getPosition()->getWorld()->addSound($entry->getPosition(), new FizzSound());
					$entry->getPosition()->getWorld()->setBlock($entry->getPosition(), $entry->getLegacy());
				}
				unset($this->destroyedBlocks[$encodedPos]);
			}
		}
	}

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
		if (isset($this->placedBlocks[encodePosition($block->getPosition())])) {
			unset($this->placedBlocks[encodePosition($block->getPosition())]);
			return;
		}
		if (!isset($this->destroyedBlocks[encodePosition($block->getPosition())])) { //JIC
			$this->destroyedBlocks[encodePosition($block->getPosition())] = new BlockBreakEntry($block, $block->getPosition(), microtime(true) + $this->arena->getSettings()->blocks_cooldown + $additionalSeconds);
		}
	}

	/**
	 * Function placeBlock
	 * @param Block $block
	 * @param int $additionalSeconds
	 * @return void
	 */
	public function placeBlock(Block $block, int $additionalSeconds = 0): void{
		if (isset($this->destroyedBlocks[encodePosition($block->getPosition())])) {
			return;
		}
		$extraTime = match (true) {
			$block->getId() == VanillaBlocks::END_STONE()->getId() => 5,
			$block->getId() == VanillaBlocks::EMERALD()->getId() => 10,
			default => 0
		};
		$this->placedBlocks[encodePosition($block->getPosition())] = new BlockEntry($block->getPosition(), microtime(true) + $this->arena->getSettings()->blocks_cooldown + $extraTime + $additionalSeconds);
		Server::getInstance()->broadcastPackets(Server::getInstance()->getOnlinePlayers(), [LevelEventPacket::create(LevelEvent::BLOCK_START_BREAK, intval(round(65535 / (20 * ($this->arena->getSettings()->blocks_cooldown + $extraTime + $additionalSeconds)))), $block->getPosition())]);
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
								$player->setup->configuration["respawn_height"] = $event->getBlock()->getPosition()->y;
								$player->setup->sendMessage("respawn_height set to " . $event->getBlock()->getPosition()->y);
								$player->setup->sendMessage("now break block at spawn protection border");
							} else if ($player->setup->getCurrentStage() == 2) {
								$player->setup->configuration["protection"] = $event->getBlock()->getPosition()->distance($player->getWorld()->getSpawnLocation());
								$player->setup->sendMessage("spawn protection set to " . $event->getBlock()->getPosition()->distance($player->getWorld()->getSpawnLocation()));
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
