<?php
declare(strict_types=1);
namespace Frago9876543210\EasyForms\elements;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;


class Button extends Element{
	/** @var Image|null */
	protected $image;
	/** @var string */
	protected $type;

	/**
	 * @param string $text
	 * @param Image|null $image
	 */
	#[Pure] public function __construct(string $text, ?Image $image = null){
		parent::__construct($text);
		$this->image = $image;
	}

	/**
	 * @param string ...$texts
	 *
	 * @return Button[]
	 */
	#[Pure] public static function createFromList(string ...$texts): array{
		$buttons = [];
		foreach ($texts as $text) {
			$buttons[] = new self($text);
		}
		return $buttons;
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string{
		return null;
	}

	/**
	 * @return array
	 */
	#[Pure] #[ArrayShape([
		"text"  => "string",
		"image" => "\Frago9876543210\EasyForms\elements\Image|null",
	])] public function serializeElementData(): array{
		$data = ["text" => $this->text];
		if ($this->hasImage()) {
			$data["image"] = $this->image;
		}
		return $data;
	}

	/**
	 * @return bool
	 */
	public function hasImage(): bool{
		return $this->image !== null;
	}
}