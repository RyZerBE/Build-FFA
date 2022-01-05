<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
/**
 * Function encodePosition
 * @param \pocketmine\world\Position $position
 * @return string
 */
#[\JetBrains\PhpStorm\Pure] function encodePosition(\pocketmine\world\Position $position): string{
	return "$position->x:$position->y:$position->z:{$position->world->getFolderName()}";
}

/**
 * Function decodePosition
 * @param string $string
 * @return \pocketmine\world\Position
 */
function decodePosition(string $string): \pocketmine\world\Position{
	$ex = explode(":", $string);
	if (!isset($ex[3])) {
		throw new LogicException("No world name is given in '$string'");
	}
	$world = \pocketmine\Server::getInstance()->getWorldManager()->getWorldByName($ex[3]);
	if ($world instanceof \pocketmine\world\World) {
		return new \pocketmine\world\Position(floatval($ex[0]), floatval($ex[1]), floatval($ex[2]), $world);
	}
	throw new \pocketmine\world\WorldException("World $ex[3] doesn't exists");
}

/**
 * Function getLogger
 * @return Logger
 */
function getLogger(): Logger{
	if (\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("BuildFFA") instanceof \xxAROX\BuildFFA\BuildFFA) {
		return \xxAROX\BuildFFA\BuildFFA::getInstance()->getLogger();
	} else {
		return GlobalLogger::get();
	}
}