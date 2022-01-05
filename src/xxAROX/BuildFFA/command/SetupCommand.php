<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\command;
use Frago9876543210\EasyForms\elements\FunctionalButton;
use Frago9876543210\EasyForms\elements\Slider;
use Frago9876543210\EasyForms\elements\StepSlider;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;
use Frago9876543210\EasyForms\forms\MenuForm;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Server;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\game\Arena;
use xxAROX\BuildFFA\game\Game;
use xxAROX\BuildFFA\game\Setup;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class SetupCommand
 * @package xxAROX\BuildFFA\command
 * @author Jan Sohn / xxAROX
 * @date 05. Januar, 2022 - 21:35
 * @ide PhpStorm
 * @project BuildFFA
 */
class SetupCommand extends \pocketmine\command\Command{
	public function __construct(){
		parent::__construct("setup", "Setup command", null, []);
		$this->setPermission("game.setup");
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if (!$this->testPermission($sender)) {
			return;
		}
		if (!$sender instanceof xPlayer) {
			$sender->sendMessage("§o§n§cNot f-f-f-for y-y-you!");
			return;
		}
		$worlds = [];
		foreach (array_diff(scandir(Server::getInstance()->getDataPath() . "worlds/"), ["..", "."]) as $world) {
			if (!in_array($world, array_map(fn(Arena $arena) => $arena->getWorld()->getFolderName(), Game::getInstance()->getArenas()))) {
				$worlds[] = new FunctionalButton($world, function (xPlayer $player) use ($world): void{
					$player->sendMessage("now break block at respawn_height");
					$player->setup = new Setup($player, BuildFFA::getInstance()->getDataFolder() . "config.yml", $world, 3, [
						BlockBreakEvent::class => function (BlockBreakEvent $event): void{
							/** @var xPlayer $player */
							$player = $event->getPlayer();
							if ($player->setup->getCurrentStage() == 1) {
								$player->setup->configuration["respawn_height"] = $event->getBlock()->getPosition()->y;
								$player->setup->sendMessage("respawn_height set to " . $event->getBlock()->getPosition()->y);
								$player->setup->sendMessage("now break block at spawn protection border");
							} else if ($player->setup->getCurrentStage() == 2) {
								$player->setup->configuration["protection"] = $event->getBlock()->getPosition()->distance($player->getWorld()->getSpawnLocation());
								$player->setup->sendMessage("spawn protection set to " . $event->getBlock()->getPosition()->distance($player->getWorld()->getSpawnLocation()));
								$player->sendForm(new CustomForm("Select block cooldown", [new Slider("Seconds", 0.5, 10, 0.5, 5)], function (xPlayer $player, CustomFormResponse $response): void{
									$count = $response->getSlider()->getValue();
									$player->setup->configuration["blocks_cooldown"] = $count;
									$player->setup->sendMessage("blocks_cooldown set to " . $count);
									$player->setup->nextStage();
								}, function (xPlayer $player): void{
									$player->setup->leave();
								}));
							}
							$player->setup->nextStage();
						},
					]);
				});
			}
		}
		if (count($worlds) == 0) {
			$sender->sendMessage("§cNo new maps found!");
			return;
		}
		$sender->sendForm(new MenuForm("New Map", "", $worlds));
	}
}
