<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Automatically typed query
 */
final class TypedQuery implements Query {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var string */
	private $statement;

	/** @var mixed[] */
	private $parameters;

	public function __construct(Connection $connection, string $statement, array $parameters = []) {
		$this->connection = $connection;
		$this->statement = $statement;
		$this->parameters = $parameters;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		$statement = $this->execute();
		['name' => $name] = $statement->getColumnMeta(0);
		return current(
			$this->conversions(
				[$name => $statement->fetchColumn()],
				$statement
			)
		);
	}

	public function row(): array {
		$statement = $this->execute();
		return $this->conversions($statement->fetch(\PDO::FETCH_ASSOC) ?: [], $statement);
	}

	public function rows(): array {
		$statement = $this->execute();
		return array_reduce(
			(array) $statement->fetchAll(\PDO::FETCH_ASSOC),
			function(array $rows, array $row) use ($statement): array {
				$rows[] = $this->conversions($row, $statement);
				return $rows;
			},
			[]
		);
	}

	public function execute(): \PDOStatement {
		$statement = $this->connection->prepare($this->statement);
		$statement->execute(
			array_map(
				static function($value) {
					if (is_bool($value)) {
						return $value ? 't' : 'f';
					}
					return $value;
				},
				$this->parameters
			)
		);
		return $statement;
	}

	/**
	 * Rows converted by conversion lookup table
	 *
	 * @param array $rows
	 * @param \PDOStatement $statement
	 * @return array
	 */
	private function conversions(array $rows, \PDOStatement $statement): array {
		$raw = array_filter($rows, 'is_string');
		$conversions = array_intersect_key($this->types($statement), $raw);
		array_walk(
			$conversions,
			function($type, string $column) use (&$raw): void {
				$raw[$column] = (new Output\PgConversions(
					$this->connection,
					$raw[$column],
					$type
				))->value();
			}
		);
		return $raw + $rows;
	}

	/**
	 * Meta types extracted from the statement
	 *
	 * @param \PDOStatement $statement
	 * @return array
	 */
	private function types(\PDOStatement $statement): array {
		return array_column(
			array_reduce(
				range(0, $statement->columnCount() - 1),
				static function(array $meta, int $column) use ($statement): array {
					$meta[] = $statement->getColumnMeta($column);
					return $meta;
				},
				[]
			),
			'native_type',
			'name'
		);
	}
}