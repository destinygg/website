<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Options;

class AppEvent {
	
	/**
	 * Event types
	 *
	 * @var string
	 */
	const EVENT_INFO = 'info';
	const EVENT_DANGER = 'danger';
	const EVENT_SUCCESS = 'success';
	
	/**
	 * The type of event
	 *
	 * @var string
	 */
	protected $type = '';
	
	/**
	 * The label
	 *
	 * @var string
	 */
	protected $label = '';
	
	/**
	 * The message
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * Create a app event
	 *
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if (! empty ( $params )) {
			Options::setOptions ( $this, $params );
		}
	}

	public function __toString() {
		return $this->message;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function getHtml() {
		return $this->html;
	}

	public function setHtml($html) {
		$this->html = $html;
	}

}