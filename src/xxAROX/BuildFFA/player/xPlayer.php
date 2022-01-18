<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace xxAROX\BuildFFA\player;
use Frago9876543210\EasyForms\elements\FunctionalButton;
use Frago9876543210\EasyForms\elements\Label;
use Frago9876543210\EasyForms\elements\Slider;
use Frago9876543210\EasyForms\elements\Toggle;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;
use Frago9876543210\EasyForms\forms\MenuForm;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\entity\Effect;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use xxAROX\BuildFFA\BuildFFA;
use xxAROX\BuildFFA\event\BuildFFAPlayerChangeInvSortEvent;
use xxAROX\BuildFFA\event\BuildFFAPlayerRespawnEvent;
use xxAROX\BuildFFA\event\BuildFFAPlayerSpectatorEvent;
use xxAROX\BuildFFA\event\BuildFFASpawnPlatformEvent;
use xxAROX\BuildFFA\game\Arena;
use xxAROX\BuildFFA\game\Game;
use xxAROX\BuildFFA\game\Kit;
use xxAROX\BuildFFA\game\Setup;
use xxAROX\BuildFFA\items\InvSortItem;
use xxAROX\BuildFFA\items\KitItem;
use xxAROX\BuildFFA\items\MapItem;
use xxAROX\BuildFFA\items\PlaceHolderItem;
use xxAROX\BuildFFA\items\SettingsItem;
use xxAROX\BuildFFA\items\SpectateItem;


/**
 * Class xPlayer
 * @package xxAROX\BuildFFA\player
 * @author Jan Sohn / xxAROX
 * @date 30. Dezember, 2021 - 14:50
 * @ide PhpStorm
 * @project BuildFFA
 */
class xPlayer extends Player{
	/** @internal */
	public ?Setup $setup = null;
	/** @internal */
	public bool $is_in_inv_sort = false;
	/** @internal */
	public bool $allow_no_fall_damage = true;
	/** @internal */
	public string $voted_map = "";
	/** @internal */
	public array $itemCountdowns = [];
	// NOTE: this is for internal api stuff
	/** @internal */
	public array $enderpearls = [];
	protected int $kill_streak = 0;
	protected int $deaths = 0; //NOTE: lmao, i wrote that shit high af
	protected int $kills = 0;
	protected ?Kit $selected_kit = null;
	protected array $inv_sort = [];

	/**
	 * Function load
	 * @param int $kills
	 * @param int $deaths
	 * @param null|array $inv_sort
	 * @param null|string $kit_name
	 * @return void
	 */
	public function load(int $kills, int $deaths, ?array $inv_sort = null, ?string $kit_name = null): void{
		$this->kills = $kills;
		$this->deaths = $deaths;
		$this->kill_streak = 0;
		$this->inv_sort = $inv_sort ?? $this->inv_sort;
		$this->selected_kit = Game::getInstance()->getKit($kit_name);
	}

	/**
	 * Function store
	 * @return void
	 */
	public function store(): void{
		// TODO: @Baubo-LP
	}

	/**
	 * Function giveKit
	 * @param Kit $kit
	 * @return void
	 */
	public function giveKit(Kit $kit): void{
		if ($this->gamemode != self::SURVIVAL) {
			return;
		}
		$kit->equip($this);
		$this->saveInvSort();
	}

	/**
	 * Function saveInvSort
	 * @return void
	 */
	public function saveInvSort(): void{
		$newSort = [];
		foreach ($this->selected_kit->getContents() as $type => $item) {
			$toSort[$type] = false;
			for ($hotbar_slot = 0; $hotbar_slot < $this->inventory->getHotbarSize(); $hotbar_slot++) {
				$hotbar_item = $this->inventory->getItem($hotbar_slot);
				$hotbar_type = $hotbar_item->getNamedTag()->getString(BuildFFA::TAG_SORT_TYPE, "");
				$hotbar_placeholderId = $hotbar_item->getNamedTag()->getString(BuildFFA::TAG_PLACEHOLDER_IDENTIFIER, "");
				if (empty($hotbar_type)) {
					continue;
				}
				if (!empty($hotbar_placeholderId)) {
					if ($hotbar_placeholderId == $item->getNamedTag()->getString(BuildFFA::TAG_PLACEHOLDER_IDENTIFIER, "") && $hotbar_item instanceof PlaceHolderItem) {
						$this->inventory->setItem($hotbar_slot, $hotbar_item->getPlaceholdersItem());
					}
				}
				if ($hotbar_type == $type && ($this->inv_sort[$type] ?? -1) != $hotbar_slot) {
					$newSort[$type] = $hotbar_slot;
				}
			}
		}
		$ev1 = new BuildFFAPlayerChangeInvSortEvent($this, $this->inv_sort, $newSort);
		$ev1->call();
		if (!$ev1->isCancelled()) {
			foreach ($newSort as $type => $slot) {
				$this->inv_sort[$type] = $slot;
			}
		}
	}

