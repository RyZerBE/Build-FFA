<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\player;
use Frago9876543210\EasyForms\elements\FunctionalButton;
use Frago9876543210\EasyForms\forms\MenuForm;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\sound\EntityLandSound;
use pocketmine\world\sound\EntityLongFallSound;
use pocketmine\world\sound\EntityShortFallSound;
use xxAROX\BuildFFA\event\BuildFFAPlayerChangeInvSortEvent;
use xxAROX\BuildFFA\event\BuildFFAPlayerRespawnEvent;
use xxAROX\BuildFFA\game\Arena;
use xxAROX\BuildFFA\game\Game;
use xxAROX\BuildFFA\game\Kit;
use xxAROX\BuildFFA\items\InvSortItem;
use xxAROX\BuildFFA\items\KitItem;
use xxAROX\BuildFFA\items\MapItem;
use xxAROX\BuildFFA\items\SpectateItem;


/**
 * Class xPlayer
 * @package xxAROX\BuildFFA\player
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:50
 * @ide PhpStorm
 * @project BuildFFA
 */
class xPlayer extends Player{
	protected int $kill_streak = 0;
	protected int $deaths = 0;
	protected int $kills = 0;
	protected ?Kit $selected_kit = null;
	protected array $inv_sort = [
		"sword"   => 0,
		"pickaxe" => 1,
		"stick"   => 2,
		"web"     => 3,
	];
	// NOTE: this is for internal api stuff
	/** @internal */
	public bool $is_in_inv_sort = false;
	/** @internal */
	public bool $allow_no_fall_damage = true;
	public string $voted_map = "";

	/**
	 * Function load
	 * @param int $kills
	 * @param int $deaths
	 * @param null|array $inv_sort
	 * @param null|string $kit_name
	 * @return void
	 */
	public function load(int $kills, int $deaths, ?array $inv_sort = null, ?string $kit_name = null): void{
		$this->kills = $kills;
		$this->deaths = $deaths;
		$this->kill_streak = 0;
		$this->inv_sort = $inv_sort ?? $this->inv_sort;
		$this->selected_kit = Game::getInstance()->getKit($kit_name);
	}

	/**
	 * Function store
	 * @return void
	 */
	public function store(): void{
		// TODO: @Baubo-LP
	}

	/**
	 * Function giveKit
	 * @param Kit $kit
	 * @return void
	 */
	public function giveKit(Kit $kit): void{
		$kit->equip($this);
	}

	/**
	 * Function sendOtakaItems
	 * @return void
	 */
	public function sendOtakaItems(){
		$this->inventory->clearAll();
		$this->armorInventory->clearAll();
		$this->offHandInventory->clearAll();
		$this->cursorInventory->clearAll();
		for ($slot=9; $slot<$this->inventory->getSize(); $slot++) {
			$this->inventory->setItem($slot, VanillaBlocks::BARRIER()->asItem()->setCustomName("§r"));
		}
		$this->inventory->setItem(0, new InvSortItem());
		$this->inventory->setItem(1, new MapItem());
		$this->inventory->setItem(4, new KitItem());
		$this->inventory->setItem(7, new SpectateItem());
	}

	/**
	 * Function toggleSneak
	 * @param bool $sneak
	 * @return bool
	 */
	public function toggleSneak(bool $sneak): bool{
		if ($this->is_in_inv_sort && !$sneak) {
			$newSort = [];
			$kitContents = $this->selected_kit->getContents();
			foreach ($this->selected_kit->getContents() as $type => $item) {
				for ($hotbar_slot = 0; $hotbar_slot < $this->inventory->getHotbarSize(); $hotbar_slot++) {
					$hotbar_item = $this->inventory->getItem($hotbar_slot);
					if ($hotbar_item->equals($kitContents[$type], true, false) && ($this->inv_sort[$type] ?? -1) != $hotbar_slot) {
						$newSort[$type] = $hotbar_slot;
					}
				}
			}
			$ev1 = new BuildFFAPlayerChangeInvSortEvent($this, $this->inv_sort, $newSort);
			$ev1->call();
			if (!$ev1->isCancelled()) {
				foreach ($newSort as $type => $slot) {
					$this->inv_sort[$type] = $slot;
				}
			}
			$this->is_in_inv_sort = false;
			$this->sendOtakaItems();
		}
		return parent::toggleSneak($sneak);
	}

	public function sendMapSelect(): void{
		if (count(Game::getInstance()->getArenas()) == 0) {
			$this->sendMessage("§cLazy owner(ping him), no maps found..");// TODO: language stuff
			return;
		}
		if (count(Game::getInstance()->getArenas()) == 1) {
			$this->sendMessage("§cOnly one map found, you have no choice..");// TODO: language stuff
			return;
		}
		$this->sendForm(new MenuForm(
			"%ui.title.voting.map",
			"",
			array_map(fn (Arena $arena) => new FunctionalButton($arena->getWorld()->getFolderName(), function (xPlayer $player) use ($arena): void{
				if ($arena->getWorld()->getFolderName() == $player->voted_map) {
					Game::getInstance()->mapVotes[$player->voted_map]--;
				} else {
					if (!empty($player->voted_map)) {
						Game::getInstance()->mapVotes[$player->voted_map]--;
					}
					Game::getInstance()->mapVotes[$player->voted_map]++;
				}
			}), Game::getInstance()->getArenas())
		));
	}

