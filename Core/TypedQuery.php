<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Automatically typed query
 */
final class TypedQuery implements Query {
	private Connection $connection;

	private string $statement;

	/** @var mixed[] */
	private array $parameters;

	/**
	 * @param mixed[] $parameters
	 */
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
		assert(is_string($name));
		[$name => $value] = $this->conversions([$name => $statement->fetchColumn()], $statement);
		return $value;
	}

	/**
	 * @return mixed[]
	 */
	public function row(): array {
		$statement = $this->execute();
		$row = $statement->fetch(\PDO::FETCH_ASSOC);
		return $this->conversions($row === false ? [] : $row, $statement);
	}

	/**
	 * @return mixed[]
	 */
	public function rows(): array {
		$statement = $this->execute();
		return array_map(
			fn (array $row): array => $this->conversions($row, $statement),
			(array) $statement->fetchAll(\PDO::FETCH_ASSOC),
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
				$this->parameters,
			),
		);
		return $statement;
	}

	/**
	 * @param array<string, mixed> $rows
	 * @param \PDOStatement $statement
	 * @return array<string, mixed>
	 */
	private function conversions(array $rows, \PDOStatement $statement): array {
		$raw = array_filter($rows, 'is_string');
		$conversions = array_intersect_key($this->types($statement), $raw);
		array_walk(
			$conversions,
			static function($type, string $column) use (&$raw): void {
				$raw[$column] = (new Output\PgConversions(
					$raw[$column],
					$type,
				))->value();
			},
		);
		return $raw + $rows;
	}

	/**
	 * @return array<string, string>
	 */
	private function types(\PDOStatement $statement): array {
		return array_column(
			array_reduce(
				range(0, $statement->columnCount() - 1),
				static function(array $meta, int $column) use ($statement): array {
					$meta[] = $statement->getColumnMeta($column);
					return $meta;
				},
				[],
			),
			'native_type',
			'name',
		);
	}
}
