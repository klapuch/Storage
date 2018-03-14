<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgRowToTypedArray implements Conversion {
	private $original;
	private $type;
	private $database;
	private $delegation;

	public function __construct(string $original, string $type, MetaPDO $database, Conversion $delegation) {
		$this->original = $original;
		$this->type = $type;
		$this->database = $database;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		$meta = $this->database->meta($this->type);
		if ($meta) {
			$converted = (new PgRowToArray(
				$this->database,
				$this->original,
				$this->type
			))->value();
			$raw = array_filter($converted, 'is_string');
			$types = array_column($meta, 'data_type', 'attribute_name');
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