<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgArrayToArray implements Conversion {
	private $database;
	private $original;
	private $type;
	private $delegation;

	public function __construct(\PDO $database, string $original, string $type, Conversion $delegation) {
		$this->database = $database;
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (preg_match('~(?J)(^(?P<type>\w+)\[\])|(^_(?P<type>\w+))$~', $this->type, $match)) {
			return json_decode(
				(new NativeQuery(
					$this->database,
					sprintf('SELECT array_to_json(?::%s[])', $match['type']),
					[$this->original]
				))->field(),
				true
			);
		}
		return $this->delegation->value();
	}
}