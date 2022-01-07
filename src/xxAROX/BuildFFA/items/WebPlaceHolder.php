<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\items;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;


/**
 * Class WebPlaceHolder
 * @package xxAROX\BuildFFA\items
 * @author Jan Sohn / xxAROX
 * @date 06. Januar, 2022 - 21:29
 * @ide PhpStorm
 * @project BuildFFA
 */
class WebPlaceHolder extends Item{
	use NonPlaceableItemTrait;


	/**
	 * WebPlaceHolder constructor.
	 */
	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemIds::WEB, 0), "Web placeholder");
		$this->setCustomName("§cNo webs"); // TODO: language stuff
	}
}
