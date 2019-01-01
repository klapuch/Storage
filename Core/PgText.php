<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgText implements Conversion {
	/** @var string */
	private $original;

	/** @var string */
	private $type;

	/** @var \Klapuch\Storage\Conversion */
	private $delegation;

	public function __construct(string $original, string $type, Conversion $delegation) {
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (strcasecmp('text', $this->type) === 0) {
			return $this->original;
		}
		return $this->delegation->value();
	}
}