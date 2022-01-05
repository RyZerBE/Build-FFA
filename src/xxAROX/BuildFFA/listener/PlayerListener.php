<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use xxAROX\BuildFFA\event\EnterArenaProtectionAreaEvent;
use xxAROX\BuildFFA\event\LeaveArenaProtectionAreaEvent;
use xxAROX\BuildFFA\game\Game;
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
		$player->teleport(Game::getInstance()->getArena()->getWorld()->getSafeSpawn());
		$player->sendOtakaItems();
	}
	public function PlayerExhaustEvent(PlayerExhaustEvent $event): void{
		if ($event->getPlayer()->getHungerManager()->isEnabled()) {
			$event->getPlayer()->getHungerManager()->setEnabled(false);
			$event->getPlayer()->getHungerManager()->setFood($event->getPlayer()->getHungerManager()->getMaxFood());
		}
	}
	public function PlayerMoveEvent(PlayerMoveEvent $event): void{
		/** @var xPlayer $player */
		$player = $event->getPlayer();

		if (Game::getInstance()->getArena()->isInProtectionArea($event->getFrom()) && !Game::getInstance()->getArena()->isInProtectionArea($event->getTo())) {
			$ev = new LeaveArenaProtectionAreaEvent($player, Game::getInstance()->getArena());
			$ev->call();
			if ($player->is_in_inv_sort) {
				$player->is_in_inv_sort = false;
			}
			$player->giveKit($player->getSelectedKit());
		}
		if (!Game::getInstance()->getArena()->isInProtectionArea($event->getFrom()) && Game::getInstance()->getArena()->isInProtectionArea($event->getTo())) {
			$ev = new EnterArenaProtectionAreaEvent($player, Game::getInstance()->getArena());
			$ev->call();
			$player->sendOtakaItems();
			if (!$player->allow_no_fall_damage) {
				$player->allow_no_fall_damage = true;
			}
		}
	}

	public function EntityTeleportEvent(EntityTeleportEvent $event): void{
		/** @var xPlayer $player */
		if (!($player = $event->getEntity()) instanceof xPlayer) {
			return;
		}
		if ($event->isCancelled()) {
			return;
		}
		if (Game::getInstance()->getArena()->isInProtectionArea($event->getFrom()) && !Game::getInstance()->getArena()->isInProtectionArea($event->getTo())) {
			$ev = new LeaveArenaProtectionAreaEvent($player, Game::getInstance()->getArena(), !$event->isCancelled());
			$ev->call();
			if ($player->is_in_inv_sort) {
				$player->is_in_inv_sort = false;
			}
			if ($player->allow_no_fall_damage) {
				$player->allow_no_fall_damage = false;
			}
		} else if (!Game::getInstance()->getArena()->isInProtectionArea($event->getFrom()) && Game::getInstance()->getArena()->isInProtectionArea($event->getTo())) {
			$ev = new EnterArenaProtectionAreaEvent($player, Game::getInstance()->getArena(), !$event->isCancelled());
			$ev->call();
			if (!$player->allow_no_fall_damage) {
				$player->allow_no_fall_damage = true;
			}
			$player->sendOtakaItems();
		}
	}

	public function PlayerQuitEvent(PlayerQuitEvent $event): void{
		/** @var xPlayer $player */
		$player = $event->getPlayer();

		if (!empty($player->voted_map)) {
			Game::getInstance()->mapVotes[$player->voted_map]--;
		}
		$player->store();
	}
}
