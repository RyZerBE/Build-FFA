<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
use DaveRandom\CallbackValidator\CallbackType;
use JetBrains\PhpStorm\Pure;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use xxAROX\BuildFFA\BuildFFA;


/**
 * Function encodeItem
 * @param Item $item
 * @return string
 */
function encodeItem(Item $item): string{
	return "{$item->getVanillaName()}:{$item->getId()}:{$item->getDamage()}";
}

/**
 * Function encodePosition
 * @param Position $position
 * @return string
 */
#[Pure] function encodePosition(Position $position): string{
	return "$position->x:$position->y:$position->z:{$position->level->getFolderName()}";
}

/**
 * Function decodePosition
 * @param string $string
 * @return Position
 */
function decodePosition(string $string): Position{
	$ex = explode(":", $string);
	if (!isset($ex[3])) {
		throw new LogicException("No world name is given in '$string'");
	}
	$world = Server::getInstance()->getLevelByName($ex[3]);
	if ($world instanceof Level) {
		return new Position(floatval($ex[0]), floatval($ex[1]), floatval($ex[2]), $world);
	}
	throw new LevelException("Level $ex[3] doesn't exists");
}

/**
 * Function applyReadonlyTag
 * @param Item $item
 * @param bool $readonly
 * @return Item
 */
function applyReadonlyTag(Item $item, bool $readonly = true): Item{
	$nbt = $item->getNamedTag();
	$nbt->setByte(BuildFFA::TAG_READONLY, intval($readonly));
	$item->setNamedTag($nbt);
	return $item;
}

/**
 * Verifies that the given callable is compatible with the desired signature. Throws a TypeError if they are
 * incompatible.
 *
 * @param callable $signature Dummy callable with the required parameters and return type
 * @param callable $subject Callable to check the signature of
 * @phpstan-param anyCallable $signature
 * @phpstan-param anyCallable $subject
 *
 * @throws \DaveRandom\CallbackValidator\InvalidCallbackException
 * @throws TypeError
 */
function validateCallableSignature(CallbackType|callable $signature, callable $subject) : void{
	if (is_callable($signature) || $signature instanceof Closure) {
		$signature = CallbackType::createFromCallable($signature);
	}
	if(!$signature->isSatisfiedBy($subject)){
		throw new TypeError("Declaration of callable `" . CallbackType::createFromCallable($subject) . "` must be compatible with `" . $signature->__toString() . "`");
	}
}