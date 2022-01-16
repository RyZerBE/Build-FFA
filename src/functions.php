<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
use JetBrains\PhpStorm\Pure;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldException;
use xxAROX\BuildFFA\BuildFFA;


/**
 * Function encodeItem
 * @param Item $item
 * @return string
 */
function encodeItem(Item $item): string{
	return "{$item->getVanillaName()}:{$item->getId()}:{$item->getMeta()}";
}

/**
 * Function encodePosition
 * @param Position $position
 * @return string
 */
#[Pure] function encodePosition(Position $position): string{
	return "$position->x:$position->y:$position->z:{$position->world->getFolderName()}";
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
	$world = Server::getInstance()->getWorldManager()->getWorldByName($ex[3]);
	if ($world instanceof World) {
		return new Position(floatval($ex[0]), floatval($ex[1]), floatval($ex[2]), $world);
	}
	throw new WorldException("World $ex[3] doesn't exists");
}

/**
 * Function getLogger
 * @return Logger
 */
function getLogger(): Logger{
	if (Server::getInstance()->getPluginManager()->getPlugin("BuildFFA") instanceof BuildFFA) {
		return BuildFFA::getInstance()->getLogger();
	} else {
		return GlobalLogger::get();
	}
}

/**
 * Function applyReadonlyTag
 * @param Item $item
 * @param bool $readonly
 * @return Item
 */
function applyReadonlyTag(Item $item, bool $readonly = true): Item{
	$item->setNamedTag($item->getNamedTag()->setByte(BuildFFA::TAG_READONLY, intval($readonly)));
	return $item;
}