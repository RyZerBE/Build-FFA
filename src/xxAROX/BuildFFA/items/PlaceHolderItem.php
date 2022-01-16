<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\items;
use Closure;
use DaveRandom\CallbackValidator\BuiltInTypes;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use Ramsey\Uuid\Uuid;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class PlaceHolderItem
 * @package xxAROX\BuildFFA\items
 * @author Jan Sohn / xxAROX
 * @date 06. Januar, 2022 - 21:35
 * @ide PhpStorm
 * @project BuildFFA
 */
class PlaceHolderItem extends Item{
	use NonPlaceableItemTrait;


	private string $placeholderIdentifier;

	public function __construct(ItemIdentifier $identifier, protected Item $placeholdersItem, protected int $countdown = 0, protected ?Closure $allowItemCooldown = null){
		if (!is_null($this->allowItemCooldown)) {
			Utils::validateCallableSignature(new CallbackType(new ReturnType(BuiltInTypes::BOOL), new ParameterType("player", xPlayer::class)), $this->allowItemCooldown);
		}
		$this->placeholderIdentifier = Uuid::uuid4()->toString();
		$this->placeholdersItem->getNamedTag()->setInt(BuildFFA::TAG_COUNTDOWN, $this->countdown);
		parent::__construct($identifier, "Placeholder:{$identifier->getId()}:{$identifier->getMeta()}");
		$this->setCustomName("§cNo {$placeholdersItem->getVanillaName()}");
		applyReadonlyTag($this);
		$this->placeholdersItem->setNamedTag($this->placeholdersItem->getNamedTag()->setString("__placeholderId", $this->placeholderIdentifier));
		$this->setNamedTag($this->getNamedTag()->setString("__placeholderId", $this->placeholderIdentifier));
	}

	/**
	 * Function allowItemCooldown
	 * @param xPlayer $player
	 * @return bool
	 */
	public function allowItemCooldown(xPlayer $player): bool{
		return is_null($this->allowItemCooldown) ? true : ($this->allowItemCooldown)($player);
	}

	/**
	 * Function getPlaceholderIdentifier
	 * @return string
	 */
	public function getPlaceholderIdentifier(): string{
		return $this->placeholderIdentifier;
	}

	/**
	 * Function hasCountdown
	 * @return bool
	 */
	public function hasCountdown(): bool{
		return ($this->countdown = $this->placeholdersItem->getNamedTag()->getInt(BuildFFA::TAG_COUNTDOWN, 0)) > 0;
	}

	/**
	 * Function getCountdown
	 * @return int
	 */
	public function getCountdown(): int{
		return $this->countdown;
	}

	/**
	 * Function getPlaceholdersItem
	 * @return Item
	 */
	public function getPlaceholdersItem(): Item{
		return $this->placeholdersItem;
	}

	/**
	 * Function onClickAir
	 * @param Player $player
	 * @param Vector3 $directionVector
	 * @return ItemUseResult
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
		return ItemUseResult::FAIL();
	}
}
