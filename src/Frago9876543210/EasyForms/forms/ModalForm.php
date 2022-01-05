<?php
declare(strict_types=1);
namespace Frago9876543210\EasyForms\forms;
use Closure;
use JetBrains\PhpStorm\ArrayShape;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

use function gettype;
use function is_bool;


class ModalForm extends Form{
	protected string $text;
	private string $yesButton;
	private string $noButton;
	private Closure $onSubmit;

	/**
	 * ModalForm constructor.
	 * @param string $title
	 * @param string $text
	 * @param Closure $onSubmit
	 * @param string $yesButton
	 * @param string $noButton
	 */
	public function __construct(string $title, string $text, Closure $onSubmit, string $yesButton = "gui.yes", string $noButton = "gui.no"){
		parent::__construct($title);
		$this->text = $text;
		$this->yesButton = $yesButton;
		$this->noButton = $noButton;
		$this->onSubmit = $onSubmit;
	}

	/**
	 * @return string
	 */
	final public function getType(): string{
		return self::TYPE_MODAL;
	}

	/**
	 * @return string
	 */
	public function getYesButtonText(): string{
		return $this->yesButton;
	}

	/**
	 * @return string
	 */
	public function getNoButtonText(): string{
		return $this->noButton;
	}

	/**
	 * @return array
	 */
	#[ArrayShape([
		"content" => "string",
		"button1" => "string",
		"button2" => "string",
	])] protected function serializeFormData(): array{
		return [
			"content" => $this->text,
			"button1" => $this->yesButton,
			"button2" => $this->noButton,
		];
	}

	final public function handleResponse(Player $player, $data): void{
		if (!is_bool($data)) {
			throw new FormValidationException("Expected bool, got " . gettype($data));
		}
		($this->onSubmit)($player, $data);
	}
}