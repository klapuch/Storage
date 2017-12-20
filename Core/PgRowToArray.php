<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgRowToArray implements Conversion {
	private $database;
	private $original;
	private $type;

	public function __construct(\PDO $database, string $original, string $type) {
		$this->database = $database;
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		try {
			if (strpos($this->type, '[]') === false)
				return $this->row();
			return $this->rows();
		} catch (\PDOException $ex) {
			$columns = $this->columns($this->type);
			throw new \UnexpectedValueException(
				$columns
					? sprintf('Type "%s" only exists as (%s)', $this->type, $columns)
					: sprintf('Type "%s" does not exist', $this->type),
				0,
				$ex
			);
		}
	}

	private function row(): array {
		return json_decode(
			(new NativeQuery(
				$this->database,
				sprintf('SELECT row_to_json(?::%s)', $this->type),
				[$this->original]
			))->field(),
			true
		);
	}

	private function rows(): array {
		return array_map(
			function(string $row): array {
				return json_decode($row, true);
			},
			array_column(
				(new NativeQuery(
					$this->database,
					sprintf('SELECT row_to_json(UNNEST(?::%s))', $this->type),
					[$this->original]
				))->rows(),
				'row_to_json'
			)
		);
	}

	private function columns(string $type): string {
		return implode(
			', ',
			array_column(
				(new NativeQuery(
					$this->database,
					'SELECT attribute_name, ordinal_position
					FROM information_schema.attributes
					WHERE udt_name = lower(:type)
					UNION ALL
					SELECT column_name AS attribute_name, ordinal_position
					FROM information_schema.columns
					WHERE table_name = lower(:type)
					ORDER BY ordinal_position',
					['type' => $type]
				))->rows(),
				'attribute_name'
			)
		);
	}
}