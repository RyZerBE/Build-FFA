<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA;
use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use xxAROX\BuildFFA\command\SetupCommand;
use xxAROX\BuildFFA\command\SkipCommand;
use xxAROX\BuildFFA\entity\BlockEntity;
use xxAROX\BuildFFA\game\Arena;
use xxAROX\BuildFFA\game\ArenaSettings;
use xxAROX\BuildFFA\game\Game;
use xxAROX\BuildFFA\items\overwrite\EnderPearl;
use xxAROX\BuildFFA\listener\BlockListener;
use xxAROX\BuildFFA\listener\PlayerListener;


/**
 * Class BuildFFA
 * @package xxAROX\BuildFFA
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:06
 * @ide PhpStorm
 * @project BuildFFA
 */
class BuildFFA extends PluginBase{
	const TAG_SORT_TYPE              = "xxarox:inv:sort_type";
	const TAG_READONLY               = "xxarox:inv:readonly";
	const TAG_COUNTDOWN              = "xxarox:inv:countdown";
	const TAG_PLACEHOLDER_IDENTIFIER = "__placeholderId";
	use SingletonTrait;


	/**
	 * BuildFFA constructor.
	 * @param PluginLoader $loader
	 * @param Server $server
	 * @param PluginDescription $description
	 * @param string $dataFolder
	 * @param string $file
	 */
	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file){
		parent::__construct($loader, $server, $description, $dataFolder, $file);
		self::setInstance($this);
	}

	/**
	 * Function onLoad
	 * @return void
	 */
	public function onLoad(): void{
		include_once dirname(__DIR__) . "/../functions.php";
	}

	/**
	 * Function onEnable
	 * @return void
	 */
	public function onEnable(): void{
		$this->registerPermissions();
		$this->registerCommands();
		$this->registerItems();
		$this->registerListeners();
		$this->registerEntities();
		$arena_data = $this->getConfig()->getAll();
		$arenas = [];
		foreach ($arena_data as $worldName => $obj) {
			if ($this->getServer()->loadLevel($worldName)) {
				$this->getLogger()->info("§3Preparing map $worldName..");
				$world = $this->getServer()->getLevelByName($worldName);
				$world->setTime(Level::TIME_NOON);
				$world->stopTime();
				$world->setAutoSave(false);
				$arenas[] = new Arena($world, new ArenaSettings($obj));
			}
		}
		new Game($arenas);
	}

	/**
	 * Function registerPermissions
	 * @return void
	 */
	private function registerPermissions(): void{
		PermissionManager::getInstance()->addPermission(new Permission("game.setup", "Allow /setup"));
		PermissionManager::getInstance()->addPermission(new Permission("game.buildffa.map.skip", "Allow /skip"));
		PermissionManager::getInstance()->addPermission(new Permission("game.buildffa.settings", "Allow /settings"));
	}

	/**
	 * Function registerCommands
	 * @return void
	 */
	private function registerCommands(): void{
		$this->getServer()->getCommandMap()->registerAll(strtoupper($this->getName()), [
			new SetupCommand(),
			new SkipCommand(),
		]);
	}

	/**
	 * Function registerItems
	 * @return void
	 */
	private function registerItems(): void{
		ItemFactory::registerItem(new EnderPearl(), true);
	}

	/**
	 * Function registerListeners
	 * @return void
	 */
	private function registerListeners(): void{
		// NOTE: custom events for this plugin
		// $this->getServer()->getPluginManager()->registerEvents(new Listener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new BlockListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
	}

	/**
	 * Function registerEntities
	 * @return void
	 */
	private function registerEntities(): void{
		Entity::registerEntity(BlockEntity::class, true, ["buildffa:falling_block"]);
	}

	/**
	 * Function onDisable
	 * @return void
	 */
	public function onDisable(): void{
	}
}
