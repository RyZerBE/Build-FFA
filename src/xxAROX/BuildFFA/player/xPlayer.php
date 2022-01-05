<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\player;
use pocketmine\player\Player;
use xxAROX\BuildFFA\event\BuildFFAPlayerChangeInvSortEvent;
use xxAROX\BuildFFA\event\BuildFFAPlayerRespawnEvent;
use xxAROX\BuildFFA\game\Game;
use xxAROX\BuildFFA\game\Kit;


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
		"web"   => 3,
	];

	/**
	 * Function load
	 * @param int $kills
	 * @param int $deaths
	 * @param array $inv_sort
	 * @param null|string $kit_name
	 * @return void
	 */
	public function load(int $kills, int $deaths, ?array $inv_sort = null, ?string $kit_name = null): void{
		$this->kills = $kills;
		$this->deaths = $deaths;
		$this->kill_streak = 0;
		$this->inv_sort = $inv_sort ?? $this->inv_sort;
		$this->selected_kit = Game::getInstance()->getKit($kit_name);
		$this->giveKit($this->selected_kit);
	}

	public function giveKit(Kit $kit): void{
		$kit->equip($this);
	}

	protected function entityBaseTick(int $tickDiff = 1): bool{
		if ($this->getPosition()->y <= Game::getInstance()->getArena()->getSettings()->respawn_height) {
			$ev = new BuildFFAPlayerRespawnEvent($this, Game::getInstance()->getArena());
			$ev->call();
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
				$this->giveKit($this->selected_kit);
			}
		}
		return parent::entityBaseTick($tickDiff);
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
