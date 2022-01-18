<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\items\overwrite;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
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
	 * Function onClickAir
	 * @param xPlayer $player
	 * @param Vector3 $directionVector
	 * @return bool
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): bool{
		$nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight(), 0), $directionVector, $player->yaw, $player->pitch);
		$this->addExtraTags($nbt);
		$projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevelNonNull(), $nbt, $player);
		if ($projectile !== null) {
			$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
		}
		$this->pop();
		if ($projectile instanceof Projectile) {
			$projectileEv = new ProjectileLaunchEvent($projectile);
			$projectileEv->call();
			if ($projectileEv->isCancelled()) {
				$projectile->flagForDespawn();
			} else {
				$player->enderpearls[] = $projectile;
				$projectile->spawnToAll();
				$player->getLevelNonNull()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_THROW, 0, EntityIds::PLAYER);
			}
		} else if ($projectile !== null) {
			$projectile->spawnToAll();
		} else {
			return false;
		}
		return true;
	}
}
