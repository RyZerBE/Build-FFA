<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\listener;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\player\GameMode;
use xxAROX\BuildFFA\BuildFFA;
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
	/**
	 * Function PlayerCreationEvent
	 * @param PlayerCreationEvent $event
	 * @return void
	 */
	public function PlayerCreationEvent(PlayerCreationEvent $event): void{
		$event->setPlayerClass(xPlayer::class);
	}

	/**
	 * Function PlayerLoginEvent
	 * @param PlayerLoginEvent $event
	 * @return void
	 */
	public function PlayerLoginEvent(PlayerLoginEvent $event): void{
		/** @var xPlayer $player */
		$player = $event->getPlayer();
		$player->load(0, 0);
		$player->teleport(Game::getInstance()->getArena()->getWorld()->getSafeSpawn());
		$player->sendOtakaItems();
	}

	/**
	 * Function PlayerExhaustEvent
	 * @param PlayerExhaustEvent $event
	 * @return void
	 */
	public function PlayerExhaustEvent(PlayerExhaustEvent $event): void{
		if ($event->getPlayer()->getHungerManager()->isEnabled()) {
			$event->getPlayer()->getHungerManager()->setEnabled(false);
			$event->getPlayer()->getHungerManager()->setFood($event->getPlayer()->getHungerManager()->getMaxFood());
		}
	}

	/**
	 * Function PlayerMoveEvent
	 * @param PlayerMoveEvent $event
	 * @return void
	 */
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

	/**
	 * Function EntityTeleportEvent
	 * @param EntityTeleportEvent $event
	 * @return void
	 */
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

	/**
	 * Function PlayerQuitEvent
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	public function PlayerQuitEvent(PlayerQuitEvent $event): void{
		/** @var xPlayer $player */
		$player = $event->getPlayer();
		if (!empty($player->voted_map)) {
			/** @noinspection PhpExpressionResultUnusedInspection */
			Game::getInstance()->mapVotes[$player->voted_map]--;
		}
		$player->store();
	}

	/**
	 * Function PlayerItemHeldEvent
	 * @param PlayerItemHeldEvent $event
	 * @return void
	 */
	public function PlayerItemHeldEvent(PlayerItemHeldEvent $event): void{
		/** @var xPlayer $player */
		$player = $event->getPlayer();
		if ($event->getPlayer()->getGamemode()->id() == GameMode::SPECTATOR()->id() && $event->getItem()->getId() == VanillaBlocks::IRON_DOOR()->asItem()->getId()) {
			$player->__respawn();
		}
	}

	public function InventoryTransactionEvent(InventoryTransactionEvent $event): void{
		if (!Game::getInstance()->filterPlayer($event->getTransaction()->getSource())) {
			return;
		}
		foreach ($event->getTransaction()->getActions() as $action) {
			if ($action instanceof DropItemAction) {
				$event->cancel();
				return;
			}
			if (!$action instanceof SlotChangeAction) {
				return;
			}
			if ($action->getInventory() instanceof PlayerOffHandInventory) {
				$event->cancel();
				return;
			}
			if ($action->getInventory() instanceof ArmorInventory) {
				$event->cancel();
				return;
			}
			/** @var Item $item */
			foreach ([$action->getSourceItem(), $action->getTargetItem()] as $item) {
				if (boolval($item->getNamedTag()->getByte(BuildFFA::TAG_READONLY, intval(false)))) {
					$event->cancel();
				}
			}
			if ($action->getInventory() instanceof PlayerInventory && !$event->isCancelled()) {
				if (!$event->isCancelled()) {
					/** @noinspection PhpPossiblePolymorphicInvocationInspection */
					$event->getTransaction()->getSource()->saveInvSort();
				}
			}
		}
	}

	public function PlayerInteractEvent(PlayerInteractEvent $event): void{
		/** @var xPlayer $player */
		$player = $event->getPlayer();
		$item = $event->getItem();
		$placeholder = $player->getSelectedKit()->getPlaceholderByIdentifier($item->getNamedTag()->getString("__placeholderId", ""));
		if (!is_null($placeholder) && $item->getCount() == 1 && !$item->equals($placeholder, true, false)) {
			if ($placeholder->allowItemCooldown($player)) {
				$player->itemCooldown($item);
			}
		}
	}
}
