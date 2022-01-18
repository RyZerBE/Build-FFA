<?php
/*
 * Copyright (c) 2021 Jan Sohn.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
namespace Frago9876543210\EasyForms\elements;
use Closure;
use JetBrains\PhpStorm\Pure;
use xxAROX\BuildFFA\player\xPlayer;


/**
 * Class FunctionalButton
 * @package Frago9876543210\EasyForms\elements
 * @author xxAROX
 * @date 25.10.2020 - 02:26
 * @project StimoCloud
 */
class FunctionalButton extends Button{
	protected ?Closure $onClick = null;

	/**
	 * FunctionalButton constructor.
	 * @param string $text
	 * @param null|Closure $onClick
	 * @param null|Image $image
	 */
	#[Pure] public function __construct(string $text, ?Closure $onClick = null, ?Image $image = null){
		parent::__construct($text, $image);
		$this->onClick = $onClick;
	}

	/**
	 * Function onClick
	 * @param xPlayer $player
	 * @return void
	 */
	public function onClick(xPlayer $player): void{
		if (!is_null($this->onClick)) {
			($this->onClick)($player);
		}
	}
}
