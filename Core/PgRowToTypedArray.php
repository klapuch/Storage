<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgRowToTypedArray implements Conversion {
	private $connection;
	private $original;
	private $type;
	private $delegation;

	public function __construct(
		Connection $connection,
		string $original,
		string $type,
		Conversion $delegation
	) {
		$this->connection = $connection;
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		$columns = $this->connection->schema()->columns($this->type);
		if ($columns) {
			$converted = (new PgRowToArray(
				$this->connection,
				$this->original,
				$this->type
			))->value();
			$raw = array_filter($converted, 'is_string');
			$types = array_column($columns, 'data_type', 'attribute_name');
			return array_combine(
				array_keys($raw),
				array_map(
					function(?string $value, string $column) use ($types) {
						return (new PgStringToScalar($value, $types[$column]))->value();
					},
					$raw,
					array_keys($raw)
				)
			) + $converted;
		}
		return $this->delegation->value();
	}
}