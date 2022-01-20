<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\game;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Server;
use xxAROX\BuildFFA\event\MapChangeEvent;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class Arena
 * @package xxAROX\BuildFFA\game
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:47
 * @ide PhpStorm
 * @project BuildFFA
 */
class Arena{
	private bool $active = false;

	/**
	 * Arena constructor.
	 * @param Level $world
	 * @param ArenaSettings $settings
	 */
	public function __construct(private Level $world, private ArenaSettings $settings){
	}

	/**
	 * Function getActive
	 * @return bool
	 */
	public function isActive(): bool{
		return $this->active;
	}

	/**
	 * Function setActive
	 * @param bool $active
	 * @return void
	 */
	public function setActive(bool $active): void{
		$this->active = $active;
		if ($this->active) {
			$ev = new MapChangeEvent();
			$ev->call();
			foreach (Game::getInstance()->mapVotes as $k => $_) {
				Game::getInstance()->mapVotes[$k] = 0;
			}
			$block_speed = Game::getInstance()->getArena()->settings->blocks_cooldown;
			/** @var xPlayer $onlinePlayer */
			foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
				unset($onlinePlayer->itemCountdowns);
				$onlinePlayer->itemCountdowns = [];
				$onlinePlayer->teleport($this->world->getSafeSpawn());
				$onlinePlayer->voted_map = "";
				$onlinePlayer->sendOtakaItems();
			}
			foreach (Game::getInstance()->getArenas() as $arena) {
				if ($arena->isActive()) {
					$arena->setActive(false);
				}
			}
		} else {
			/*$worldManager = $this->world->getServer()->getWorldManager();
			$worldName = $this->world->getFolderName();
			if ($worldManager->getDefaultWorld()->getFolderName() !== $worldName) {
				$worldManager->unloadWorld($this->world);
				$worldManager->loadWorld($worldName);
			}*/
		}
	}

	/**
	 * Function getWorld
	 * @return Level
	 */
	public function getWorld(): Level{
		return $this->world;
	}

	/**
	 * Function getSettings
	 * @return ArenaSettings
	 */
	public function getSettings(): ArenaSettings{
		return $this->settings;
	}

	/**
	 * Function isInProtectionArea
	 * @param Vector3 $vector3
	 * @return bool
	 */
	public function isInProtectionArea(Vector3 $vector3): bool{
		return $this->world->getSpawnLocation()->asVector3()->distance($vector3) <= $this->settings->protection;
	}
}
