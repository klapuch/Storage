<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgHStoreToArray implements Conversion {
	private $database;
	private $original;

	public function __construct(\PDO $database, ?string $original) {
		$this->database = $database;
		$this->original = $original;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		return $this->original === null ? $this->original : array_reduce(
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
}