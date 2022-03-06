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
use pocketmine\entity\utils\Bossbar;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
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
use pocketmine\utils\TextFormat;
use ryzerbe\core\language\LanguageProvider;
use ryzerbe\core\player\RyZerPlayerProvider;
use ryzerbe\core\util\Settings;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\entity\BlockEntity;
use xxAROX\BuildFFA\generic\entry\BlockBreakEntry;
use xxAROX\BuildFFA\generic\entry\BlockEntry;
use xxAROX\BuildFFA\items\overwrite\PlatformItem;
use xxAROX\BuildFFA\items\PlaceHolderItem;
use xxAROX\BuildFFA\player\xPlayer;
use function array_rand;
use function array_search;
use function count;
use function max;
use function time;


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
	const KIT_CHANGE_INTERVAL = (60 * 10);
	use SingletonTrait;

	public array $mapVotes = [];
	protected ?BossBar $bossBar = null;

	/** @var Kit[] */
	protected array $kits = [];
	public Kit $kit;
	protected int $lastArenaChange = -1;
	protected int $nextArenaChange = -1;
	protected int $nextKitChange = -1;
	protected int $lastKitChange = -1;

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
				$this->bossBar->setTitle(BuildFFA::PREFIX.TextFormat::WHITE." RyZer".TextFormat::RED."BE");
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
		$this->kit = $this->kits[array_rand($this->kits)];
		$this->lastKitChange = time();
		$this->nextKitChange = Server::getInstance()->getTick() + (self::KIT_CHANGE_INTERVAL * 20);
		BuildFFA::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $_): void{ $this->tick(); }), 1);
	}

	/**
	 * Function initKits
	 * @return void
	 */
	private function initKits(): void{
		$head = ItemFactory::get(ItemIds::LEATHER_CAP);
		$head->setUnbreakable();
		$head->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 1));
		$chest = ItemFactory::get(ItemIds::CHAINMAIL_CHESTPLATE);
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
		$knockerStick = ItemFactory::get(ItemIds::STICK)->setCount(1);
		$knockerStick->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::KNOCKBACK), 2));
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
		$rod = Item::get(ItemIds::FISHING_ROD);
		$rod->setUnbreakable();
		$contents = [
			"sword"   => $basicSword,
			"stick"   => $basicStick,
            "blocks"  => $basicBlocks,
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
            "enderpearl" => $basicEnderpearl,
            "platform" => $platform
		];
		$this->kits["Rusher"] = new Kit("Rusher", $contents, $head, $chest, $leg, $feet);
		$contents = [
			"sword"   => $basicSword,
            "blocks"  => $basicBlocks,
			"bow"     => $basicBow,
			"pickaxe" => $basicPickaxe,
			"web"     => $basicWebs,
            "enderpearl" => $basicEnderpearl,
            "platform" => $platform,
			"arrow"  => ItemFactory::get(ItemIds::ARROW)->setCount(1),
		];
		$this->kits["Spammer"] = new Kit("Archer", $contents, $head, $chest, $leg, $feet);
        $contents = [
            "sword"   => $basicSword,
            "blocks"  => $basicBlocks,
            "rod"     => $rod,
            "pickaxe" => $basicPickaxe,
            "web"     => $basicWebs,
            "enderpearl" => $basicEnderpearl,
            "platform" => $platform
        ];
        $this->kits["Basedef"] = new Kit("Basedef", $contents, $head, $chest, $leg, $feet);
        $contents = [
            "sword"   => $basicSword,
            "blocks"  => $basicBlocks,
            "snowballs"     => Item::get(ItemIds::SNOWBALL, 0, 8),
            "pickaxe" => $basicPickaxe,
            "web"     => $basicWebs,
            "enderpearl" => $basicEnderpearl,
            "platform" => $platform
        ];
        $this->kits["Snowballer"] = new Kit("Snowballer", $contents, $head, $chest, $leg, $feet);
	}

	/**
	 * Function tick
	 * @return void
	 */
	private function tick(): void{
		if (!is_null($this->bossBar)) {
			$this->bossBar->setHealthPercent(((self::MAP_CHANGE_INTERVAL * 20) / 100 * ($this->nextArenaChange - Server::getInstance()->getTick())) / 100);

			/** @var xPlayer $onlinePlayer */
            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
				$minutes = intval(round((($this->nextArenaChange - Server::getInstance()->getTick()) / 20 / 60)));
				$minutesKit = intval(round((($this->nextKitChange - Server::getInstance()->getTick()) / 20 / 60)));
				if($minutesKit >= 0 && $minutes >= 0) {
                    $onlinePlayer->sendActionBarMessage(LanguageProvider::getMessageContainer("bffa-popup-map-change", $onlinePlayer, ["#minutes" => $minutes])
                        ."\n".LanguageProvider::getMessageContainer("bffa-popup-kit-change", $onlinePlayer, ["#minutes" => $minutesKit]));
                }else if($minutesKit >= 0 && $minutes < 1) {
                    $onlinePlayer->sendActionBarMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("bffa-popup-few-seconds-map-change", $onlinePlayer, ["#minutes" => $minutes])
                        ."\n".LanguageProvider::getMessageContainer("bffa-popup-kit-change", $onlinePlayer, ["#minutes" => $minutesKit]));
                }else if($minutesKit < 1 && $minutes > 0) {
                    $onlinePlayer->sendActionBarMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("bffa-popup-map-change", $onlinePlayer, ["#minutes" => $minutes])
                        ."\n".LanguageProvider::getMessageContainer("bffa-popup-few-seconds-kit-change", $onlinePlayer, ["#minutes" => $minutesKit]));

                }else if($minutes < 1 && $minutesKit < 0) {
                    $onlinePlayer->sendActionBarMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("bffa-popup-few-seconds-map-change", $onlinePlayer)
                        ."\n".LanguageProvider::getMessageContainer("bffa-popup-few-seconds-kit-change", $onlinePlayer, ["#minutes" => $minutesKit]));
                }
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
			$worldName = $maps[max($this->mapVotes)] ?? null;
			if($worldName === null) {
				$worldName = $maps[array_rand($maps)];
			}
			unset($_maps, $maps);
			foreach ($this->arenas as $arena) {
				if ($arena->getWorld()->getFolderName() == $worldName) {
					$arena->setActive(true);
					unset($this->arena);
					$this->arena = $arena;
					$this->lastArenaChange = time();
					$this->nextArenaChange = Server::getInstance()->getTick() + (Game::MAP_CHANGE_INTERVAL * 20);
					unset($current);
					foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                        $player->sendMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("bffa-map-change", $player->getName(), ["#map" => TextFormat::GREEN.$arena->getWorld()->getFolderName()]));
                    }
                    break;
				}
			}
		}

		if($this->lastKitChange != -1 && Server::getInstance()->getTick() >= $this->nextKitChange){
            /** @var xPlayer $player */
            $kitVotes = [];
            foreach(Server::getInstance()->getOnlinePlayers() as $player){
                if($player->kit_vote === null) continue;
                $rbePlayer = RyZerPlayerProvider::getRyzerPlayer($player->getName());
                if($rbePlayer === null) continue;
                if(empty($kitVotes[$player->kit_vote->getDisplayName()])) $kitVotes[$player->kit_vote->getDisplayName()] = 0;

                $vote = Settings::VOTING[$rbePlayer->getRank()->getRankName()] ?? 1;
                if($player->hasPermission("game.votes.team")) $vote = 5;

                $kitVotes[$player->kit_vote->getDisplayName()] += $vote;
            }

            if(count($kitVotes) === 0) {
                foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->sendMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("bffa-kit-continue", $player->getName(), ["#kit" => TextFormat::GREEN.$this->kit->getDisplayName()]));
                }
                $this->lastKitChange = time();
                $this->nextKitChange = Server::getInstance()->getTick() + (Game::KIT_CHANGE_INTERVAL * 20);
            }else{
                $kit = $this->getKit(array_search(max($kitVotes), $kitVotes));
                $this->kit = $kit;
                $this->lastKitChange = time();
                $this->nextKitChange = Server::getInstance()->getTick() + (Game::KIT_CHANGE_INTERVAL * 20);
                if($this->kit->getDisplayName() === $kit->getDisplayName()) {
                    $player->sendMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("bffa-kit-continue", $player->getName(), ["#kit" => TextFormat::GREEN.$this->kit->getDisplayName()]));
                }else {
                    foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                        $player->setSelectedKit($kit);
                        $player->sendMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("bffa-kit-change", $player->getName(), ["#kit" => TextFormat::GREEN.$kit->getDisplayName()]));
                    }
                }
            }
        }


		foreach ($this->placedBlocks as $encodedPos => $entry) {
			if (microtime(true) >= $entry->getTimestamp()) {
				$block = $entry->getPosition()->getLevel()->getBlock($entry->getPosition());
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
				if ($current->getId() != BlockIds::AIR)
					continue;
				$entry->getPosition()->getLevel()->addParticle(new DestroyBlockParticle($entry->getPosition(), $current));
				$entry->getPosition()->getLevel()->addSound(new FizzSound($entry->getPosition()));
				$entry->getPosition()->getLevel()->setBlock($entry->getPosition(), $entry->getLegacy());
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
					$player->sendMessage(Setup::PREFIX."now break block at respawn_height");
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
