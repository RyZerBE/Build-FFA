<?php
/*
 * Copyright (c) 2021 Jan Sohn.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace Frago9876543210\EasyForms\elements;
class StepSlider extends Dropdown{
	/**
	 * @return string
	 */
	public function getType(): string{
		return "step_slider";
	}

	/**
	 * @return array
	 */
	public function serializeElementData(): array{
		return [
			"steps"   => $this->options,
			"default" => $this->default,
		];
	}
}