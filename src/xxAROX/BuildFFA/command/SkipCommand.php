<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\command;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use xxAROX\BuildFFA\game\Game;


/**
 * Class SkipCommand
 * @package xxAROX\BuildFFA\command
 * @author Jan Sohn / xxAROX
 * @date 16. Januar, 2022 - 21:36
 * @ide PhpStorm
 * @project BuildFFA
 */
class SkipCommand extends Command{
	/**
	 * SkipCommand constructor.
	 */
	public function __construct(){
		parent::__construct("skip", "Skip current map", "/skip", ["forcemap"]);
		$this->setPermission("game.buildffa.map.skip");
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if (!$this->testPermission($sender)) {
			return;
		}
		Game::getInstance()->skip();
		$sender->sendMessage("§aSkipped");
		Command::broadcastCommandMessage($sender, "Skipped current map", false);
	}
}
