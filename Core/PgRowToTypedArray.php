<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgRowToTypedArray implements Conversion {
	private $origin;
	private $type;
	private $database;

	public function __construct(Conversion $origin, string $type, MetaPDO $database) {
		$this->origin = $origin;
		$this->type = $type;
		$this->database = $database;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		$converted = $this->origin->value();
		$raw = array_filter($converted, 'is_string');
		$types = array_column($this->database->meta($this->type), 'data_type', 'attribute_name');
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
}