	/**
	 * Function spawnPlatform
	 * @return bool
	 */
	public function spawnPlatform(): bool{
		if (!$this->isOnGround()) {
			$hand = $this->inventory->getItemInHand();
			if (!is_null($this->selected_kit->getPlaceholderByIdentifier($hand->getNamedTag()->getString("__placeholderId", "")))) {
				$this->itemCooldown($hand);
			}
			$size = 1;
			$affectedBlocks = [];
			$y = $this->getPosition()->y - 7;
			for ($xx = -$size; $xx <= $size; $xx++) {
				for ($zz = -$size; $zz <= $size; $zz++) {
					$vector3 = new Vector3($this->getPosition()->x + $xx, $y, $this->getPosition()->z + $zz);
					$blockBefore = $this->getLevel()->getBlock($vector3);
					if ($blockBefore->getId() == BlockIds::AIR) {
						$affectedBlocks[] = $this->getLevel()->getBlock($vector3);
					}
				}
			}
			$ev = new BuildFFASpawnPlatformEvent($this, $hand, $affectedBlocks, BlockFactory::get(BlockIds::GLASS));
			$ev->call();
			if (!$ev->isCancelled()) {
				foreach ($ev->getAffectedBlocks() as $affectedBlock) {
					$this->getLevel()->setBlock($affectedBlock->asPosition(), $ev->getBlock());
					Game::getInstance()->placeBlock($this->getLevel()->getBlock($affectedBlock->asPosition()), 5);
				}
				$this->teleport(new Vector3($this->getPosition()->x, $y + 2, $this->getPosition()->z));
				$this->fallDistance = 0.0;
			}
		}
		return !$this->isOnGround();
	}

	/**
	 * Function itemCooldown
	 * @param Item $item
	 * @return void
	 */
	public function itemCooldown(Item $item): void{
		$placeHolderItem = $this->selected_kit->getPlaceholderByIdentifier($item->getNamedTag()->getString("__placeholderId", ""));
		if (!is_null($placeHolderItem) && $placeHolderItem->hasCountdown() && !isset($player->itemCountdowns[encodeItem($item)])) {
			$this->itemCountdowns[encodeItem($item)] = [
				$placeHolderItem->getCountdown(),
				$item,
				$this->inventory->getHeldItemIndex(),
				$placeHolderItem,
			];
			$placeHolderItem->setCount($placeHolderItem->getCountdown());
			$this->inventory->setItemInHand($placeHolderItem);
		}
	}

	/**
	 * Function toggleSneak
	 * @param bool $sneak
	 * @return bool
	 */
	public function toggleSneak(bool $sneak): void{
		if ($this->is_in_inv_sort && !$sneak) {
			$this->saveInvSort();
			$this->is_in_inv_sort = false;
			$this->sendOtakaItems();
		}
		parent::toggleSneak($sneak);
	}

	/**
	 * Function sendOtakaItems
	 * @return void
	 */
	public function sendOtakaItems(){
		if ($this->gamemode == self::SPECTATOR) {
			return;
		}
		$barrier = applyReadonlyTag(ItemFactory::get(BlockIds::INVISIBLE_BEDROCK)->setCustomName("§r"));
		$this->inventory->clearAll();
		$this->armorInventory->clearAll();
		$this->cursorInventory->clearAll();
		$this->armorInventory->setHelmet($barrier);
		$this->armorInventory->setChestplate($barrier);
		$this->armorInventory->setLeggings($barrier);
		$this->armorInventory->setBoots($barrier);
		for ($slot = 9; $slot < $this->inventory->getSize(); $slot++) {
			$this->inventory->setItem($slot, $barrier);
		}
		for ($slot = 0; $slot < $this->craftingGrid->getSize(); $slot++) {
			$this->craftingGrid->setItem($slot, $barrier);
		}
		$this->inventory->setItem(0, new InvSortItem());
		$this->inventory->setItem(1, new MapItem());
		$this->inventory->setItem(4, new KitItem());
		if ($this->hasPermission("game.setup") || $this->hasPermission("game.buildffa.settings")) {
			$this->inventory->setItem(7, new SettingsItem());
		}
		$this->inventory->setItem(8, new SpectateItem());
	}

