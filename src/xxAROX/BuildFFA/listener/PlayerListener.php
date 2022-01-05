<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\listener;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerLoginEvent;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class PlayerListener
 * @package xxAROX\BuildFFA\listener
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:20
 * @ide PhpStorm
 * @project BuildFFA
 */
class PlayerListener implements Listener{
	public function PlayerCreationEvent(PlayerCreationEvent $event): void{
		$event->setPlayerClass(xPlayer::class);
	}
	public function PlayerLoginEvent(PlayerLoginEvent $event): void{
		/** @var xPlayer $player */
		$player = $event->getPlayer();
		$player->load(0, 0);
	}
	public function PlayerExhaustEvent(PlayerExhaustEvent $event): void{
		if ($event->getPlayer()->getHungerManager()->isEnabled()) {
			$event->getPlayer()->getHungerManager()->setEnabled(false);
			$event->getPlayer()->getHungerManager()->setFood($event->getPlayer()->getHungerManager()->getMaxFood());
		}
	}
}
