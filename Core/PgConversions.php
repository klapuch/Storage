<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgConversions implements Conversion {
	private $database;
	private $original;
	private $types;

	public function __construct(\PDO $database, string $original, array $types) {
		$this->database = $database;
		$this->original = $original;
		$this->types = $types;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		$type = key($this->types) ?: current($this->types);
		if (strcasecmp($type, 'HSTORE') === 0)
			return (new PgHStoreToArray($this->database, $this->original))->value();
		elseif (preg_match('~^(\w+)\[\]$~', $type, $match))
			return (new PgArrayToArray($this->database, $this->original, $match[1]))->value();
		elseif ($this->compound($type)) {
			$rows = (new PgRowToArray($this->database, $this->original, $type))->value();
			return $this->toCustomScalars($rows, $type) + $this->toScalars($rows);
		}
		return $this->original;
	}

	private function toCustomScalars(array $rows, string $type): array {
		$customTypedRows = array_filter(
			$rows,
			function(string $column) use ($type): bool {
				return isset($this->types[$type][$column]);
			},
			ARRAY_FILTER_USE_KEY
		);
		return array_combine(
			array_keys($customTypedRows),
			array_map(
				function(string $row, string $column) use ($type) {
					return (new PgStringToScalar($row, $this->types[$type][$column]))->value();
				},
				$customTypedRows,
				array_keys($customTypedRows)
			)
		);
	}

	private function toScalars(array $rows): array {
		return array_combine(
			array_keys($rows),
			array_map(
				function(string $row) {
					return (new PgStringToScalar($row))->value();
				},
				$rows
			)
		);
	}

	/**
	 * Is the given type compound?
	 * @param string $type
	 * @return bool
	 */
	private function compound(string $type): bool {
		return (bool) (new ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM information_schema.user_defined_types
			WHERE user_defined_type_name = lower(?)',
			[$type]
		))->field();
	}
}