	/**
	 * Function sendMapSelect
	 * @return void
	 * @noinspection PhpExpressionResultUnusedInspection
	 */
	public function sendMapSelect(): void{
		if (count(Game::getInstance()->getArenas()) == 0) {
			$this->sendMessage("§cLazy owner(ping him), no maps found..");// TODO: language stuff
			return;
		}
		if (count(Game::getInstance()->getArenas()) == 1) {
			$this->sendMessage("§cOnly one map found, you have no choice..");// TODO: language stuff
			return;
		}
		$this->sendForm(new MenuForm("%ui.title.voting.map", "", array_map(fn(Arena $arena) => new FunctionalButton($arena->getWorld()->getFolderName() . "\n§c" . Game::getInstance()->mapVotes[$arena->getWorld()->getFolderName()] . " vote/s", function (xPlayer $player) use ($arena): void{
			if ($arena->getWorld()->getFolderName() == $player->voted_map) {
				Game::getInstance()->mapVotes[$player->voted_map]--;
				$player->voted_map = "";
			} else {
				if (!empty($player->voted_map)) {
					Game::getInstance()->mapVotes[$player->voted_map]--;
				}
				$player->voted_map = $arena->getWorld()->getFolderName();
				Game::getInstance()->mapVotes[$player->voted_map]++;
			}
		}), Game::getInstance()->getArenas())));
	}

	/**
	 * Function sendBuildFFASettingsForm
	 * @return void
	 */
	public function sendBuildFFASettingsForm(): void{
		$elements = [
			new Toggle("Enable Fall damage", Game::getInstance()->getArena()->getSettings()->enable_fall_damage),
			new Slider("Block despawn time", 0.5, 30, 0.5, Game::getInstance()->getArena()->getSettings()->blocks_cooldown),
		];
		$this->sendForm(new CustomForm("BuildFFA Settings", array_merge((Server::getInstance()->isOp($this->getName())
			? []
			: [/* if you remove this you are not a good developer :> */
				new Label("§o§9BuildFFA by " . implode(", ", BuildFFA::getInstance()->getDescription()->getAuthors())),
			]), $elements), function (xPlayer $player, CustomFormResponse $response): void{
			Game::getInstance()->getArena()->getSettings()->enable_fall_damage = $response->getToggle()->getValue();
			Game::getInstance()->getArena()->getSettings()->blocks_cooldown = $response->getSlider()->getValue();
			Command::broadcastCommandMessage($this, "Updated BuildFFA settings", false);
		}));
	}

	/**
	 * Function sendKitSelect
	 * @return void
	 */
	public function sendKitSelect(): void{
		if (count(Game::getInstance()->getKits()) == 0) {
			$this->sendMessage("§cLazy owner(ping him), no kits found..");// TODO: language stuff
			return;
		}
		if (count(Game::getInstance()->getKits()) == 1) {
			$this->sendMessage("§cOnly one kit found, you have no choice..");// TODO: language stuff
			return;
		}
		$this->sendForm(new MenuForm("%ui.title.voting.kit", "", array_map(fn(Kit $kit) => new FunctionalButton($kit->getDisplayName(), function (xPlayer $player) use ($kit): void{
			$player->setSelectedKit($kit);
		}), Game::getInstance()->getKits())));
	}

	/**
	 * Function spectate
	 * @return void
	 */
	public function spectate(): void{
		$ev = new BuildFFAPlayerSpectatorEvent($this, Game::getInstance()->getArena());
		$ev->call();
		if (!$ev->isCancelled()) {
			$this->inventory->setHeldItemIndex(0);
			$barrier = applyReadonlyTag(ItemFactory::get(BlockIds::INVISIBLE_BEDROCK)->setCustomName("§r"));
			$this->setGamemode(self::SPECTATOR);
			$this->inventory->clearAll();
			$this->cursorInventory->clearAll();
			$this->armorInventory->clearAll();
			$this->craftingGrid->clearAll();
			$this->armorInventory->setHelmet($barrier);
			$this->armorInventory->setChestplate($barrier);
			$this->armorInventory->setLeggings($barrier);
			$this->armorInventory->setBoots($barrier);
			for ($slot = 9; $slot < $this->inventory->getSize(); $slot++) {
				$this->inventory->setItem($slot, $barrier);
			}
			for ($slot = 0; $slot < $this->craftingGrid->getSize(); $slot++) {
				$this->craftingGrid->setItem($slot, $barrier);
			}
			$this->inventory->setItem(8, ItemFactory::get(BlockIds::IRON_DOOR_BLOCK)->setCustomName("§r"));
		}
	}

