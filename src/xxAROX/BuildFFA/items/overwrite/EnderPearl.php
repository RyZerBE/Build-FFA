<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\BuildFFA\items\overwrite;
use pocketmine\entity\Location;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\ThrowSound;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class EnderPearl
 * @package xxAROX\BuildFFA\items\overwrite
 * @author Jan Sohn / xxAROX
 * @date 07. Januar, 2022 - 15:37
 * @ide PhpStorm
 * @project BuildFFA
 */
class EnderPearl extends \pocketmine\item\EnderPearl{
	/**
	 * EnderPearl constructor.
	 */
	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemIds::ENDER_PEARL, 0), "Enderpearl");
	}

	/**
	 * Function onClickAir
	 * @param xPlayer $player
	 * @param Vector3 $directionVector
	 * @return ItemUseResult
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
		$location = $player->getLocation();
		var_dump(get_class($player->getInventory()->getItemInHand()->getNamedTag()));
		$player->itemCooldown($player->getInventory()->getItemInHand());
		//if ($player->has)

		$projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
		$projectile->setMotion($directionVector->multiply($this->getThrowForce()));

		$projectileEv = new ProjectileLaunchEvent($projectile);
		$projectileEv->call();
		if($projectileEv->isCancelled()){
			$projectile->flagForDespawn();
			return ItemUseResult::FAIL();
		}

		$projectile->spawnToAll();

		$location->getWorld()->addSound($location, new ThrowSound());

		$this->pop();

		return ItemUseResult::SUCCESS();
	}
}
