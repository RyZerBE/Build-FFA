<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\player;
use pocketmine\player\Player;
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
	protected array $inv_sort = [];

	/**
	 * Function load
	 * @param int $kills
	 * @param int $deaths
	 * @param array $inv_sort
	 * @return void
	 */
	public function load(int $kills, int $deaths, array $inv_sort): void{
		$this->kills = $kills;
		$this->deaths = $deaths;
		$this->kill_streak = 0;
		$this->inv_sort = $inv_sort;
	}

	public function giveKit(Kit $kit): void{
		$kit->equip($this);
	}
}