	/**
	 * Function getInvSort
	 * @return int[]
	 */
	public function getInvSort(): array{
		return $this->inv_sort;
	}

	/**
	 * Function setInvSort
	 * @param array|int[] $inv_sort
	 * @return void
	 */
	public function setInvSort(array $inv_sort): void{
		$this->inv_sort = $inv_sort;
	}

	/**
	 * Function getSelectedKit
	 * @return ?Kit
	 */
	public function getSelectedKit(): ?Kit{
		return $this->selected_kit;
	}

	/**
	 * Function setSelectedKit
	 * @param null|Kit $selected_kit
	 * @return void
	 */
	public function setSelectedKit(?Kit $selected_kit): void{
		$this->selected_kit = $selected_kit;
	}

	/**
	 * Function entityBaseTick
	 * @param int $tickDiff
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1): bool{
		if ($this->getPosition()->y <= Game::getInstance()->getArena()->getSettings()->respawn_height) {
			$this->__respawn();
		}
		if ($this->server->getTick() % 20 == 0) {
			foreach ($this->itemCountdowns as $_ => $obj) {
				$this->itemCountdowns[$_][0]--;
				$secondsLeft = $this->itemCountdowns[$_][0];
				$slot = $this->itemCountdowns[$_][2];
				/** @var PlaceHolderItem $placeholder_item */
				$placeholder_item = $this->itemCountdowns[$_][3];
				if ($secondsLeft <= 0) {
					unset($this->itemCountdowns[$_]);
					$this->inventory->setItem($slot, $placeholder_item->getPlaceholdersItem());
				} else {
					$item = clone $placeholder_item;
					$item->setCount(intval(round($secondsLeft)));
					$item->setCustomName("§r§8{$secondsLeft} seconds left");
					$this->inventory->setItem($slot, $item);
				}
			}
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function fall(float $fallDistance): void{
		$damage = ceil($fallDistance - 3 - ($this->hasEffect(Effect::JUMP)
				? $this->getEffect(Effect::JUMP)->getEffectLevel() : 0));
		if ($damage > 0) {
			if ($this->allow_no_fall_damage || !Game::getInstance()->getArena()->getSettings()->enable_fall_damage) {
				$this->allow_no_fall_damage = false;
				return;
			}
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev);
		}
	}

	/**
	 * Function attack
	 * @param EntityDamageEvent $source
	 * @return void
	 */
	public function attack(EntityDamageEvent $source): void{
		if (Game::getInstance()->getArena()->isInProtectionArea($this->getPosition()->asVector3())) {
			$source->setCancelled();
		}
		parent::attack($source);
	}

	/**
	 * Function onDeath
	 * @return void
	 */
	protected function onDeath(): void{
		$this->doCloseInventory();
		$ev = new PlayerDeathEvent($this, $this->getDrops(), "", $this->getXpDropAmount());
		$ev->call();
		if (!$ev->getKeepInventory()) {
			$this->inventory?->clearAll();
			$this->armorInventory?->clearAll();
		}
		$this->setXpAndProgress(0, 0.0);
		//TODO: death message
		//$this->server->broadcastMessage($ev->getDeathMessage());
		$this->startDeathAnimation();
		$this->setHealth($this->getMaxHealth());
		$this->__respawn();
	}

	/**
	 * Function __respawn
	 * @return void
	 */
	public function __respawn(): void{
		$ev = new BuildFFAPlayerRespawnEvent($this, Game::getInstance()->getArena());
		$ev->call();
		$this->setHealth($this->getMaxHealth());
		$this->setGamemode(self::SURVIVAL);
		$this->saveInvSort();
		/** @var EnderPearl $enderpearl */
		foreach ($this->enderpearls as $enderpearl) {
			if (!$enderpearl->isFlaggedForDespawn()) {
				$enderpearl->flagForDespawn();
			}
		}
		unset($this->enderpearls);
		$this->enderpearls = [];
		unset($this->itemCountdowns);
		$this->itemCountdowns = [];
		if (!$ev->isCancelled()) {
			$this->teleport(Game::getInstance()->getArena()->getWorld()->getSafeSpawn());
			$this->sendOtakaItems();
		}
	}

	/**
	 * Function __set
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value){
		$this->$name = $value;
	}

	/**
	 * Function __get
	 * @param string $name
	 * @return int|mixed
	 */
	public function __get($name){
		return $this->$name;
	}
}
