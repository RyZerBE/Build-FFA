<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use Closure;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\UUID;
use ReflectionException;
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
	public string $id;
	protected string $path;
	protected Level $world;
	protected int $maxStage = 0;
	protected int $currentStage = 0;

	/**
	 * BaseSetup constructor.
	 * @param xPlayer $player
	 * @param string $path
	 * @param string $world
	 * @param int $maxStage
	 * @param array $events
	 * @throws ReflectionException
	 */
	public function __construct(xPlayer $player, string $path, string $world, int $maxStage = 1, array $events = []){
		$this->player = $player;
		if (!$player->getServer()->isLevelLoaded($world)) {
			if (!$player->getServer()->loadLevel($world)) {
				$this->sendMessage("§cLevel not found!");
				$this->leave();
				return;
			}
		}
		$this->world = $this->player->getServer()->getLevelByName($world);
		$this->id = UUID::fromRandom()->toString();
		$this->path = $path;
		$this->currentStage = 1;
		$this->maxStage = $maxStage;
		$this->configuration = $this->getDefaultConfiguration();
		$this->configuration["world"] = $this->world->getFolderName();
		$plugin_manager = Server::getInstance()->getPluginManager();
		$plugin_manager->registerEvent(PlayerQuitEvent::class, new class($this) implements Listener{
			/**
			 * Anonymous constructor.
			 * @param Setup $setup
			 */
			public function __construct(private Setup $setup){
			}

			/**
			 * Function PlayerQuitEvent
			 * @param PlayerQuitEvent $event
			 * @return void
			 */
			public function PlayerQuitEvent(PlayerQuitEvent $event): void{
				$player = $event->getPlayer();
				if ($player instanceof xPlayer && !is_null($player->setup)) {
					if ($player->setup->id == $this->setup->id) {
						$this->setup->leave();
					}
				}
			}
		}, EventPriority::MONITOR, new MethodEventExecutor("PlayerQuitEvent"), BuildFFA::getInstance());
		foreach ($events as $eventClass => $eventListener) {
			if (class_exists($eventClass) && method_exists($eventClass, "getPlayer")) {
				$plugin_manager->registerEvent($eventClass, new class($this, $eventListener) implements Listener{
					/**
					 * Anonymous constructor.
					 * @param Setup $setup
					 * @param Closure $eventListener
					 */
					public function __construct(private Setup $setup, private Closure $eventListener){
					}

					/**
					 * Function Event
					 * @param Event $event
					 * @return void
					 */
					public function Event($event): void{
						if (method_exists($event, "getPlayer")) {
							$player = $event->getPlayer();
							if ($player instanceof xPlayer && !is_null($player->setup) && $player->setup->id == $this->setup->id) {
								if ($event instanceof Cancellable && method_exists($event, "cancel")) {
									$event->cancel();
								}
								if ($player->isSneaking()) {
									$this->setup->sendMessage("Ignored.");
									return;
								}
								($this->eventListener)($event);
								if ($event instanceof Cancellable) {
									$event->cancel();
								}
							}
						}
					}
				}, EventPriority::MONITOR, new MethodEventExecutor("Event"), BuildFFA::getInstance());
			}
		}
		$player->setGamemode(Player::CREATIVE);
		$player->setFlying(true);
		$player->teleport($this->world->getSafeSpawn());
	}

	/**
	 * Function sendMessage
	 * @param string $message
	 * @return void
	 */
	public function sendMessage(string $message): void{
		$this->player->sendMessage(self::PREFIX . $message);
	}

	/**
	 * Function leave
	 * @return void
	 */
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

	/**
	 * Function nextStage
	 * @return void
	 */
	public function nextStage(): void{
		$this->currentStage++;
		if ($this->currentStage == $this->maxStage + 1) {
			$this->saveConfiguration();
			$this->player->teleport($this->player->getServer()->getDefaultLevel()->getSafeSpawn());
			if ($this->world->getFolderName() != Server::getInstance()->getDefaultLevel()->getFolderName()) {
				$worldName = $this->world->getFolderName();
				Server::getInstance()->unloadLevel($this->world);
				Server::getInstance()->loadLevel($worldName);
				$this->world = Server::getInstance()->getLevelByName($worldName);
			} else {
				$this->sendMessage("Please restart server!");
			}
			$packet = new PlaySoundPacket();
			$packet->soundName = "block.composter.fill_success";
			$packet->x = $this->player->getPosition()->x;
			$packet->y = $this->player->getPosition()->y;
			$packet->z = $this->player->getPosition()->z;
			$packet->pitch = $packet->volume = 1;
			$this->player->sendDataPacket($packet);
			$this->sendMessage("Setup done!");
			Game::getInstance()->addArena(new Arena($this->world, new ArenaSettings($this->configuration)));
			$this->player->setup = null;
		} else {
			$packet = new PlaySoundPacket();
			$packet->soundName = "block.composter.fill";
			$packet->x = $this->player->getPosition()->x;
			$packet->y = $this->player->getPosition()->y;
			$packet->z = $this->player->getPosition()->z;
			$packet->pitch = $packet->volume = 1;
			$this->player->sendDataPacket($packet);
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
		return new Position($position->x + $x, $position->y + $y, $position->z + $z, $position->getLevel());
	}
}
