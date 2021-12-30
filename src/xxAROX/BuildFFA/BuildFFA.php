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


/**
 * Class BuildFFA
 * @package xxAROX\BuildFFA
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:06
 * @ide PhpStorm
 * @project BuildFFA
 */
class BuildFFA extends PluginBase{
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
	}

	protected function onEnable(): void{
		$config = $this->getConfig()->getAll();
		$worlds = [];
		foreach ($config["worlds"] ?? [$this->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()] as $worldName) {
			if ($this->getServer()->getWorldManager()->loadWorld($worldName)) {
				$world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
			}
		}
	}

	protected function onDisable(): void{
	}
}
