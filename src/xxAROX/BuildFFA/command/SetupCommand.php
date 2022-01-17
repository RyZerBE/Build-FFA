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
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class SetupCommand
 * @package xxAROX\BuildFFA\command
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 21:35
 * @ide PhpStorm
 * @project BuildFFA
 */
class SetupCommand extends Command{
	/**
	 * SetupCommand constructor.
	 */
	public function __construct(){
		parent::__construct("setup", "Setup command", null, []);
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if (!$sender instanceof xPlayer) {
			$sender->sendMessage("§o§n§cNot f-f-f-for y-y-you!");
			return;
		}
		if (isset($args[0]) && strtolower($args[0]) == "settings") {
			if (!$this->testPermission($sender, "game.buildffa.settings")) {
				return;
			}
			$sender->sendBuildFFASettingsForm();
		} else {
			if (!$this->testPermission($sender, "game.setup")) {
				return;
			}
			Game::getInstance()->setup($sender);
		}
	}
}
