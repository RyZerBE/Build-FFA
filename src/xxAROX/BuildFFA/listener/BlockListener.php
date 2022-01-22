<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use xxAROX\BuildFFA\game\Game;
use function random_int;


/**
 * Class BlockListener
 * @package xxAROX\BuildFFA\listener
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:21
 * @ide PhpStorm
 * @project BuildFFA
 */
class BlockListener implements Listener{
	/**
	 * Function BlockBreakEvent
	 * @param BlockBreakEvent $event
	 * @return void
	 */
	public function BlockBreakEvent(BlockBreakEvent $event): void{
		if (Game::getInstance()->getArena()->isInProtectionArea($event->getBlock()->asPosition()->asVector3())) {
			$event->setCancelled();
			return;
		}
		/** @noinspection PhpParamsInspection */
		if (Game::getInstance()->filterPlayer($event->getPlayer())) {
			Game::getInstance()->breakBlock($event->getBlock());
			$event->setDrops([]);
		}
	}

	public function BlockPlaceEvent(BlockPlaceEvent $event): void{
		if (Game::getInstance()->getArena()->isInProtectionArea($event->getBlock()->asPosition()->asVector3())) {
			$event->setCancelled();
			return;
		}
		/** @noinspection PhpParamsInspection */
		if (Game::getInstance()->filterPlayer($event->getPlayer())) {
			if (!boolval($event->getItem()->getNamedTag()->getByte("pop", intval(false)))) {
				$event->getItem()->setCount((random_int(1, 3) == 1) ? $event->getItem()->getMaxStackSize()
					: $event->getItem()->getCount());
				$event->getPlayer()->getInventory()->setItemInHand($event->getItem());
			}
			Game::getInstance()->placeBlock($event->getBlock());
		}
	}
}
