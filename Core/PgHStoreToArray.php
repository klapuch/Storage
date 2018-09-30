<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgHStoreToArray implements Conversion {
	private $connection;
	private $original;
	private $type;
	private $delegation;

	public function __construct(Connection $connection, string $original, string $type, Conversion $delegation) {
		$this->connection = $connection;
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
					$this->connection,
					'SELECT key, value FROM each(?::hstore)',
					[$this->original]
				))->rows(),
				static function (array $array, array $hstore): array {
					$array[$hstore['key']] = $hstore['value'];
					return $array;
				},
				[]
			);
		}
		return $this->delegation->value();
	}
}