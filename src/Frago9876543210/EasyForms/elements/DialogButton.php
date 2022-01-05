<?php
/*
 * Copyright (c) 2022 Jan Sohn.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace Frago9876543210\EasyForms\elements;
use Closure;
use JsonSerializable;
use pocketmine\utils\Utils;
use xxAROX\Core\player\CorePlayer;


/**
 * Class DialogButton
 * @package Frago9876543210\EasyForms\elements
 * @author Jan Sohn / xxAROX
 * @date 24. November, 2021 - 08:06
 * @ide PhpStorm
 * @project Bridge
 */
class DialogButton implements JsonSerializable{
	private const MODE_BUTTON = 0;
	private const MODE_ON_CLOSE = 1;

	private const TYPE_URL = 0; //???
	private const TYPE_COMMAND = 1;
	private const TYPE_INVALID = 2;

	private string $name;
	private string $text; //???
	private ?array $data = null; //???
	private int $mode = self::MODE_BUTTON; //???
	private int $type = self::TYPE_COMMAND; // ????
	private ?Closure $submitListener;

	/**
	 * DialogButton constructor.
	 * @param string $name
	 * @param null|Closure $submitListener
	 */
	public function __construct(string $name, ?Closure $submitListener = null) {
		$this->name = $name;
		$this->setSubmitListener($submitListener);
	}

	/**
	 * Function getName
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Function setName
	 * @param string $name
	 * @return void
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * Function getText
	 * @return string
	 */
	public function getText(): string{
		return $this->text;
	}

	/**
	 * Function setText
	 * @param string $text
	 * @return void
	 */
	public function setText(string $text): void{
		$this->text = $text;
	}

	/**
	 * Function getData
	 * @return ?array
	 */
	public function getData(): ?array{
		return $this->data;
	}

	/**
	 * Function setData
	 * @param null|array $data
	 * @return void
	 */
	public function setData(?array $data): void{
		$this->data = $data;
	}

	/**
	 * Function getMode
	 * @return int
	 */
	public function getMode(): int{
		return $this->mode;
	}

	/**
	 * Function setMode
	 * @param int $mode
	 * @return void
	 */
	public function setMode(int $mode): void{
		$this->mode = $mode;
	}

	/**
	 * Function getType
	 * @return int
	 */
	public function getType(): int{
		return $this->type;
	}

	/**
	 * Function setType
	 * @param int $type
	 * @return void
	 */
	public function setType(int $type): void{
		$this->type = $type;
	}

	/**
	 * Function getSubmitListener
	 * @return null|Closure
	 */
	public function getSubmitListener(): ?Closure {
		return $this->submitListener;
	}

	/**
	 * Function setSubmitListener
	 * @param null|Closure $submitListener
	 * @return void
	 */
	public function setSubmitListener(?Closure $submitListener): void {
		if ($submitListener !== null) {
			Utils::validateCallableSignature(function(CorePlayer $player) {}, $submitListener);
		}
		$this->submitListener = $submitListener;
	}

	/**
	 * Function executeSubmitListener
	 * @param CorePlayer $player
	 * @return void
	 */
	public function executeSubmitListener(CorePlayer $player): void {
		if($this->submitListener !== null) {
			($this->submitListener)($player);
		}
	}

	/**
	 * Function jsonSerialize
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			"button_name" => $this->name,
			"text" => $this->text ?? "",
			"data" => $this->data,
			"mode" => $this->mode,
			"type" => $this->type
		];
	}
}
