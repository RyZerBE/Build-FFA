<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\plugin\ResourceProvider;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use xxAROX\BuildFFA\game\Arena;
use xxAROX\BuildFFA\game\ArenaSettings;
use xxAROX\BuildFFA\game\Game;
use xxAROX\BuildFFA\listener\BlockListener;


/**
 * Class BuildFFA
 * @package xxAROX\BuildFFA
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:06
 * @ide PhpStorm
 * @project BuildFFA
 */
class BuildFFA extends PluginBase{
	const IS_DEVELOPMENT = true;
	use SingletonTrait;


	/**
	 * BuildFFA constructor.
	 * @param PluginLoader $loader
	 * @param Server $server
	 * @param PluginDescription $description
	 * @param string $dataFolder
	 * @param string $file
	 * @param ResourceProvider $resourceProvider
	 */
	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file, ResourceProvider $resourceProvider){
		parent::__construct($loader, $server, $description, $dataFolder, $file, $resourceProvider);
		self::setInstance($this);
	}

	protected function onLoad(): void{
		include_once dirname(__DIR__) . "/../functions.php";
	}

	protected function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents(new Listener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new BlockListener(), $this);
		$arena_data = $this->getConfig()->getAll();
		$arenas = [];
		foreach ($arena_data as $worldName => $obj) {
			if ($this->getServer()->getWorldManager()->loadWorld($worldName)) {
				$this->getLogger()->info("§3Preparing map $worldName..");
				$world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
				$world->setTime(World::TIME_NOON);
				$world->stopTime();
				$world->setAutoSave(false);
				$arenas[] = new Arena($world, new ArenaSettings($obj));
			}
		}
		new Game($arenas);
	}

	protected function onDisable(): void{
	}
}
