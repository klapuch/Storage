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
		$converted = $this->origin->value();
		$raw = array_filter($converted, 'is_string');
		$types = $this->types($this->compound);
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

	private function types(string $compound): array {
		return array_column(
			(new NativeQuery(
				$this->database,
				'SELECT attribute_name, data_type, ordinal_position
				FROM information_schema.attributes
				WHERE udt_name = lower(:type)
				UNION ALL
				SELECT column_name AS attribute_name, data_type, ordinal_position
				FROM information_schema.columns
				WHERE table_name = lower(:type)
				ORDER BY ordinal_position',
				['type' => $compound]
			))->rows(),
			'data_type',
			'attribute_name'
		);
	}
}