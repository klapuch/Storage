<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgHStoreToArray implements Conversion {
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
		if (strcasecmp($this->type, 'hstore') === 0) {
			return array_reduce(
				(new NativeQuery(
					$this->database,
					'SELECT key, value FROM EACH(?::hstore)',
					[$this->original]
				))->rows(),
				function (array $array, array $hstore): array {
					$array[$hstore['key']] = $hstore['value'];
					return $array;
				},
				[]
			);
		}
		return $this->delegation->value();
	}
}