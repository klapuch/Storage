<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

use Klapuch\Storage;

final class PgRowToArray implements Conversion {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var string */
	private $original;

	/** @var string */
	private $type;

	public function __construct(
		Storage\Connection $connection,
		string $original,
		string $type
	) {
		$this->connection = $connection;
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		try {
			if (strpos($this->type, '[]') === false) {
				return $this->row();
			}
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
			(new Storage\NativeQuery(
				$this->connection,
				sprintf('SELECT row_to_json(?::%s)', $this->type),
				[$this->original]
			))->field(),
			true
		);
	}

	private function rows(): array {
		return array_map(
			static function(string $row): array {
				return json_decode($row, true);
			},
			array_column(
				(new Storage\NativeQuery(
					$this->connection,
					sprintf('SELECT row_to_json(unnest(?::%s))', $this->type),
					[$this->original]
				))->rows(),
				'row_to_json'
			)
		);
	}

	private function columns(string $type): string {
		return implode(', ', array_column($this->connection->schema()->columns($type), 'attribute_name'));
	}
}