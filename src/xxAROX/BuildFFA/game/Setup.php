<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use Ramsey\Uuid\Uuid;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class Setup
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 21:36
 * @ide PhpStorm
 * @project BuildFFA
 */
class Setup{
	const PREFIX     = "§cSetup §l§8»§r §7§o";
	const SETUP_NONE = 0;
	public array $configuration = [];
	protected xPlayer $player;
	protected string $id;
	protected string $path;
	protected World $world;
	protected int $maxStage = 0;
	protected int $currentStage = 0;

	/**
	 * BaseSetup constructor.
	 * @param xPlayer $player
	 * @param string $path
	 * @param string $world
	 * @param int $maxStage
	 * @param array $events
	 */
	public function __construct(xPlayer $player, string $path, string $world, int $maxStage = 1, array $events = []){
		$this->player = $player;
		if (!$player->getServer()->getWorldManager()->isWorldLoaded($world)) {
			if (!$player->getServer()->getWorldManager()->loadWorld($world, true)) {
				$this->sendMessage("§cWorld not found!"); // TODO: language stuff
				$this->leave();
				return;
			}
		}
		$this->world = $this->player->getServer()->getWorldManager()->getWorldByName($world);
		$this->id = Uuid::uuid4()->toString();
		$this->path = $path;
		$this->currentStage = 1;
		$this->maxStage = $maxStage;
		$this->configuration = $this->getDefaultConfiguration();
		$this->configuration["world"] = $this->world->getFolderName();
		$plugin_manager = Server::getInstance()->getPluginManager();
		$plugin_manager->registerEvent(PlayerQuitEvent::class, function (PlayerQuitEvent $event): void{
			$player = $event->getPlayer();
			if ($player instanceof xPlayer && !is_null($player->setup)) {
				if ($player->setup->id == $this->id) {
					$this->leave();
				}
			}
		}, EventPriority::MONITOR, BuildFFA::getInstance());
		foreach ($events as $eventClass => $eventListener) {
			if (class_exists($eventClass) && method_exists($eventClass, "getPlayer")) {
				$plugin_manager->registerEvent($eventClass, function (Event $event) use ($eventListener): void{
					if (method_exists($event, "getPlayer")) {
						$player = $event->getPlayer();
						if ($player instanceof xPlayer && !is_null($player->setup) && $player->setup->id == $this->id) {
							if ($event instanceof Cancellable && method_exists($event, "cancel")) {
								$event->cancel();
							}
							if ($player->isSneaking()) {
								$this->sendMessage("Ignored.");
								return;
							}
							($eventListener)($event);
							if ($event instanceof Cancellable) {
								$event->cancel();
							}
						}
					}
				}, EventPriority::MONITOR, BuildFFA::getInstance());
			}
		}
		$player->setGamemode(GameMode::CREATIVE());
		$player->setFlying(true);
		$player->teleport($this->world->getSafeSpawn());
	}

	public function sendMessage(string $message): void{
		$this->player->sendMessage(self::PREFIX . $message);
	}

	public function leave(): void{
		$this->player->setup = null;
		$this->currentStage = 0;
		$this->configuration = $this->getDefaultConfiguration();
	}

	/**
	 * Function getDefaultConfiguration
	 * @return array
	 */
	#[Pure] #[ArrayShape([
		"respawnHeight"   => "int",
		"protection"      => "int",
		"blocks_cooldown" => "int",
	])] protected function getDefaultConfiguration(): array{
		return (new ArenaSettings())->jsonSerialize();
	}

	/**
	 * Function getPlayer
	 * @return xPlayer
	 */
	public function getPlayer(): xPlayer{
		return $this->player;
	}

	public function nextStage(): void{
		$this->currentStage++;
		if ($this->currentStage == $this->maxStage + 1) {
			$this->saveConfiguration();
			$this->player->teleport($this->player->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
			if ($this->world->getFolderName() != Server::getInstance()->getWorldManager()->getDefaultWorld()->getFolderName()) {
				$worldName = $this->world->getFolderName();
				Server::getInstance()->getWorldManager()->unloadWorld($this->world);
				Server::getInstance()->getWorldManager()->loadWorld($worldName);
				$this->world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
			} else {
				$this->sendMessage("Please restart server!");
			}
			$this->player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("block.composter.fill_success", $this->player->getPosition()->x, $this->player->getPosition()->y, $this->player->getPosition()->z, 1, 1));
			$this->sendMessage("Setup done!");
			Game::getInstance()->addArena(new Arena($this->world, new ArenaSettings($this->configuration)));
			$this->player->setup = null;
		} else {
			$this->player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("block.composter.fill", $this->player->getPosition()->x, $this->player->getPosition()->y, $this->player->getPosition()->z, 1, 1));
		}
	}

	/**
	 * Function saveConfiguration
	 * @return void
	 */
	protected function saveConfiguration(): void{
		$worldName = $this->configuration["world"];
		$arenas = new Config($this->path, Config::DETECT);
		unset($this->configuration["world"]);
		$arenas->set($worldName, $this->configuration);
		$arenas->save();
	}

	/**
	 * Function getCurrentStage
	 * @return int
	 */
	public function getCurrentStage(): int{
		return $this->currentStage;
	}

	/**
	 * Function addPos
	 * @param Position $position
	 * @param float|int $x
	 * @param float|int $y
	 * @param float|int $z
	 * @return Position
	 */
	protected function addPos(Position $position, float|int $x, float|int $y = 0, float|int $z = 0): Position{
		return new Position($position->x + $x, $position->y + $y, $position->z + $z, $position->getWorld());
	}
}