	public function sendKitSelect(): void{
		// TODO
	}

	public function spectate(): void{
		$this->sendMessage("§e// TODO: implement spectator mode");
		$this->setGamemode(GameMode::SPECTATOR());
	}

	/**
	 * Function onDeath
	 * @return void
	 */
	protected function onDeath(): void{
		$this->removeCurrentWindow();
		$ev = new PlayerDeathEvent($this, $this->getDrops(), $this->getXpDropAmount(), null);
		$ev->call();
		if (!$ev->getKeepInventory()) {
			if ($this->inventory !== null) {
				$this->inventory->clearAll();
			}
			if ($this->armorInventory !== null) {
				$this->armorInventory->clearAll();
			}
			if ($this->offHandInventory !== null) {
				$this->offHandInventory->clearAll();
			}
		}
		$this->xpManager->setXpAndProgress(0, 0.0);
		//TODO: death message
		//$this->server->broadcastMessage($ev->getDeathMessage());
		$this->startDeathAnimation();
		$this->setHealth($this->getMaxHealth());
		$this->__respawn();
	}

	/**
	 * Function entityBaseTick
	 * @param int $tickDiff
	 * @return bool
	 */
	protected function entityBaseTick(int $tickDiff = 1): bool{
		if ($this->getPosition()->y <= Game::getInstance()->getArena()->getSettings()->respawn_height) {
			$this->__respawn();
		}
		return parent::entityBaseTick($tickDiff);
	}

	/**
	 * Function __respawn
	 * @return void
	 */
	public function __respawn(): void{
		$ev = new BuildFFAPlayerRespawnEvent($this, Game::getInstance()->getArena());
		$ev->call();
		$this->setHealth($this->getMaxHealth());
		$newSort = [];
		$kitContents = $this->selected_kit->getContents();
		foreach ($this->selected_kit->getContents() as $type => $item) {
			for ($hotbar_slot = 0; $hotbar_slot < $this->inventory->getHotbarSize(); $hotbar_slot++) {
				$hotbar_item = $this->inventory->getItem($hotbar_slot);
				if ($hotbar_item->equals($kitContents[$type], true, false) && ($this->inv_sort[$type] ?? -1) != $hotbar_slot) {
					$newSort[$type] = $hotbar_slot;
				}
			}
		}
		$ev1 = new BuildFFAPlayerChangeInvSortEvent($this, $this->inv_sort, $newSort);
		$ev1->call();
		if (!$ev1->isCancelled()) {
			foreach ($newSort as $type => $slot) {
				$this->inv_sort[$type] = $slot;
			}
		}
		if (!$ev->isCancelled()) {
			$this->teleport(Game::getInstance()->getArena()->getWorld()->getSafeSpawn());
			$this->sendOtakaItems();
		}
	}

	/**
	 * Function onHitGround
	 * @return null|float
	 */
	protected function onHitGround(): ?float{
		$fallBlockPos = $this->location->floor();
		$fallBlock = $this->getWorld()->getBlock($fallBlockPos);
		if (count($fallBlock->getCollisionBoxes()) === 0) {
			$fallBlockPos = $fallBlockPos->down();
			$fallBlock = $this->getWorld()->getBlock($fallBlockPos);
		}
		$newVerticalVelocity = $fallBlock->onEntityLand($this);
		$damage = $this->calculateFallDamage($this->fallDistance);
		if ($damage > 0) {
			if ($this->allow_no_fall_damage) {
				$this->allow_no_fall_damage = false;
				return $newVerticalVelocity;
			}
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev);
			$this->broadcastSound($damage > 4 ? new EntityLongFallSound($this) : new EntityShortFallSound($this));
		} else if ($fallBlock->getId() !== BlockLegacyIds::AIR) {
			$this->broadcastSound(new EntityLandSound($this, $fallBlock));
		}
		return $newVerticalVelocity;
	}

	/**
	 * Function getInvSort
	 * @return array
	 */
	public function getInvSort(): array{
		return $this->inv_sort;
	}

	/**
	 * Function setInvSort
	 * @param array|int[] $inv_sort
	 * @return void
	 */
	public function setInvSort(array $inv_sort): void{
		$this->inv_sort = $inv_sort;
	}

	/**
	 * Function setSelectedKit
	 * @param null|Kit $selected_kit
	 * @return void
	 */
	public function setSelectedKit(?Kit $selected_kit): void{
		$this->selected_kit = $selected_kit;
	}

	/**
	 * Function getSelectedKit
	 * @return ?Kit
	 */
	public function getSelectedKit(): ?Kit{
		return $this->selected_kit;
	}
}
