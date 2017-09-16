<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class TypedQuery implements Query {
	private $database;
	private $origin;
	private $conversions;

	public function __construct(\PDO $database, Query $origin, array $conversions) {
		$this->database = $database;
		$this->origin = $origin;
		$this->conversions = $conversions;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return (new PgConversions(
			$this->database,
			$this->origin->field(),
			current($this->conversions)
		))->value();
	}

	public function row(): array {
		return $this->conversions($this->origin->row(), $this->conversions);
	}

	public function rows(): array {
		return array_reduce(
			$this->origin->rows(),
			function(array $rows, array $row): array {
				$rows[] = $this->conversions($row, $this->conversions);
				return $rows;
			},
			[]
		);
	}

	public function execute(): \PDOStatement {
		return $this->origin->execute();
	}

	/**
	 * Rows converted by conversion lookup table
	 * @param array $rows
	 * @param array $conversions
	 * @return array
	 */
	private function conversions(array $rows, array $conversions): array {
		array_walk(
			$conversions,
			function(string $type, string $column) use (&$rows): void {
				$rows[$column] = (new PgConversions(
					$this->database,
					$rows[$column],
					$type
				))->value();
			}
		);
		return $rows;
	}
}