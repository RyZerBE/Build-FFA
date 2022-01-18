<?php
declare(strict_types=1);
namespace Frago9876543210\EasyForms\forms;
use Closure;
use Error;
use Frago9876543210\EasyForms\elements\Button;
use Frago9876543210\EasyForms\elements\FunctionalButton;
use JetBrains\PhpStorm\ArrayShape;
use pocketmine\form\FormValidationException;
use pocketmine\Player;
use xxAROX\Bridge\Bridge;

use function array_merge;
use function is_string;


class MenuForm extends Form{
	/** @var Button[] */
	protected array $buttons = [];
	protected string $text;
	private ?Closure $onSubmit;
	private ?Closure $onClose;

	/**
	 * MenuForm constructor.
	 * @param string $title
	 * @param string $text
	 * @param Button[]|string[] $buttons
	 * @param null|Closure $onSubmit
	 * @param null|Closure $onClose
	 */
	public function __construct(string $title, string $text = "", array $buttons = [], ?Closure $onSubmit = null, ?Closure $onClose = null){
		parent::__construct($title);
		$this->text = $text;
		$this->append(...$buttons);
		$this->setOnSubmit($onSubmit);
		$this->setOnClose($onClose);
	}

	/**
	 * @param FunctionalButton|Button|string ...$buttons
	 *
	 * @return self
	 */
	public function append(...$buttons): self{
		if (isset($buttons[0]) && is_string($buttons[0])) {
			$buttons = Button::createFromList(...$buttons);
		}
		$this->buttons = array_merge($this->buttons, $buttons);
		$buttons = [];
		foreach ($this->buttons as $button) {
			if (!is_null($button)) {
				$buttons[] = $button;
			}
		}
		$this->buttons = $buttons;
		return $this;
	}

	/**
	 * @param Closure|null $onSubmit
	 *
	 * @return self
	 */
	public function setOnSubmit(?Closure $onSubmit): self{
		$this->onSubmit = $onSubmit;
		return $this;
	}

	/**
	 * @param Closure|null $onClose
	 *
	 * @return self
	 */
	public function setOnClose(?Closure $onClose): self{
		$this->onClose = $onClose;
		return $this;
	}

	/**
	 * @param string $text
	 *
	 * @return self
	 */
	public function setText(string $text): self{
		$this->text = $text;
		return $this;
	}

	/**
	 * @return string
	 */
	final public function getType(): string{
		return self::TYPE_MENU;
	}

	final public function handleResponse(Player $player, $data): void{
		if ($data === null) {
			if ($this->onClose !== null) {
				($this->onClose)($player, $data);
			}
		} else if (is_int($data)) {
			if (!isset($this->buttons[$data])) {
				throw new FormValidationException("Button with index $data does not exist");
			}
			$button = $this->buttons[$data];
			$button->setValue($data);
			if ($button instanceof FunctionalButton) {
				$button->onClick($player);
				if (!is_null($this->onSubmit)) {
					Bridge::getInstance()->getLogger()->logException(new Error("Maybe Outdated call: onSubmit && on call"));
				}
			} else {
				if ($this->onSubmit !== null) {
					($this->onSubmit)($player, $button);
				}
			}
		} else {
			throw new FormValidationException("Expected int or null, got " . gettype($data));
		}
	}

	/**
	 * @return array
	 */
	#[ArrayShape([
		"buttons" => "\\Frago9876543210\\EasyForms\\elements\\Button[]",
		"content" => "string",
	])] protected function serializeFormData(): array{
		return [
			"buttons" => $this->buttons,
			"content" => $this->text,
		];
	}
}