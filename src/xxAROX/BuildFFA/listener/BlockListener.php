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
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;
use xxAROX\BuildFFA\game\Game;


/**
 * Class BlockListener
 * @package xxAROX\BuildFFA\listener
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:21
 * @ide PhpStorm
 * @project BuildFFA
 */
class BlockListener implements Listener{
	public function BlockBreakEvent(BlockBreakEvent $event): void{
		if (Game::getInstance()->filterPlayer($event->getPlayer())) {
			Game::getInstance()->breakBlock($event->getBlock());
			$event->setDrops([]);
		}
	}
	public function BlockPlaceEvent(BlockPlaceEvent $event): void{
		if (!boolval($event->getItem()->getNamedTag()->getByte("pop", intval(false)))) {
			$event->getItem()->pop();
			$event->getItem()->setCount($event->getItem()->getCount() +1);
			$event->getPlayer()->getInventory()->setItemInHand($event->getItem());
		}
		if (Game::getInstance()->filterPlayer($event->getPlayer())) {
			Game::getInstance()->placeBlock($event->getBlock());
		}
	}
}
