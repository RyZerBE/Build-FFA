<?php
/*
 * Copyright (c) 2021 Jan Sohn.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);

namespace Frago9876543210\EasyForms\elements;

use pocketmine\form\FormValidationException;

class Label extends Element{

	/**
	 * @return string
	 */
	public function getType() : string{
		return "label";
	}

	/**
	 * @return array
	 */
	public function serializeElementData() : array{
		return [];
	}

	public function validate($value) : void{
		if($value !== null){
			throw new FormValidationException("Expected null, got " . gettype($value));
		}
	}
}