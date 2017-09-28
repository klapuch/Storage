<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgRowToTypedArray implements Conversion {
	private $origin;
	private $compound;
	private $database;

	public function __construct(Conversion $origin, string $compound, \PDO $database) {
		$this->origin = $origin;
		$this->compound = $compound;
		$this->database = $database;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		$row = $this->origin->value();
		$columns = array_keys($row);
		$types = $this->types($this->compound);
		return array_combine(
			$columns,
			array_map(
				function(?string $value, string $column) use ($types) {
					return (new PgStringToScalar($value, $types[$column]))->value();
				},
				$row,
				$columns
			)
		);
	}

	private function types(string $compound): array {
		return array_column(
			(new ParameterizedQuery(
				$this->database,
				'SELECT attribute_name, data_type
				FROM information_schema.attributes
				WHERE udt_name = lower(?)
				ORDER BY ordinal_position',
				[$compound]
			))->rows(),
			'data_type',
			'attribute_name'
		);
	}
}